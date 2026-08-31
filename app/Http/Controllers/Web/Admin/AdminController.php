<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AffiliateReferral;
use App\Models\AppSetting;
use App\Models\Ad;
use App\Models\AntiCheatFlag;
use App\Models\Complaint;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\DepositMethod;
use App\Models\Faq;
use App\Models\Gig;
use App\Models\MarketplaceListing;
use App\Models\Message;
use App\Models\User;
use App\Models\SiteSetting;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskProof;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawalMethod;
use App\Services\ActivationService;
use App\Services\AffiliateService;
use App\Services\NotificationService;
use App\Services\SettingService;
use App\Services\SystemHealthService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Admin panel controller (Blade / session-based via the 'admin' guard).
 *
 * Provides full administrative control: dashboard, user management, deposit &
 * withdrawal approvals, task moderation, category & FAQ CRUD, complaint
 * resolution, support messaging, currency & payment-method management,
 * global settings, transaction oversight, and affiliate program management.
 */
class AdminController extends Controller
{
    public function __construct(
        private SettingService $settings,
        private WalletService $wallet,
        private ActivationService $activation,
        private AffiliateService $affiliate,
        private NotificationService $notifications,
    ) {}

    /* =========================================================
     * Auth
     * ========================================================= */
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($validated['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::guard('admin')->attempt([$field => $validated['email'], 'password' => $validated['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $admin = Auth::guard('admin')->user();
            $admin->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->with('error', 'Invalid credentials.')->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    /* =========================================================
     * Dashboard
     * ========================================================= */
    public function dashboard()
    {
        $stats = [
            'users'        => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'tasks'        => Task::count(),
            'active_tasks' => Task::where('status', 1)->count(),
            'deposits'     => Deposit::where('status', 1)->sum('amount'),
            'withdrawals'  => Withdrawal::where('status', 1)->sum('paid'),
            'pending_deposits'    => Deposit::where('status', 0)->count(),
            'pending_withdrawals' => Withdrawal::where('status', 0)->count(),
            'pending_tasks'       => Task::where('status', 0)->count(),
            'complaints'          => Complaint::where('status', 1)->count(),
        ];

        $recentUsers = User::latest()->limit(6)->get(['id', 'name', 'username', 'email', 'is_active', 'created_at']);
        $recentTxns = Transaction::with('user:id,username,name')->latest()->limit(8)->get();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentTxns'));
    }

    /* =========================================================
     * Users
     * ========================================================= */
    public function users(Request $request)
    {
        $query = User::query();

        if ($q = $request->input('q')) {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if ($request->input('active') === '1') {
            $query->where('is_active', true);
        } elseif ($request->input('active') === '0') {
            $query->where('is_active', false);
        }
        if ($request->input('banned') === '1') {
            $query->where('banned', true);
        }

        $users = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.users', compact('users'));
    }

    public function userShow(User $user)
    {
        $user->load(['country', 'referrer:id,username,name', 'deposits.method', 'withdrawals.method', 'transactions' => fn ($q) => $q->limit(20)]);
        $affiliateReferrals = $user->affiliateReferrals()->with('referee:id,username,name')->latest()->limit(20)->get();

        return view('admin.user-show', compact('user', 'affiliateReferrals'));
    }

    public function userBan(Request $request, User $user)
    {
        $request->validate(['banned' => 'required|boolean']);
        $user->update(['banned' => (bool) $request->input('banned')]);

        return back()->with('success', $request->input('banned') ? 'User has been blocked.' : 'User has been unblocked.');
    }

    public function userActivate(User $user)
    {
        $pendingActivation = Deposit::where('user_id', $user->id)->where('type', 'activation')->where('status', 0)->latest()->first();

        if ($pendingActivation) {
            $this->activation->applyActivation($pendingActivation, (float) $pendingActivation->amount_paid);
        } else {
            // Manually activate without a deposit record.
            DB::transaction(function () use ($user) {
                $user->update(['is_active' => true, 'activated_at' => now()]);
                $this->wallet->credit($user, 0, 'activation', [
                    'description' => 'Manual activation by admin',
                    'status'      => 'completed',
                ]);
            });
        }

        return back()->with('success', 'User activated manually.');
    }

    public function userBalance(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|not_in:0',
            'description' => 'required|string|max:500',
        ]);

        $this->wallet->adminAdjust($user, (float) $validated['amount'], $validated['description']);
        $this->settings->flush();

        return back()->with('success', 'Balance adjusted successfully.');
    }

    /* =========================================================
     * Deposits
     * ========================================================= */
    public function deposits(Request $request)
    {
        $query = Deposit::with('user:id,username,name,email', 'method');

        if ($status = $request->input('status')) {
            $map = ['pending' => 0, 'approved' => 1, 'rejected' => 2];
            if (isset($map[$status])) {
                $query->where('status', $map[$status]);
            }
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $deposits = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.deposits', compact('deposits'));
    }

    public function depositApprove(Deposit $deposit)
    {
        if ($deposit->isPaid()) {
            return back()->with('error', 'This deposit is already approved.');
        }

        DB::transaction(function () use ($deposit) {
            if ($deposit->isActivation()) {
                $this->activation->applyActivation($deposit, (float) $deposit->amount_paid);
            } else {
                $deposit->update(['status' => 1]);
                $this->wallet->credit($deposit->user, (float) $deposit->amount, 'deposit', [
                    'reference'    => $deposit->reference,
                    'description'  => 'Wallet deposit approved ('.$deposit->amount.' USD)',
                    'related_id'   => $deposit->id,
                    'related_type' => Deposit::class,
                ]);
            }
        });

        return back()->with('success', 'Deposit approved.');
    }

    public function depositReject(Request $request, Deposit $deposit)
    {
        if ($deposit->isPaid()) {
            return back()->with('error', 'Cannot reject an already-approved deposit.');
        }

        $request->validate(['reject_note' => 'nullable|string|max:500']);
        $deposit->update(['status' => 2, 'reject_note' => $request->input('reject_note')]);

        return back()->with('success', 'Deposit rejected.');
    }

    /* =========================================================
     * Withdrawals
     * ========================================================= */
    public function withdrawals(Request $request)
    {
        $query = Withdrawal::with('user:id,username,name,email', 'method');

        if ($status = $request->input('status')) {
            $map = ['pending' => 0, 'paid' => 1, 'rejected' => 2];
            if (isset($map[$status])) {
                $query->where('status', $map[$status]);
            }
        }

        $withdrawals = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.withdrawals', compact('withdrawals'));
    }

    public function withdrawalPaid(Withdrawal $withdrawal)
    {
        if ((int) $withdrawal->status !== 0) {
            return back()->with('error', 'This withdrawal has already been processed.');
        }
        $withdrawal->update(['status' => 1]);

        return back()->with('success', 'Withdrawal marked as paid.');
    }

    public function withdrawalReject(Request $request, Withdrawal $withdrawal)
    {
        if ((int) $withdrawal->status !== 0) {
            return back()->with('error', 'This withdrawal has already been processed.');
        }

        $request->validate(['reject_note' => 'nullable|string|max:500']);

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal->update(['status' => 2, 'reject_note' => $request->input('reject_note')]);
            // Refund the debited amount.
            $this->wallet->credit($withdrawal->user, (float) $withdrawal->amount, 'withdrawal_refund', [
                'reference'    => 'WDREFUND-'.$withdrawal->id,
                'description'  => 'Withdrawal rejected — funds refunded',
                'related_id'   => $withdrawal->id,
                'related_type' => Withdrawal::class,
            ]);
        });

        return back()->with('success', 'Withdrawal rejected and funds refunded.');
    }

    /* =========================================================
     * Tasks moderation
     * ========================================================= */
    public function tasks(Request $request)
    {
        $query = Task::with('category', 'user:id,username,name');

        if ($status = $request->input('status')) {
            $map = ['pending' => 0, 'active' => 1, 'completed' => 2, 'rejected' => 3, 'full' => 4];
            if (isset($map[$status])) {
                $query->where('status', $map[$status]);
            }
        }

        $tasks = $query->latest()->paginate(20)->appends($request->query());

        return view('admin.tasks', compact('tasks'));
    }

    public function taskApprove(Task $task)
    {
        if ((int) $task->status !== 0) {
            return back()->with('error', 'Only pending tasks can be approved.');
        }
        $task->update(['status' => 1]);

        return back()->with('success', 'Task approved and is now live.');
    }

    public function taskReject(Request $request, Task $task)
    {
        if (in_array((int) $task->status, [1, 2], true)) {
            return back()->with('error', 'Cannot reject an active or completed task.');
        }
        $request->validate(['reject_note' => 'required|string|max:1000']);

        DB::transaction(function () use ($task, $request) {
            $task->update(['status' => 3, 'reject_note' => $request->input('reject_note')]);
            // Refund the reserved total to the employer.
            if ((float) $task->total_price > 0) {
                $this->wallet->credit($task->user, (float) $task->total_price, 'task_refund', [
                    'reference'    => 'TASKREFUND-'.$task->code,
                    'description'  => 'Task rejected — funds refunded',
                    'related_id'   => $task->id,
                    'related_type' => Task::class,
                ]);
            }
        });

        return back()->with('success', 'Task rejected and funds refunded to the employer.');
    }

    public function taskDelete(Task $task)
    {
        $admin = Auth::guard('admin')->user();
        if (! $admin->isSuper()) {
            return back()->with('error', 'Only super admins can delete tasks.');
        }
        $task->delete();

        return back()->with('success', 'Task deleted.');
    }

    /**
     * Admin view of all proof submissions (pictures + details) for a given task.
     * Lets the admin see every advertiser pending/processed proof and take
     * moderation action (approve & pay worker, or reject with a note).
     */
    public function taskProofs(Request $request, Task $task)
    {
        $task->load('category', 'user:id,username,name,image');

        $query = $task->proofs()->with('user:id,username,name,image');

        if ($request->input('status') !== null && $request->input('status') !== '') {
            $query->where('status', (int) $request->input('status'));
        }

        $proofs = $query->latest()->paginate(12)->appends($request->query());

        return view('admin.task-proofs', compact('task', 'proofs'));
    }

    /**
     * Admin approves a proof and credits the worker (same flow as the employer
     * side, but accessible to admins for moderation).
     */
    public function proofApprove(Request $request, TaskProof $proof, WalletService $wallet)
    {
        $task = $proof->task;

        if ((int) $proof->status !== 0) {
            return back()->with('error', 'This proof has already been processed.');
        }

        DB::transaction(function () use ($proof, $task, $wallet) {
            $proof->update(['status' => 1]);
            $locked = Task::lockForUpdate()->find($task->id);
            $locked->increment('completed');

            // Pay the worker the unit price.
            $wallet->credit($proof->user, (float) $task->price, 'task_credit', [
                'reference'    => 'TASK-'.$task->code.'-'.$proof->id,
                'description'  => 'Payment for task "'.$task->title.'" (admin approved)',
                'related_id'   => $proof->id,
                'related_type' => TaskProof::class,
            ]);

            // If all slots completed, mark task completed.
            if ($locked->completed >= $locked->amount) {
                $locked->update(['status' => 2]);
            }
        });

        return back()->with('success', 'Proof approved. The worker has been paid.');
    }

    /**
     * Admin rejects a proof with a mandatory reason note.
     */
    public function proofReject(Request $request, TaskProof $proof)
    {
        if ((int) $proof->status !== 0) {
            return back()->with('error', 'This proof has already been processed.');
        }

        $request->validate(['reject_note' => 'required|string|max:1000']);

        $proof->update(['status' => 2, 'reject_note' => $request->input('reject_note')]);

        return back()->with('success', 'Proof rejected.');
    }

    /* =========================================================
     * Categories
     * ========================================================= */
    public function categories()
    {
        $categories = TaskCategory::with('children')->whereNull('parent_id')->orderBy('position')->get();
        $all = TaskCategory::orderBy('name')->get();
        return view('admin.categories', compact('categories', 'all'));
    }

    public function categoryStore(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:120',
            'parent_id' => 'nullable|exists:task_categories,id',
            'icon'      => 'nullable|string|max:60',
            'color'     => 'nullable|string|max:20',
            'price'     => 'nullable|numeric|min:0',
            'min_amount' => 'nullable|integer|min:1',
            'active'    => 'nullable|boolean',
            'position'  => 'nullable|integer|min:0',
        ]);

        TaskCategory::create([
            'name'       => $validated['name'],
            'parent_id'  => $validated['parent_id'] ?? null,
            'slug'       => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'icon'       => $validated['icon'] ?? 'briefcase',
            'color'      => $validated['color'] ?? '#2563eb',
            'price'      => $validated['price'] ?? 0,
            'min_amount' => $validated['min_amount'] ?? 1,
            'active'     => (bool) ($validated['active'] ?? true),
            'position'   => $validated['position'] ?? 0,
        ]);

        return back()->with('success', 'Category created.');
    }

    public function categoryUpdate(Request $request, TaskCategory $category)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:120',
            'parent_id' => 'nullable|exists:task_categories,id',
            'icon'      => 'nullable|string|max:60',
            'color'     => 'nullable|string|max:20',
            'price'     => 'nullable|numeric|min:0',
            'min_amount' => 'nullable|integer|min:1',
            'active'    => 'nullable|boolean',
            'position'  => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name'       => $validated['name'],
            'parent_id'  => $validated['parent_id'] ?? null,
            'icon'       => $validated['icon'] ?? $category->icon ?? 'briefcase',
            'color'      => $validated['color'] ?? $category->color ?? '#2563eb',
            'price'      => $validated['price'] ?? 0,
            'min_amount' => $validated['min_amount'] ?? 1,
            'active'     => (bool) ($validated['active'] ?? true),
            'position'   => $validated['position'] ?? 0,
        ]);

        return back()->with('success', 'Category updated.');
    }

    public function categoryDelete(TaskCategory $category)
    {
        if ($category->children()->exists()) {
            return back()->with('error', 'Delete the sub-categories first.');
        }
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    /* =========================================================
     * Complaints
     * ========================================================= */
    public function complaints(Request $request)
    {
        $query = Complaint::with(['proof.task:id,title,code', 'user:id,username,name', 'user2:id,username,name']);

        if ($status = $request->input('status')) {
            $map = ['open' => 1, 'approved' => 2, 'rejected' => 3];
            if (isset($map[$status])) {
                $query->where('status', $map[$status]);
            }
        }

        $complaints = $query->latest()->paginate(15)->appends($request->query());

        return view('admin.complaints', compact('complaints'));
    }

    public function complaintResolve(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:2,3',
            'reply'  => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($complaint, $validated) {
            $complaint->update(['status' => (int) $validated['status'], 'reply' => $validated['reply'] ?? null]);

            // status 2 = worker wins, status 3 = employer wins
            if ((int) $validated['status'] === 2) {
                // Worker wins: pay the worker.
                $proof = $complaint->proof;
                $this->wallet->credit($proof->user, (float) $proof->task->price, 'task_credit', [
                    'reference'   => 'COMPLAINT-'.$complaint->id,
                    'description' => 'Complaint resolved in favour of worker for "'.$proof->task->title.'"',
                    'related_id'  => $complaint->id,
                    'related_type' => Complaint::class,
                ]);
                $proof->update(['status' => 1]);
            } else {
                // Employer wins: refund the unit price to the employer.
                $proof = $complaint->proof;
                $this->wallet->credit($proof->task->user, (float) $proof->task->price, 'task_refund', [
                    'reference'   => 'COMPLAINT-'.$complaint->id,
                    'description' => 'Complaint resolved in favour of employer for "'.$proof->task->title.'"',
                    'related_id'  => $complaint->id,
                    'related_type' => Complaint::class,
                ]);
                $proof->update(['status' => 3]);
            }
        });

        return back()->with('success', 'Complaint resolved.');
    }

    /* =========================================================
     * Messages (support)
     * ========================================================= */
    public function messages()
    {
        $users = User::whereHas('messages')->withCount(['messages as unread_count' => fn ($q) => $q->where('from_admin', false)->where('seen', false)])
            ->latest()->paginate(20);
        return view('admin.messages', compact('users'));
    }

    public function messageShow(User $user)
    {
        $messages = $user->messages()->orderBy('created_at')->get();
        $user->messages()->where('from_admin', false)->where('seen', false)->update(['seen' => true]);

        return view('admin.message-show', compact('user', 'messages'));
    }

    public function messageReply(Request $request, User $user)
    {
        $validated = $request->validate(['message' => 'required|string|max:3000']);
        Message::create([
            'user_id'    => $user->id,
            'message'    => $validated['message'],
            'from_admin' => true,
            'seen'       => false,
        ]);
        return back()->with('success', 'Reply sent.');
    }

    /* =========================================================
     * FAQs
     * ========================================================= */
    public function faqs()
    {
        $faqs = Faq::orderBy('position')->paginate(20);
        return view('admin.faqs', compact('faqs'));
    }

    public function faqStore(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer'   => 'required|string|max:5000',
            'position' => 'nullable|integer|min:0',
        ]);
        Faq::create($validated);
        return back()->with('success', 'FAQ created.');
    }

    public function faqUpdate(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer'   => 'required|string|max:5000',
            'position' => 'nullable|integer|min:0',
        ]);
        $faq->update($validated);
        return back()->with('success', 'FAQ updated.');
    }

    public function faqDelete(Faq $faq)
    {
        $faq->delete();
        return back()->with('success', 'FAQ deleted.');
    }

    /* =========================================================
     * Currencies
     * ========================================================= */
    public function currencies()
    {
        $currencies = Currency::orderBy('position')->orderBy('code')->paginate(20);
        return view('admin.currencies', compact('currencies'));
    }

    public function currencyStore(Request $request)
    {
        $validated = $request->validate([
            'code'               => 'required|string|max:6|unique:currencies,code',
            'name'               => 'required|string|max:120',
            'symbol'             => 'required|string|max:8',
            'usd_value'          => 'required|numeric|min:0.000001',
            'is_default'         => 'nullable|boolean',
            'paystack_supported' => 'nullable|boolean',
            'active'             => 'nullable|boolean',
            'position'           => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            if (! empty($validated['is_default'])) {
                Currency::where('is_default', true)->update(['is_default' => false]);
            }
            Currency::create([
                'code'               => strtoupper($validated['code']),
                'name'               => $validated['name'],
                'symbol'             => $validated['symbol'],
                'usd_value'          => $validated['usd_value'],
                'is_default'         => ! empty($validated['is_default']),
                'paystack_supported' => ! empty($validated['paystack_supported']),
                'active'             => (bool) ($validated['active'] ?? true),
                'position'           => $validated['position'] ?? 0,
            ]);
        });
        $this->settings->flush();

        return back()->with('success', 'Currency added.');
    }

    public function currencyUpdate(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:120',
            'symbol'             => 'required|string|max:8',
            'usd_value'          => 'required|numeric|min:0.000001',
            'is_default'         => 'nullable|boolean',
            'paystack_supported' => 'nullable|boolean',
            'active'             => 'nullable|boolean',
            'position'           => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($currency, $validated) {
            if (! empty($validated['is_default'])) {
                Currency::where('is_default', true)->where('id', '!=', $currency->id)->update(['is_default' => false]);
            }
            $currency->update([
                'name'               => $validated['name'],
                'symbol'             => $validated['symbol'],
                'usd_value'          => $validated['usd_value'],
                'is_default'         => ! empty($validated['is_default']),
                'paystack_supported' => ! empty($validated['paystack_supported']),
                'active'             => (bool) ($validated['active'] ?? true),
                'position'           => $validated['position'] ?? 0,
            ]);
            if ($currency->wasChanged('is_default') && $currency->is_default) {
                AppSetting::where('id', 1)->update(['default_currency_id' => $currency->id]);
            }
        });
        $this->settings->flush();

        return back()->with('success', 'Currency updated.');
    }

    public function currencyDelete(Currency $currency)
    {
        if ($currency->is_default) {
            return back()->with('error', 'Cannot delete the default currency.');
        }
        $currency->delete();
        return back()->with('success', 'Currency deleted.');
    }

    /* =========================================================
     * Settings
     * ========================================================= */
    public function settings()
    {
        $s = $this->settings->all();
        $currencies = Currency::where('active', true)->orderBy('code')->get();
        return view('admin.settings', compact('s', 'currencies'));
    }

    public function settingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:120',
            'logotext'            => 'nullable|string|max:60',
            'url'                 => 'nullable|string|max:300',
            'contact_email'       => 'nullable|email|max:191',
            'phone'               => 'nullable|string|max:40',
            'address'             => 'nullable|string|max:300',
            'default_currency_id' => 'required|exists:currencies,id',
            'activation_fee'      => 'required|numeric|min:0',
            'affiliate_reward'    => 'required|numeric|min:0',
            'affiliate_enabled'   => 'nullable|boolean',
            'withdraw_com'        => 'required|numeric|min:0|max:100',
            'task_com'            => 'required|numeric|min:0|max:100',
            'need_verification'   => 'nullable|boolean',
            'manual_payment'      => 'nullable|boolean',
            'ann_status'          => 'nullable|boolean',
            'ann_text'            => 'nullable|string|max:1000',
            'primary_color'       => 'nullable|string|max:20',
            'accent_color'        => 'nullable|string|max:20',
            'footer_text'         => 'nullable|string|max:500',
        ]);

        $setting = AppSetting::find(1);
        $validated['affiliate_enabled'] = ! empty($validated['affiliate_enabled']);
        $validated['need_verification'] = ! empty($validated['need_verification']);
        $validated['manual_payment'] = ! empty($validated['manual_payment']);
        $validated['ann_status'] = ! empty($validated['ann_status']);
        $setting->update($validated);
        $this->settings->flush();

        return back()->with('success', 'Settings updated successfully.');
    }

    /* =========================================================
     * Payment methods
     * ========================================================= */
    public function methods()
    {
        $depositMethods = DepositMethod::orderBy('position')->paginate(10, ['*'], 'dpage');
        $withdrawalMethods = WithdrawalMethod::whereNull('parent_id')->orderBy('position')->paginate(10, ['*'], 'wpage');
        return view('admin.methods', compact('depositMethods', 'withdrawalMethods'));
    }

    public function depositMethodStore(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:60',
            'min_amount'  => 'nullable|numeric|min:0',
            'instructions'=> 'nullable|string|max:3000',
            'active'      => 'nullable|boolean',
            'manual'      => 'nullable|boolean',
            'position'    => 'nullable|integer|min:0',
        ]);
        DepositMethod::create([
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'min_amount'  => $validated['min_amount'] ?? 0,
            'instructions'=> $validated['instructions'] ?? null,
            'active'      => (bool) ($validated['active'] ?? true),
            'manual'      => (bool) ($validated['manual'] ?? false),
            'position'    => $validated['position'] ?? 0,
        ]);
        return back()->with('success', 'Deposit method added.');
    }

    public function depositMethodUpdate(Request $request, DepositMethod $method)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:60',
            'min_amount'  => 'nullable|numeric|min:0',
            'instructions'=> 'nullable|string|max:3000',
            'active'      => 'nullable|boolean',
            'manual'      => 'nullable|boolean',
            'position'    => 'nullable|integer|min:0',
        ]);
        $method->update([
            'name'        => $validated['name'],
            'min_amount'  => $validated['min_amount'] ?? 0,
            'instructions'=> $validated['instructions'] ?? null,
            'active'      => (bool) ($validated['active'] ?? true),
            'manual'      => (bool) ($validated['manual'] ?? false),
            'position'    => $validated['position'] ?? 0,
        ]);
        return back()->with('success', 'Deposit method updated.');
    }

    public function depositMethodDelete(DepositMethod $method)
    {
        $method->delete();
        return back()->with('success', 'Deposit method deleted.');
    }

    public function withdrawalMethodStore(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:80',
            'parent_id'  => 'nullable|exists:withdrawal_methods,id',
            'min_amount' => 'nullable|numeric|min:0',
            'active'     => 'nullable|boolean',
            'gift_card'  => 'nullable|boolean',
            'position'   => 'nullable|integer|min:0',
        ]);
        WithdrawalMethod::create([
            'name'       => $validated['name'],
            'parent_id'  => $validated['parent_id'] ?? null,
            'slug'       => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'min_amount' => $validated['min_amount'] ?? 0,
            'active'     => (bool) ($validated['active'] ?? true),
            'gift_card'  => (bool) ($validated['gift_card'] ?? false),
            'position'   => $validated['position'] ?? 0,
        ]);
        return back()->with('success', 'Withdrawal method added.');
    }

    public function withdrawalMethodUpdate(Request $request, WithdrawalMethod $method)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:80',
            'min_amount' => 'nullable|numeric|min:0',
            'active'     => 'nullable|boolean',
            'gift_card'  => 'nullable|boolean',
            'position'   => 'nullable|integer|min:0',
        ]);
        $method->update([
            'name'       => $validated['name'],
            'min_amount' => $validated['min_amount'] ?? 0,
            'active'     => (bool) ($validated['active'] ?? true),
            'gift_card'  => (bool) ($validated['gift_card'] ?? false),
            'position'   => $validated['position'] ?? 0,
        ]);
        return back()->with('success', 'Withdrawal method updated.');
    }

    public function withdrawalMethodDelete(WithdrawalMethod $method)
    {
        if ($method->children()->exists()) {
            return back()->with('error', 'Delete the sub-methods first.');
        }
        $method->delete();
        return back()->with('success', 'Withdrawal method deleted.');
    }

    /* =========================================================
     * Transactions
     * ========================================================= */
    public function transactions(Request $request)
    {
        $query = Transaction::with('user:id,username,name,email');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($q = $request->input('q')) {
            $query->whereHas('user', fn ($u) => $u->where('username', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))
                ->orWhere('reference', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%");
        }

        $transactions = $query->latest()->paginate(25)->appends($request->query());

        $summary = [
            'total_in'  => (float) Transaction::whereIn('type', ['deposit', 'task_credit', 'affiliate_reward', 'withdrawal_refund', 'task_refund'])->sum('amount'),
            'total_out' => (float) abs(Transaction::whereIn('type', ['withdrawal', 'task_creation'])->sum('amount')),
            'count'     => Transaction::count(),
        ];

        return view('admin.transactions', compact('transactions', 'summary'));
    }

    /* =========================================================
     * Affiliate program management
     * ========================================================= */
    public function affiliate(Request $request)
    {
        $query = AffiliateReferral::with(['referrer:id,username,name,email', 'referee:id,username,name,is_active,activated_at']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $referrals = $query->latest()->paginate(20)->appends($request->query());

        $topReferrers = User::withCount(['affiliateReferrals as paid_count' => fn ($q) => $q->where('status', 'paid')])
            ->withSum(['affiliateReferrals as paid_earnings' => fn ($q) => $q->where('status', 'paid')], 'reward_amount')
            ->whereHas('affiliateReferrals', fn ($q) => $q->where('status', 'paid'))
            ->orderByDesc('paid_count')
            ->limit(10)
            ->get();

        $stats = [
            'total_referrals'  => AffiliateReferral::count(),
            'paid_referrals'   => AffiliateReferral::where('status', 'paid')->count(),
            'pending_referrals'=> AffiliateReferral::where('status', 'pending')->count(),
            'total_paid_out'   => (float) AffiliateReferral::where('status', 'paid')->sum('reward_amount'),
        ];

        return view('admin.affiliate', compact('referrals', 'topReferrers', 'stats'));
    }

    public function affiliateRevoke(Request $request, AffiliateReferral $referral)
    {
        $admin = Auth::guard('admin')->user();
        if (! $admin->isSuper()) {
            return back()->with('error', 'Only super admins can revoke affiliate rewards.');
        }

        $request->validate(['revoke_reason' => 'required|string|max:500']);

        DB::transaction(function () use ($referral, $request) {
            if ($referral->status === 'paid') {
                // Debit the reward back from the referrer.
                $this->wallet->debit($referral->referrer, (float) $referral->reward_amount, 'affiliate_revoke', [
                    'reference'   => 'AFFREVOKE-'.$referral->id,
                    'description' => 'Affiliate reward revoked: '.$request->input('revoke_reason'),
                    'related_id'  => $referral->id,
                    'related_type'=> AffiliateReferral::class,
                ]);
            }
            $referral->update([
                'status'        => 'revoked',
                'revoke_reason' => $request->input('revoke_reason'),
            ]);
        });

        return back()->with('success', 'Affiliate reward revoked.');
    }

    /* =========================================================
     * LOGIN AS USER (Impersonation)
     * ========================================================= */
    public function loginAsUser(User $user)
    {
        abort_unless(Auth::guard('admin')->user()?->isSuper(), 403, 'Super administrator access required.');
        if ($user->banned) {
            return back()->with('error', 'Cannot login as a banned user.');
        }
        // Store the admin ID in the session so we can return later.
        session(['impersonating_admin' => Auth::guard('admin')->id()]);
        Auth::guard('web')->login($user);
        return redirect()->route('user.dashboard')->with('success', "You are now logged in as {$user->username}.");
    }

    public function stopImpersonating(Request $request)
    {
        $adminId = $request->session()->pull('impersonating_admin');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($adminId) {
            $admin = \App\Models\Admin::find($adminId);
            if ($admin) {
                Auth::guard('admin')->login($admin);
                return redirect()->route('admin.users')->with('success', 'Returned to admin panel.');
            }
        }
        return redirect()->route('admin.login');
    }

    /* =========================================================
     * ADS MANAGEMENT
     * ========================================================= */
    public function ads()
    {
        $ads = Ad::latest()->paginate(20);
        return view('admin.ads', compact('ads'));
    }

    public function adStore(Request $request)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:120',
            'position'   => 'required|string|max:60',
            'type'       => 'required|in:html,image,text',
            'content'    => 'nullable|string|max:10000',
            'link_url'   => 'nullable|string|max:500',
            'active'     => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            $imagePath = $request->file('image_file')->store('ads', 'public');
        }

        Ad::create([
            'title'      => $validated['title'],
            'position'   => $validated['position'],
            'type'       => $validated['type'],
            'content'    => $validated['content'] ?? null,
            'link_url'   => $validated['link_url'] ?? null,
            'image_path' => $imagePath,
            'active'     => (bool) ($validated['active'] ?? true),
            'sort_order' => $validated['sort_order'] ?? 0,
            'starts_at'  => $validated['starts_at'] ?? null,
            'ends_at'    => $validated['ends_at'] ?? null,
        ]);

        return back()->with('success', 'Ad created successfully.');
    }

    public function adUpdate(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'title'      => 'required|string|max:120',
            'position'   => 'required|string|max:60',
            'type'       => 'required|in:html,image,text',
            'content'    => 'nullable|string|max:10000',
            'link_url'   => 'nullable|string|max:500',
            'active'     => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
            if ($ad->image_path) {
                Storage::disk('public')->delete($ad->image_path);
            }
            $validated['image_path'] = $request->file('image_file')->store('ads', 'public');
        }

        $validated['active'] = (bool) ($validated['active'] ?? false);
        $ad->update($validated);

        return back()->with('success', 'Ad updated successfully.');
    }

    public function adDelete(Ad $ad)
    {
        if ($ad->image_path) {
            Storage::disk('public')->delete($ad->image_path);
        }
        $ad->delete();
        return back()->with('success', 'Ad deleted.');
    }

    /* =========================================================
     * APPEARANCE: Logo, Favicon, Universal CSS, HTML injection
     * ========================================================= */
    public function appearance()
    {
        $s = $this->settings->all();
        $customCss = SiteSetting::get('custom_css', '');
        $headerHtml = SiteSetting::get('header_html', '');
        $footerHtml = SiteSetting::get('footer_html', '');
        $bodyTopHtml = SiteSetting::get('body_top_html', '');
        $bodyBottomHtml = SiteSetting::get('body_bottom_html', '');
        $sidebarHtml = SiteSetting::get('sidebar_html', '');
        return view('admin.appearance', compact('s', 'customCss', 'headerHtml', 'footerHtml', 'bodyTopHtml', 'bodyBottomHtml', 'sidebarHtml'));
    }

    public function appearanceUpdate(Request $request)
    {
        $request->validate([
            'primary_color' => 'nullable|string|max:20',
            'accent_color'  => 'nullable|string|max:20',
            'custom_css'    => 'nullable|string|max:50000',
            'header_html'   => 'nullable|string|max:50000',
            'footer_html'   => 'nullable|string|max:50000',
            'body_top_html' => 'nullable|string|max:50000',
            'body_bottom_html' => 'nullable|string|max:50000',
            'sidebar_html'  => 'nullable|string|max:50000',
        ]);

        $setting = AppSetting::find(1);
        $setting->update([
            'primary_color' => $request->input('primary_color', '#2563eb'),
            'accent_color'  => $request->input('accent_color', '#1e40af'),
        ]);

        SiteSetting::set('custom_css', site_injected_html($request->input('custom_css', '')), 'appearance');
        SiteSetting::set('header_html', site_injected_html($request->input('header_html', '')), 'appearance');
        SiteSetting::set('footer_html', site_injected_html($request->input('footer_html', '')), 'appearance');
        SiteSetting::set('body_top_html', site_injected_html($request->input('body_top_html', '')), 'appearance');
        SiteSetting::set('body_bottom_html', site_injected_html($request->input('body_bottom_html', '')), 'appearance');
        SiteSetting::set('sidebar_html', site_injected_html($request->input('sidebar_html', '')), 'appearance');

        $this->settings->flush();
        SiteSetting::flushCache();

        return back()->with('success', 'Appearance & custom code updated successfully.');
    }

    /* =========================================================
     * ERROR SCREEN CONTENT MANAGEMENT (admin "god-mode")
     * Controls the branded "We Couldn't Process Your Request."
     * page shown when a destination doesn't exist / can't fetch.
     * ========================================================= */
    public function errorScreenUpdate(Request $request)
    {
        $request->validate([
            'error_heading'       => 'nullable|string|max:200',
            'error_subtext'       => 'nullable|string|max:600',
            'error_accent_color'  => 'nullable|string|max:20',
            'error_show_code'     => 'nullable|boolean',
        ]);

        SiteSetting::set('error_heading',      $request->input('error_heading', "We Couldn't Process Your Request."), 'appearance');
        SiteSetting::set('error_subtext',      $request->input('error_subtext', 'The page you\'re looking for may have moved, is temporarily unavailable, or couldn\'t be loaded right now. Please try again in a moment.'), 'appearance');
        SiteSetting::set('error_accent_color', $request->input('error_accent_color', '#2563eb'), 'appearance');
        SiteSetting::set('error_show_code',    $request->boolean('error_show_code') ? '1' : '0', 'appearance');

        SiteSetting::flushCache();

        return back()->with('success', 'Error screen content updated successfully.');
    }

    public function uploadErrorLogo(Request $request)
    {
        $request->validate(['error_logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048']);
        $existing = SiteSetting::get('error_logo');
        if ($existing) {
            Storage::disk('public')->delete($existing);
        }
        $path = $request->file('error_logo')->store('logos', 'public');
        SiteSetting::set('error_logo', $path, 'appearance');
        SiteSetting::flushCache();
        return back()->with('success', 'Error screen logo updated successfully.');
    }

    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048']);
        $setting = AppSetting::find(1);
        if ($setting->logo) {
            Storage::disk('public')->delete($setting->logo);
        }
        $path = $request->file('logo')->store('logos', 'public');
        $setting->update(['logo' => $path]);
        $this->settings->flush();
        return back()->with('success', 'Logo updated successfully.');
    }

    public function uploadFavicon(Request $request)
    {
        $request->validate(['favicon' => 'required|image|mimes:png,jpg,jpeg,ico,svg,webp|max:1024']);
        $setting = AppSetting::find(1);
        if ($setting->favicon) {
            Storage::disk('public')->delete($setting->favicon);
        }
        $path = $request->file('favicon')->store('logos', 'public');
        $setting->update(['favicon' => $path]);
        $this->settings->flush();
        return back()->with('success', 'Favicon updated successfully.');
    }

    /* =========================================================
     * EMAIL / SMTP SETTINGS
     * ========================================================= */
    public function emailSettings()
    {
        $config = [
            'mail_mailer'      => SiteSetting::get('mail_mailer', config('mail.default', 'smtp')),
            'mail_host'        => SiteSetting::get('mail_host', config('mail.mailers.smtp.host', '')),
            'mail_port'        => SiteSetting::get('mail_port', config('mail.mailers.smtp.port', '587')),
            'mail_username'    => SiteSetting::get('mail_username', ''),
            'mail_password'    => SiteSetting::get('mail_password', ''),
            'mail_encryption'  => SiteSetting::get('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')),
            'mail_from_address'=> SiteSetting::get('mail_from_address', config('mail.from.address', '')),
            'mail_from_name'   => SiteSetting::get('mail_from_name', config('mail.from.name', '')),
        ];
        $appSetting = AppSetting::find(1);
        return view('admin.email-settings', compact('config', 'appSetting'));
    }

    public function emailSettingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer'      => 'required|in:smtp,sendmail,mailgun,ses,log',
            'mail_host'        => 'nullable|string|max:200',
            'mail_port'        => 'nullable|integer|min:1|max:65535',
            'mail_username'    => 'nullable|string|max:200',
            'mail_password'    => 'nullable|string|max:200',
            'mail_encryption'  => 'nullable|in:tls,ssl,null',
            'mail_from_address'=> 'required|email|max:200',
            'mail_from_name'   => 'required|string|max:120',
            'need_verification'=> 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'mail_encryption' && $value === 'null') {
                $value = null;
            }
            if ($key === 'need_verification') {
                continue; // handled via AppSetting below
            }
            SiteSetting::set($key, $value, 'email');
        }

        // Toggle email verification on registration (AppSetting single-row).
        $appSetting = AppSetting::find(1);
        if ($appSetting) {
            $appSetting->update(['need_verification' => ! empty($validated['need_verification'])]);
            $this->settings->flush();
        }

        // Also write to .env so the mailer picks it up immediately.
        $this->writeMailEnv($validated);

        SiteSetting::flushCache();
        Artisan::call('config:clear');

        return back()->with('success', 'Email settings saved. Use "Send Test Email" to verify.');
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate(['test_email' => 'required|email']);
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "This is a test email from {$this->settings->get('name', 'Airtrendmedia')}. If you received this, your SMTP settings are working correctly!",
                function ($message) use ($request) {
                    $message->to($request->input('test_email'))
                        ->subject('Test Email — ' . $this->settings->get('name', 'Airtrendmedia'));
                }
            );
            return back()->with('success', 'Test email sent successfully! Check your inbox (and spam folder).');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    private function writeMailEnv(array $validated): void
    {
        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            return;
        }
        $content = File::get($envPath);
        $replacements = [
            'MAIL_MAILER'       => $validated['mail_mailer'] ?? 'smtp',
            'MAIL_HOST'         => $validated['mail_host'] ?? '',
            'MAIL_PORT'         => $validated['mail_port'] ?? '587',
            'MAIL_USERNAME'     => $validated['mail_username'] ?? '',
            'MAIL_PASSWORD'     => $validated['mail_password'] ?? '',
            'MAIL_ENCRYPTION'   => $validated['mail_encryption'] ?? 'tls',
            'MAIL_FROM_ADDRESS' => $validated['mail_from_address'] ?? '',
            'MAIL_FROM_NAME'    => $validated['mail_from_name'] ?? '',
        ];
        foreach ($replacements as $key => $value) {
            if ($value === null) {
                $value = '';
            }
            // If the key exists, replace it; otherwise append.
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }
        File::put($envPath, $content);
    }

    /* =========================================================
     * PAYMENT API KEYS (Paystack) — managed from admin
     * ========================================================= */
    public function paymentKeys()
    {
        $keys = [
            'paystack_public_key' => SiteSetting::get('paystack_public_key', config('services.paystack.public_key', '')),
            'paystack_secret_key' => SiteSetting::get('paystack_secret_key', config('services.paystack.secret_key', '')),
            'paystack_base_url'   => SiteSetting::get('paystack_base_url', config('services.paystack.base_url', 'https://api.paystack.co')),
            'paystack_currency'   => SiteSetting::get('paystack_currency', config('services.paystack.currency', 'NGN')),
            'paystack_callback_url' => SiteSetting::get('paystack_callback_url', config('services.paystack.callback_url', '')),
            'openai_api_key' => SiteSetting::get('openai_api_key',''),
            'openai_model' => SiteSetting::get('openai_model','gpt-4.1-mini'),
            'ai_image_model' => SiteSetting::get('ai_image_model','gpt-image-1'),
            'flutterwave_public_key' => SiteSetting::get('flutterwave_public_key',''),
            'flutterwave_secret_key' => SiteSetting::get('flutterwave_secret_key',''),
            'flutterwave_base_url' => SiteSetting::get('flutterwave_base_url','https://api.flutterwave.com/v3'),
            'flutterwave_webhook_secret' => SiteSetting::get('flutterwave_webhook_secret',''),
            'payoneer_api_key' => SiteSetting::get('payoneer_api_key',''),
            'payoneer_api_secret' => SiteSetting::get('payoneer_api_secret',''),
            'bank_payout_api_key' => SiteSetting::get('bank_payout_api_key',''),
            'usdt_payout_api_key' => SiteSetting::get('usdt_payout_api_key',''),
            'withdrawal_webhook_secret' => SiteSetting::get('withdrawal_webhook_secret',''),
        ];
        return view('admin.payment-keys', compact('keys'));
    }

    public function paymentKeysUpdate(Request $request)
    {
        $validated = $request->validate([
            'paystack_public_key'   => 'nullable|string|max:200',
            'paystack_secret_key'   => 'nullable|string|max:200',
            'paystack_base_url'     => 'nullable|string|max:200',
            'paystack_currency'     => 'nullable|string|max:10',
            'paystack_callback_url' => 'nullable|string|max:300',
            'openai_api_key' => 'nullable|string|max:255', 'openai_model'=>'nullable|string|max:80', 'ai_image_model'=>'nullable|string|max:80',
            'flutterwave_public_key'=>'nullable|string|max:255','flutterwave_secret_key'=>'nullable|string|max:255','flutterwave_base_url'=>'nullable|url|max:255','flutterwave_webhook_secret'=>'nullable|string|max:255',
            'payoneer_api_key'=>'nullable|string|max:255','payoneer_api_secret'=>'nullable|string|max:255','bank_payout_api_key'=>'nullable|string|max:255','usdt_payout_api_key'=>'nullable|string|max:255','withdrawal_webhook_secret'=>'nullable|string|max:255',
        ]);

        foreach ($validated as $key => $value) {
            SiteSetting::set($key, $value, 'payment');
        }

        // Write to .env
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $content = File::get($envPath);
            $map = [
                'PAYSTACK_PUBLIC_KEY'   => $validated['paystack_public_key'] ?? '',
                'PAYSTACK_SECRET_KEY'   => $validated['paystack_secret_key'] ?? '',
                'PAYSTACK_BASE_URL'     => $validated['paystack_base_url'] ?? 'https://api.paystack.co',
                'PAYSTACK_CURRENCY'     => $validated['paystack_currency'] ?? 'NGN',
                'PAYSTACK_CALLBACK_URL' => $validated['paystack_callback_url'] ?? '',
            ];
            foreach ($map as $key => $value) {
                if (preg_match("/^{$key}=/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
                } else {
                    $content .= "\n{$key}={$value}";
                }
            }
            File::put($envPath, $content);
        }

        SiteSetting::flushCache();
        Artisan::call('config:clear');

        return back()->with('success', 'Payment API keys saved successfully.');
    }

    /* =========================================================
     * SYSTEM UPDATE (GitHub auto-update) + AI AUTO-CORRECTION
     * ========================================================= */
    public function systemUpdate()
    {
        $currentVersion = SiteSetting::get('system_version', '1.0.0');
        $repoUrl = 'https://github.com/primemini016-lang/Airtrendmedia';
        $branch = 'main';
        $lastUpdate = SiteSetting::get('last_update_at', null);
        $updateLog = SiteSetting::get('update_log', '');

        // AI auto-correction report (most recent self-healing sweep).
        $healthReport = SystemHealthService::lastReport();

        // Live environment health snapshot for the diagnostics panel.
        $healthStatus = $this->buildHealthStatus();

        return view('admin.system-update', compact('currentVersion', 'repoUrl', 'branch', 'lastUpdate', 'updateLog', 'healthReport', 'healthStatus'));
    }

    /**
     * Build a live health snapshot shown in the AI Auto-Correction panel.
     */
    protected function buildHealthStatus(): array
    {
        $status = [];

        $status['app_key'] = ! empty(config('app.key')) && strlen((string) config('app.key')) >= 20;
        $status['jwt_secret'] = ! empty(config('jwt.secret')) && strlen((string) config('jwt.secret')) >= 16;
        $status['env_file'] = file_exists(base_path('.env'));
        $status['storage_writable'] = is_writable(storage_path());
        $status['bootstrap_cache_writable'] = is_writable(base_path('bootstrap/cache'));
        $status['storage_link'] = is_link(public_path('storage')) || file_exists(public_path('storage'));
        $status['logs_writable'] = is_writable(storage_path('logs')) || is_writable(storage_path('logs/laravel.log'));
        $status['installed'] = file_exists(storage_path('app/installed.json'));

        $status['db_connected'] = false;
        try {
            DB::select('SELECT 1');
            $status['db_connected'] = true;
        } catch (\Throwable $e) {
            $status['db_connected'] = false;
        }

        $passCount = count(array_filter($status));
        $status['_pass_count'] = $passCount;
        $status['_total'] = count($status) - 2; // exclude the two _ meta keys
        $status['_overall'] = $passCount === $status['_total'];

        return $status;
    }

    /**
     * Run the AI auto-correction sweep on demand (admin "Run Diagnostics"
     * button). Bypasses the throttle so the admin always gets a fresh report.
     */
    public function runDiagnostics(Request $request)
    {
        $report = SystemHealthService::sweep(false);

        return back()->with('success', 'AI auto-correction sweep complete — ' . count($report) . ' check(s) ran.')
                     ->with('diagnosticsReport', $report);
    }

    public function updateGithubToken(Request $request)
    {
        $validated = $request->validate(['github_token'=>'nullable|string|max:500']);
        SiteSetting::set('github_token', trim((string)($validated['github_token'] ?? '')), 'system');
        return back()->with('success','GitHub updater credentials saved.');
    }

    public function clearImageCache(Request $request)
    {
        $version = (int) cache()->get('airtrend:image_cache_version', 1) + 1;
        cache()->forever('airtrend:image_cache_version', $version);
        try { SiteSetting::set('image_cache_last_purged_at', now()->toDateTimeString(), 'system'); } catch (\Throwable $e) {}
        return back()->with('success', 'Image cache version advanced. Browsers/CDNs will fetch fresh media as needed.');
    }

    public function runSystemUpdate(Request $request)
    {
        $request->validate([
            'branch' => 'nullable|string|max:50',
        ]);

        $branch = $request->input('branch', 'main');
        $repoUrl = 'https://github.com/primemini016-lang/Airtrendmedia.git';
        $log = [];
        $log[] = '[' . now() . '] Starting system update from ' . $repoUrl . ' (branch: ' . $branch . ')';

        try {
            $basePath = base_path();
            $tempDir = storage_path('app/system_update_' . time());

            // Step 1: Clone or fetch the latest code
            $log[] = 'Cloning repository...';
            $cloneCmd = "git clone --depth 1 --branch " . escapeshellarg($branch) . " " . escapeshellarg($repoUrl) . " " . escapeshellarg($tempDir) . " 2>&1";
            exec($cloneCmd, $cloneOutput, $cloneCode);
            if ($cloneCode !== 0) {
                // Try with token if available
                $token = SiteSetting::get('github_token', config('services.github_token') ?: getenv('GITHUB_TOKEN'));
                if ($token) {
                    $authRepoUrl = "https://x-access-token:{$token}@github.com/primemini016-lang/Airtrendmedia.git";
                    $cloneCmd = "git clone --depth 1 --branch " . escapeshellarg($branch) . " " . escapeshellarg($authRepoUrl) . " " . escapeshellarg($tempDir) . " 2>&1";
                    exec($cloneCmd, $cloneOutput, $cloneCode);
                }
            }

            if ($cloneCode !== 0 || ! is_dir($tempDir)) {
                throw new \Exception('Failed to clone repository: ' . implode("\n", $cloneOutput));
            }
            $log[] = 'Repository cloned successfully.';

            // Step 2: Backup current .env and database
            $log[] = 'Backing up current .env file...';
            if (File::exists($basePath . '/.env')) {
                File::copy($basePath . '/.env', $basePath . '/.env.backup_' . time());
            }

            // Step 3: Copy new files (excluding .env, storage, vendor)
            $log[] = 'Copying updated files...';
            $excludeDirs = ['.git', '.env', 'storage', 'vendor', 'node_modules'];
            $this->recursiveCopy($tempDir, $basePath, $excludeDirs);
            $log[] = 'Files updated successfully.';

            // Step 4: Install/update dependencies
            $log[] = 'Installing Composer dependencies...';
            $composerCmd = "cd " . escapeshellarg($basePath) . " && composer install --no-dev --optimize-autoloader 2>&1";
            exec($composerCmd, $composerOutput, $composerCode);
            $log[] = 'Composer: ' . (end($composerOutput) ?: 'done');
            if ($composerCode !== 0) {
                $log[] = 'Warning: Composer install had issues, continuing anyway.';
            }

            // Step 5: Run migrations
            $log[] = 'Running database migrations...';
            Artisan::call('migrate', ['--force' => true]);
            $log[] = 'Migrations: ' . Artisan::output();

            // Step 6: Clear caches
            $log[] = 'Clearing caches...';
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            $log[] = 'Caches cleared.';

            // Step 7: Optimize
            $log[] = 'Optimizing...';
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            $log[] = 'Optimization complete.';

            // Cleanup temp dir
            $this->recursiveDelete($tempDir);

            $log[] = '[' . now() . '] System update completed successfully!';

            SiteSetting::set('last_update_at', now()->toDateTimeString(), 'system');
            SiteSetting::set('update_log', implode("\n", $log), 'system');
            SiteSetting::flushCache();

            return back()->with('success', 'System updated successfully from GitHub!');

        } catch (\Throwable $e) {
            $log[] = 'ERROR: ' . $e->getMessage();
            SiteSetting::set('update_log', implode("\n", $log), 'system');
            SiteSetting::flushCache();
            if (isset($tempDir) && is_dir($tempDir)) {
                $this->recursiveDelete($tempDir);
            }
            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    private function recursiveCopy(string $src, string $dst, array $exclude = []): void
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (in_array($file, $exclude, true)) {
                continue;
            }
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                if (! is_dir($dstPath)) {
                    mkdir($dstPath, 0755, true);
                }
                $this->recursiveCopy($srcPath, $dstPath, $exclude);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }

    private function recursiveDelete(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $fileinfo->isDir() ? rmdir($fileinfo->getRealPath()) : unlink($fileinfo->getRealPath());
        }
        rmdir($dir);
    }

    /* =========================================================
     * GIGS MANAGEMENT
     * ========================================================= */
    public function gigs(Request $request)
    {
        $query = Gig::with('user:id,username,name', 'category:id,name');
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        $gigs = $query->latest()->paginate(20)->appends($request->query());
        return view('admin.gigs', compact('gigs'));
    }

    public function gigApprove(Gig $gig)
    {
        $gig->update(['status' => 'active']);
        $this->notifications->notify($gig->user, 'system', 'Gig Approved', "Your gig \"{$gig->title}\" has been approved and is now live.", route('user.gigs'));
        return back()->with('success', 'Gig approved.');
    }

    public function gigReject(Request $request, Gig $gig)
    {
        $request->validate(['reject_note' => 'required|string|max:500']);
        $gig->update(['status' => 'rejected']);
        $this->notifications->notify($gig->user, 'system', 'Gig Rejected', "Your gig \"{$gig->title}\" was rejected.", route('user.gigs'));
        return back()->with('success', 'Gig rejected.');
    }

    public function gigDelete(Gig $gig)
    {
        $gig->delete();
        return back()->with('success', 'Gig deleted.');
    }

    /* =========================================================
     * MARKETPLACE MANAGEMENT
     * ========================================================= */
    public function marketplace(Request $request)
    {
        $query = MarketplaceListing::with('user:id,username,name', 'category:id,name');
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        $listings = $query->latest()->paginate(20)->appends($request->query());
        return view('admin.marketplace', compact('listings'));
    }

    public function marketplaceApprove(MarketplaceListing $listing)
    {
        $listing->update(['status' => 'active']);
        $this->notifications->notify($listing->user, 'system', 'Listing Approved', "Your listing \"{$listing->title}\" has been approved.", route('marketplace'));
        return back()->with('success', 'Listing approved.');
    }

    public function marketplaceReject(Request $request, MarketplaceListing $listing)
    {
        $request->validate(['reject_note' => 'required|string|max:500']);
        $listing->update(['status' => 'rejected']);
        $this->notifications->notify($listing->user, 'system', 'Listing Rejected', "Your listing \"{$listing->title}\" was rejected.", route('marketplace'));
        return back()->with('success', 'Listing rejected.');
    }

    public function marketplaceDelete(MarketplaceListing $listing)
    {
        $listing->delete();
        return back()->with('success', 'Listing deleted.');
    }

    /* =========================================================
     * NOTIFICATIONS (Admin can send broadcast)
     * ========================================================= */
    public function adminNotifications()
    {
        $recentNotifications = \App\Models\Notification::with('user:id,username,name')->latest()->limit(50)->get();
        return view('admin.notifications', compact('recentNotifications'));
    }

    public function sendBroadcastNotification(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'body'  => 'required|string|max:1000',
            'url'   => 'nullable|string|max:500',
        ]);
        $this->notifications->broadcast('system', $validated['title'], $validated['body'], $validated['url'] ?? null);
        return back()->with('success', 'Notification sent to all users.');
    }

    /* ====================================================================
    |  Airtrendmedia — God-mode Admin Extensions
    |===================================================================== */

    /**
     * Admin: anti-cheat flags dashboard.
     */
    public function antiCheat(Request $request)
    {
        $query = AntiCheatFlag::with('user:id,username,name,email,image');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $flags = $query->latest()->paginate(20);

        $stats = [
            'total'    => AntiCheatFlag::count(),
            'open'     => AntiCheatFlag::where('status', 'open')->count(),
            'resolved' => AntiCheatFlag::where('status', 'resolved')->count(),
            'high'     => AntiCheatFlag::where('severity', 'high')->count(),
        ];

        $enforceOnePerIp = (bool) $this->settings->get('anti_cheat_one_account_per_ip', true);
        $threshold = (int) $this->settings->get('anti_cheat_action_threshold', 100);

        return view('admin.anti-cheat', compact('flags', 'stats', 'enforceOnePerIp', 'threshold'));
    }

    /**
     * Admin: resolve an anti-cheat flag.
     */
    public function antiCheatResolve(Request $request, AntiCheatFlag $flag)
    {
        $admin = auth('admin')->user();
        $flag->update([
            'status'      => 'resolved',
            'resolved_by' => $admin?->id,
            'resolved_at' => now(),
        ]);
        return back()->with('success', 'Anti-cheat flag resolved.');
    }

    /**
     * Admin: dismiss an anti-cheat flag.
     */
    public function antiCheatDismiss(Request $request, AntiCheatFlag $flag)
    {
        $admin = auth('admin')->user();
        $flag->update([
            'status'      => 'dismissed',
            'resolved_by' => $admin?->id,
            'resolved_at' => now(),
        ]);
        return back()->with('success', 'Anti-cheat flag dismissed.');
    }

    /**
     * Admin: PWA settings page.
     */
    public function pwaSettings()
    {
        $s = $this->settings->all();
        return view('admin.pwa-settings', compact('s'));
    }

    /**
     * Admin: save PWA settings (manifest, service worker, offline page, theme).
     */
    public function pwaSettingsSave(Request $request)
    {
        $validated = $request->validate([
            'pwa_enabled'              => ['nullable', 'boolean'],
            'pwa_app_name'             => ['nullable', 'string', 'max:100'],
            'pwa_short_name'           => ['nullable', 'string', 'max:30'],
            'pwa_theme_color'          => ['nullable', 'string', 'max:20'],
            'pwa_background_color'     => ['nullable', 'string', 'max:20'],
            'pwa_display'              => ['nullable', 'in:standalone,fullscreen,minimal-ui,browser'],
            'pwa_offline_message'      => ['nullable', 'string', 'max:500'],
            'pwa_offline_enabled'      => ['nullable', 'boolean'],
            'pwa_description'          => ['nullable', 'string', 'max:500'],
            'pwa_orientation'          => ['nullable', 'in:any,portrait,landscape'],
            'pwa_custom_css'           => ['nullable', 'string'],
            'pwa_custom_js'            => ['nullable', 'string'],
            'pwa_native_app_behavior'  => ['nullable', 'boolean'],
        ]);

        $s = $this->settings->all();
        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $s->$key = $value;
            }
        }
        // Handle booleans
        $s->pwa_enabled = $request->boolean('pwa_enabled');
        $s->pwa_offline_enabled = $request->boolean('pwa_offline_enabled');
        $s->pwa_native_app_behavior = $request->boolean('pwa_native_app_behavior');
        $s->save();
        $this->settings->flush();

        // Regenerate manifest.json
        $this->generatePwaManifest();

        return back()->with('success', 'PWA settings saved and manifest regenerated.');
    }

    /**
     * Generate the PWA manifest.json based on settings.
     */
    private function generatePwaManifest(): void
    {
        $s = $this->settings->all();
        $manifest = [
            'name'             => $s->pwa_app_name ?? 'Airtrendmedia',
            'short_name'       => $s->pwa_short_name ?? 'Airtrendmedia',
            'description'      => $s->pwa_description ?? 'All-in-one social media, micro-jobs & advertising platform',
            'start_url'        => '/',
            'display'          => $s->pwa_display ?? 'standalone',
            'orientation'      => $s->pwa_orientation ?? 'any',
            'theme_color'      => $s->pwa_theme_color ?? '#1877F2',
            'background_color' => $s->pwa_background_color ?? '#FFFFFF',
            'icons'            => [
                [
                    'src'   => $s->logo ? storage_asset($s->logo) : asset('images/icon-192.png'),
                    'sizes' => '192x192',
                    'type'  => 'image/png',
                ],
                [
                    'src'   => $s->logo ? storage_asset($s->logo) : asset('images/icon-512.png'),
                    'sizes' => '512x512',
                    'type'  => 'image/png',
                ],
            ],
        ];

        File::put(public_path('manifest.json'), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Admin: Banner & Popup settings page.
     * Manages header banner, footer banner, popup banner (with description text),
     * and PWA install popup banner — all admin-controlled.
     */
    public function bannerSettings()
    {
        $s = $this->settings->all();
        return view('admin.banner-settings', compact('s'));
    }

    /**
     * Admin: Save banner & popup settings.
     */
    public function bannerSettingsSave(Request $request)
    {
        $validated = $request->validate([
            'header_banner_enabled'      => ['nullable', 'boolean'],
            'header_banner_text'         => ['nullable', 'string', 'max:500'],
            'header_banner_bg_color'     => ['nullable', 'string', 'max:20'],
            'header_banner_text_color'   => ['nullable', 'string', 'max:20'],
            'header_banner_link'         => ['nullable', 'string', 'max:500'],
            'footer_banner_enabled'      => ['nullable', 'boolean'],
            'footer_banner_text'         => ['nullable', 'string', 'max:500'],
            'footer_banner_bg_color'     => ['nullable', 'string', 'max:20'],
            'footer_banner_text_color'   => ['nullable', 'string', 'max:20'],
            'footer_banner_link'         => ['nullable', 'string', 'max:500'],
            'popup_banner_enabled'       => ['nullable', 'boolean'],
            'popup_banner_title'         => ['nullable', 'string', 'max:200'],
            'popup_banner_description'   => ['nullable', 'string', 'max:2000'],
            'popup_banner_image'         => ['nullable', 'string', 'max:500'],
            'popup_banner_link'          => ['nullable', 'string', 'max:500'],
            'popup_banner_link_text'     => ['nullable', 'string', 'max:100'],
            'popup_banner_bg_color'      => ['nullable', 'string', 'max:20'],
            'popup_banner_delay_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'popup_banner_show_again_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
            'pwa_popup_enabled'          => ['nullable', 'boolean'],
            'pwa_popup_title'            => ['nullable', 'string', 'max:200'],
            'pwa_popup_description'      => ['nullable', 'string', 'max:2000'],
            'pwa_popup_install_btn_text' => ['nullable', 'string', 'max:100'],
            'pwa_popup_dismiss_btn_text' => ['nullable', 'string', 'max:100'],
        ]);

        $s = $this->settings->all();
        foreach ($validated as $key => $value) {
            if ($value !== null) {
                $s->$key = $value;
            }
        }
        // Handle booleans
        $boolFields = [
            'header_banner_enabled', 'footer_banner_enabled', 'popup_banner_enabled', 'pwa_popup_enabled',
        ];
        foreach ($boolFields as $field) {
            $s->$field = $request->boolean($field);
        }
        $s->save();
        $this->settings->flush();

        return back()->with('success', 'Banner & Popup settings saved successfully.');
    }
}
