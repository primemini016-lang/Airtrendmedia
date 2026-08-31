<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Services\MediaUploadService;
use App\Models\Country;
use App\Models\Deposit;
use App\Models\DepositMethod;
use App\Models\Faq;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Task;
use App\Models\TaskBooking;
use App\Models\TaskCategory;
use App\Models\TaskProof;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\WithdrawalMethod;
use App\Services\ActivationService;
use App\Services\AffiliateService;
use App\Services\PaystackService;
use App\Services\SettingService;
use App\Services\WalletService;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

/**
 * User dashboard controller (Blade / session-based).
 *
 * Handles the full user experience: dashboard, activation + $5 Paystack flow,
 * browsing & booking tasks, submitting proofs, managing offers (as employer),
 * wallet deposits, withdrawals, transactions, affiliate dashboard, profile,
 * and admin messaging.
 */
class DashboardController extends Controller
{
    public function __construct(
        private SettingService $settings,
        private PaystackService $paystack,
        private \App\Services\FlutterwaveService $flutterwave,
    ) {}

    /* =========================================================
     * Dashboard
     * ========================================================= */
    public function index()
    {
        $user = auth('web')->user()->load(['transactions' => fn ($q) => $q->limit(8)]);

        $openBookings = $user->bookings()->with('task')
            ->whereHas('task', fn ($q) => $q->where('status', 1))
            ->latest()->limit(5)->get();

        $myActiveTasks = $user->tasks()->where('status', 1)->latest()->limit(5)->get();

        // Pending tasks: tasks the user posted that are pending approval (status=0)
        $pendingTasks = $user->tasks()->where('status', 0)->latest()->limit(5)->get();

        // Pending proofs submitted by the user (awaiting employer review, status=0)
        $pendingBookings = TaskProof::where('user_id', $user->id)
            ->where('status', 0)->with('task')->latest()->limit(5)->get();

        $pendingProofs = TaskProof::whereHas('task', fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 0)->count();

        $pendingWithdrawals = $user->withdrawals()->where('status', 0)->count();

        // Professional dashboard metrics (paidwork.com-style)
        $completedTasks = $user->proofs()->where('status', 1)->count();
        $totalGigs = $user->gigs()->count();
        $activeGigs = $user->gigs()->where('status', 'active')->count();
        $totalListings = $user->marketplaceListings()->count();
        $totalBookings = $user->bookings()->count();
        $referralCount = $user->referrals()->count();
        $unreadNotifications = $user->notifications()->where('is_read', false)->count();

        // Earnings this month
        $earningsThisMonth = $user->transactions()
            ->where('amount', '>', 0)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        // Available tasks to complete (marketplace opportunities)
        $availableTasks = \App\Models\Task::where('status', 1)->latest()->limit(4)->get();

        // The dashboard always renders the AI workspace. Keep this lookup
        // defensive so a missing/old AI-wallet table cannot cause a 500.
        $aiCredits = 0.0;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('ai_credit_wallets')) {
                $aiCredits = (float) (DB::table('ai_credit_wallets')
                    ->where('user_id', $user->id)
                    ->value('credits') ?? 0);
            }
        } catch (\Throwable $e) {
            report($e);
            $aiCredits = 0.0;
        }

        return view('user.dashboard', compact(
            'user', 'openBookings', 'myActiveTasks', 'pendingTasks', 'pendingBookings',
            'pendingProofs', 'pendingWithdrawals',
            'completedTasks', 'totalGigs', 'activeGigs', 'totalListings',
            'totalBookings', 'referralCount', 'unreadNotifications',
            'earningsThisMonth', 'availableTasks', 'aiCredits'
        ));
    }

    /* =========================================================
     * Activation ($5 fee via Paystack)
     * ========================================================= */
    public function activate()
    {
        $user = auth('web')->user();
        if ($user->is_active) {
            return redirect()->route('user.dashboard');
        }

        $s = $this->settings->all();
        $currency = $this->settings->defaultCurrency();
        $pendingDeposit = Deposit::where('user_id', $user->id)
            ->where('type', 'activation')->where('status', 0)->latest()->first();

        return view('user.activate', compact('s', 'currency', 'pendingDeposit'));
    }

    public function initiateActivation(Request $request, ActivationService $activation)
    {
        $request->validate(['callback_url' => 'nullable|url']);
        $user = auth('web')->user();

        if ($user->is_active) {
            return redirect()->route('user.dashboard')->with('success', 'Your account is already active.');
        }

        if (! $this->paystack->configured()) {
            return back()->with('error', 'Paystack is not configured yet. Please contact the administrator or use a manual deposit request.');
        }

        $callback = $request->input('callback_url', route('user.activate.verify'));
        $payload = $activation->initiate($user, $callback);

        if (! $payload || empty($payload['authorization_url'])) {
            return back()->with('error', 'Unable to start the payment. Please try again or contact support.');
        }

        return redirect()->away($payload['authorization_url']);
    }

    public function verifyActivation(Request $request, ActivationService $activation)
    {
        $reference = $request->query('reference');
        if (! $reference) {
            return redirect()->route('user.activate')->with('error', 'No payment reference was returned.');
        }

        $ok = $activation->confirmByReference($reference);
        if ($ok) {
            return redirect()->route('user.dashboard')->with('success', 'Account activated! You can now perform tasks and earn money.');
        }

        return redirect()->route('user.activate')->with('error', 'Payment could not be verified. If you have paid, please wait a moment and try again, or contact support with reference '.$reference);
    }

    /**
     * Start a 3-day free trial (no payment required).
     */
    public function startTrial(Request $request)
    {
        $user = auth('web')->user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->is_active) {
            return redirect()->route('user.dashboard')->with('info', 'Your account is already active.');
        }

        // Don't allow starting a trial if they already had one that expired
        if ($user->trial_ends_at && now()->gte($user->trial_ends_at) && ! $user->is_active) {
            return redirect()->route('user.activate')
                ->with('error', 'Your free trial has already been used. Please pay the '.money($this->settings->activationFee()).' activation fee to continue.');
        }

        // Don't restart if already on an active trial
        if ($user->isOnTrial()) {
            return redirect()->route('user.dashboard')
                ->with('info', 'You are already on a free trial with ' . $user->trialDaysLeft() . ' days left.');
        }

        $user->startTrial();
        \App\Models\AffiliateReferral::where('referee_id',$user->id)->update(['trial_started_at'=>now()]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Your 3-day free trial has started! You now have full access. After 3 days, you\'ll need to pay the '.money($this->settings->activationFee()).' activation fee.');
    }

    /* =========================================================
     * Browse & book tasks (worker side)
     * ========================================================= */
    public function browseTasks(Request $request)
    {
        $user = auth('web')->user();

        $query = Task::with('category', 'user:id,username,name,image')
            ->where('status', 1)
            ->where('user_id', '!=', $user->id)
            ->whereColumn('booked', '<', 'amount');

        if ($cat = $request->input('category')) {
            $query->where('category_id', $cat);
        }
        if ($q = $request->input('q')) {
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")->orWhere('details', 'like', "%{$q}%");
            });
        }
        if ($sort = $request->input('sort')) {
            match ($sort) {
                'price_high' => $query->orderByDesc('price'),
                'price_low'  => $query->orderBy('price'),
                default      => $query->latest(),
            };
        } else {
            $query->latest();
        }

        $tasks = $query->paginate(12)->appends($request->query());
        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();

        return view('user.browse-tasks', compact('tasks', 'categories'));
    }

    public function taskDetail(Task $task)
    {
        $user = auth('web')->user();
        $task->load('category', 'user:id,username,name,image', 'proofs');

        $myBooking = TaskBooking::where('user_id', $user->id)->where('task_id', $task->id)->first();
        $myProof = TaskProof::where('user_id', $user->id)->where('task_id', $task->id)->first();

        return view('user.task-detail', compact('task', 'myBooking', 'myProof'));
    }

    public function bookTask(Request $request, Task $task, WalletService $wallet)
    {
        $user = auth('web')->user();

        if (! $task->isActive()) {
            return back()->with('error', 'This task is not available.');
        }
        if ($task->user_id === $user->id) {
            return back()->with('error', 'You cannot book your own task.');
        }
        if ($task->booked >= $task->amount) {
            return back()->with('error', 'All slots for this task have been filled.');
        }

        $existing = TaskBooking::where('user_id', $user->id)->where('task_id', $task->id)->first();
        if ($existing) {
            return back()->with('error', 'You have already booked this task.');
        }

        DB::transaction(function () use ($task, $user) {
            $locked = Task::lockForUpdate()->find($task->id);
            if ($locked->booked >= $locked->amount) {
                throw new \Exception('Task is full.');
            }
            $locked->increment('booked');

            TaskBooking::create([
                'user_id'   => $user->id,
                'task_id'   => $task->id,
                'expire_in' => $task->time ? now()->addMinutes($task->time * 2 + 120) : now()->addHours(24),
            ]);
        });

        return redirect()->route('user.task', $task)->with('success', 'Task booked! Complete it and submit your proof before the deadline.');
    }

    public function submitProof(Request $request, Task $task)
    {
        $user = auth('web')->user();

        $request->validate([
            'comment' => 'required|string|max:3000',
            'images'  => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $booking = TaskBooking::where('user_id', $user->id)->where('task_id', $task->id)->first();
        if (! $booking) {
            return back()->with('error', 'You must book this task before submitting proof.');
        }

        $existing = TaskProof::where('user_id', $user->id)->where('task_id', $task->id)->first();
        if ($existing && in_array((int) $existing->status, [0, 1])) {
            return back()->with('error', 'You have already submitted a proof for this task.');
        }

        $paths = [];
        foreach ($request->file('images') as $file) {
            $paths[] = app(MediaUploadService::class)->storeProof($file);
        }

        DB::transaction(function () use ($user, $task, $request, $paths) {
            TaskProof::create([
                'user_id' => $user->id,
                'task_id' => $task->id,
                'comment' => $request->input('comment'),
                'images'  => $paths,
                'status'  => 0,
            ]);
            $locked = Task::lockForUpdate()->find($task->id);
            $locked->increment('submitted');
        });

        return redirect()->route('user.task', $task)->with('success', 'Proof submitted! The employer will review it shortly.');
    }

    /* =========================================================
     * My bookings (worker)
     * ========================================================= */
    public function myBookings(Request $request)
    {
        $user = auth('web')->user();
        $status = $request->input('status', 'all');

        $query = $user->bookings()->with('task.category', 'task.user:id,username');

        if ($status === 'pending') {
            $query->whereHas('task', fn ($q) => $q->whereColumn('booked', '<', 'amount'))->whereDoesntHave('proofs');
        } elseif ($status === 'submitted') {
            $query->whereHas('proofs', fn ($q) => $q->where('status', 0));
        } elseif ($status === 'approved') {
            $query->whereHas('proofs', fn ($q) => $q->where('status', 1));
        } elseif ($status === 'rejected') {
            $query->whereHas('proofs', fn ($q) => $q->where('status', 2));
        }

        $bookings = $query->latest()->paginate(15)->appends($request->query());

        return view('user.bookings', compact('bookings', 'status'));
    }

    /* =========================================================
     * My tasks / offers (employer side)
     * ========================================================= */
    public function myTasks(Request $request)
    {
        $user = auth('web')->user();
        $status = $request->input('status', 'all');

        $query = $user->tasks()->with('category', 'proofs.user:id,username,name,image');

        if ($status !== 'all') {
            $map = ['pending' => 0, 'active' => 1, 'completed' => 2, 'rejected' => 3, 'full' => 4];
            if (isset($map[$status])) {
                $query->where('status', $map[$status]);
            }
        }

        $tasks = $query->latest()->paginate(10)->appends($request->query());

        return view('user.my-tasks', compact('tasks', 'status'));
    }

    public function taskProofs(Task $task)
    {
        $user = auth('web')->user();
        if ($task->user_id !== $user->id) {
            abort(403);
        }
        $task->load('category');
        $proofs = $task->proofs()->with('user:id,username,name,image')->latest()->paginate(12);

        return view('user.task-proofs', compact('task', 'proofs'));
    }

    public function approveProof(Request $request, TaskProof $proof, WalletService $wallet)
    {
        $user = auth('web')->user();
        $task = $proof->task;

        if ($task->user_id !== $user->id) {
            abort(403);
        }
        if ((int) $proof->status !== 0) {
            return back()->with('error', 'This proof has already been processed.');
        }

        DB::transaction(function () use ($proof, $task, $user, $wallet) {
            $proof->update(['status' => 1]);
            $locked = Task::lockForUpdate()->find($task->id);
            $locked->increment('completed');

            // Pay the worker the unit price.
            $wallet->credit($proof->user, (float) $task->price, 'task_credit', [
                'reference'    => 'TASK-'.$task->code.'-'.$proof->id,
                'description'  => 'Payment for task "'.$task->title.'"',
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

    public function rejectProof(Request $request, TaskProof $proof)
    {
        $user = auth('web')->user();
        $task = $proof->task;

        if ($task->user_id !== $user->id) {
            abort(403);
        }
        if ((int) $proof->status !== 0) {
            return back()->with('error', 'This proof has already been processed.');
        }

        $request->validate(['reject_note' => 'required|string|max:1000']);

        DB::transaction(function () use ($proof, $task, $request) {
            $proof->update(['status' => 2, 'reject_note' => $request->input('reject_note')]);
            $locked = Task::lockForUpdate()->find($task->id);
            // Free up a slot so another worker can book.
            if ($locked->booked > 0) {
                $locked->decrement('booked');
            }
        });

        return back()->with('success', 'Proof rejected. The slot has been reopened.');
    }

    /* =========================================================
     * Create task (employer)
     * ========================================================= */
    public function createTask()
    {
        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();
        $s = $this->settings->all();
        $currency = $this->settings->defaultCurrency();

        return view('user.create-task', compact('categories', 's', 'currency'));
    }

    public function storeTask(Request $request, WalletService $wallet, AiService $ai)
    {
        $user = auth('web')->user();
        $s = $this->settings->all();
        $commission = (float) $s->task_com; // percent

        $validated = $request->validate([
            'title'       => 'required|string|max:191',
            'category_id' => 'required|exists:task_categories,id',
            'price'       => 'required|numeric|min:0.01|max:1000',
            'amount'      => 'required|integer|min:1|max:1000',
            'time'        => 'nullable|integer|min:1|max:10080',
            'action_url'  => 'nullable|url|max:500',
            'details'     => 'required|string|max:10000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:8192',
        ]);

        $unitPrice = (float) $validated['price'];
        $quantity = (int) $validated['amount'];
        $totalPrice = round($unitPrice * $quantity * (1 + $commission / 100), 2);

        if ((float) $user->balance < $totalPrice) {
            return back()->with('error', 'Insufficient balance. You need '.money($totalPrice).' but have '.money((float)$user->balance).'. Please deposit funds first.')->withInput();
        }

        $code = strtoupper(Str::random(10));

        $taskImage = null;
        if (!$request->hasFile('image') && !$user->isAdvertiser()) {
            return back()->with('error', 'Every task must include an image. Upload a task image before continuing.')->withInput();
        }
        if ($request->hasFile('image')) {
            $taskImage = $request->file('image')->store('tasks', 'public');
        } elseif ($user->isAdvertiser()) {
            try {
                // Advertisers without artwork get an AI-generated task image. Cost: configured AI image price (default $0.20).
                $aiUrl = $ai->image($user, 'Create a clean professional micro-job marketplace image for this task. Title: '.$validated['title'].'. Instructions context: '.$validated['details'].' Use no trademarks, logos, or brand names.');
                $relative = str_replace('/storage/', '', parse_url($aiUrl, PHP_URL_PATH) ?: '');
                if ($relative) $taskImage = ltrim($relative, '/');
            } catch (\Throwable $e) {
                return back()->with('error', 'This task needs an image. Upload one or add AI credits so the system can generate the required image for '.money((float) \App\Models\SiteSetting::get('ai_image_price', 0.20)).'.')->withInput();
            }
        }

        $debit = $wallet->debit($user, $totalPrice, 'task_creation', [
            'reference'   => 'TASKCREATE-'.$code,
            'description' => 'Created task "'.$validated['title'].'" (qty '.$quantity.')',
        ]);

        if (! $debit) {
            return back()->with('error', 'Insufficient balance to create this task.')->withInput();
        }

        Task::create([
            'code'        => $code,
            'title'       => $validated['title'],
            'price'       => $unitPrice,
            'action_url'  => $validated['action_url'] ?? null,
            'details'     => $validated['details'],
            'image'       => $taskImage,
            'category_id' => $validated['category_id'],
            'user_id'     => $user->id,
            'date'        => now()->toDateString(),
            'amount'      => $quantity,
            'time'        => $validated['time'] ?? null,
            'total_price' => $totalPrice,
            'status'      => 0, // pending admin approval
        ]);

        return redirect()->route('user.offers')->with('success', 'Task created and submitted for admin approval. '.money($totalPrice).' has been reserved from your wallet.');
    }

    /* =========================================================
     * Wallet & deposits
     * ========================================================= */
    public function wallet()
    {
        $user = auth('web')->user();
        $deposits = $user->deposits()->with('method')->latest()->paginate(10);
        $methods = DepositMethod::where('active', true)->orderBy('position')->get();
        $currency = $this->settings->defaultCurrency();

        return view('user.wallet', compact('deposits', 'methods', 'currency'));
    }

    public function initiateDeposit(Request $request, WalletService $wallet)
    {
        $user = auth('web')->user();
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'method_id' => 'required|exists:deposit_methods,id',
        ]);
        $amountUsd = (float) $validated['amount'];
        $method = DepositMethod::where('id', $validated['method_id'])->where('active', true)->first();
        if (!$method) return back()->with('error', 'This deposit method is not available.');
        if ((float)$method->min_amount > $amountUsd) return back()->with('error', 'Minimum deposit for '.$method->name.' is '.money((float)$method->min_amount).'.');
        if ($method->manual) return back()->with('error', 'This deposit method requires manual confirmation. Please use the instructions shown or contact support.');

        $currency = $this->settings->defaultCurrency();
        $amountLocal = round($currency->fromUsd($amountUsd), 2);
        $slug = strtolower((string)$method->slug);

        if (in_array($slug, ['paystack','paystack-card'], true)) {
            if (!$this->paystack->configured()) return back()->with('error', 'Paystack is not configured. Please contact the administrator.');
            $paystackCurrency = \App\Models\Currency::where('paystack_supported', true)->where('active', true)->orderBy('is_default','desc')->first();
            if (!$paystackCurrency) return back()->with('error', 'No Paystack-supported currency is configured.');
            $amountLocal = round($paystackCurrency->fromUsd($amountUsd), 2);
            $reference = $this->paystack->generateReference('DEP');
            $deposit = Deposit::create([
                'user_id'=>$user->id,'method_id'=>$method->id,'amount'=>$amountUsd,'amount_paid'=>$amountLocal,
                'type'=>'wallet','reference'=>$reference,'manual'=>false,'status'=>0,'date'=>now()->toDateString(),
            ]);
            $payload=$this->paystack->initialise([
                'email'=>$user->email,'amount'=>(int)round($amountLocal*100),'currency'=>$paystackCurrency->code,'reference'=>$reference,
                'callback_url'=>route('user.wallet.verify'),'metadata'=>['deposit_id'=>$deposit->id,'user_id'=>$user->id,'type'=>'wallet','amount_usd'=>$amountUsd],
            ]);
            if (!$payload || empty($payload['authorization_url'])) { $deposit->delete(); return back()->with('error','Unable to start the Paystack payment. Please try again.'); }
            return redirect()->away($payload['authorization_url']);
        }

        if (in_array($slug, ['flutterwave','flutterwave-card'], true)) {
            if (!$this->flutterwave->configured()) return back()->with('error','Flutterwave is not configured. Please contact the administrator.');
            $reference = 'FWV_'.strtoupper(Str::random(18)).'_'.time();
            $deposit = Deposit::create([
                'user_id'=>$user->id,'method_id'=>$method->id,'amount'=>$amountUsd,'amount_paid'=>$amountLocal,
                'type'=>'wallet','reference'=>$reference,'manual'=>false,'status'=>0,'date'=>now()->toDateString(),
            ]);
            $payload=$this->flutterwave->initialise([
                'tx_ref'=>$reference,'amount'=>$amountLocal,'currency'=>$currency->code,'redirect_url'=>route('user.wallet.verify'),
                'customer'=>['email'=>$user->email,'name'=>$user->name],
                'customizations'=>['title'=>'Airtrendmedia Wallet Deposit'],
                'meta'=>['deposit_id'=>$deposit->id,'user_id'=>$user->id,'amount_usd'=>$amountUsd],
            ]);
            if (!$payload || empty($payload['link'])) { $deposit->delete(); return back()->with('error','Unable to start the Flutterwave payment. Please try again.'); }
            return redirect()->away($payload['link']);
        }

        return back()->with('error','This online payment method is not yet configured. Please contact the administrator.');
    }

    public function verifyDeposit(Request $request, WalletService $wallet)
    {
        $reference = (string)($request->query('reference') ?: $request->query('tx_ref'));
        $transactionId = (string)$request->query('transaction_id');
        if (!$reference && !$transactionId) return redirect()->route('user.wallet')->with('error','No payment reference was returned.');
        $deposit = $reference ? Deposit::where('reference',$reference)->where('type','wallet')->first() : null;
        if (!$deposit) return redirect()->route('user.wallet')->with('error','Deposit record not found.');
        if ($deposit->isPaid()) return redirect()->route('user.wallet')->with('info','This deposit has already been credited.');

        $methodSlug = strtolower((string)optional($deposit->method)->slug);
        $data = null;
        $paidAmount = null;
        if (str_contains($methodSlug,'flutterwave')) {
            $data = $this->flutterwave->verify($transactionId);
            if (!$data && $reference) {
                return redirect()->route('user.wallet')->with('error','Flutterwave payment could not be verified. If you have paid, contact support with reference '.$reference.'.');
            }
            if ($data) $paidAmount = (float)($data['amount'] ?? 0);
            if (!$data || ($data['tx_ref'] ?? '') !== $deposit->reference) return redirect()->route('user.wallet')->with('error','Payment verification failed: transaction reference mismatch.');
            if ($paidAmount <= 0 || abs($paidAmount - (float)$deposit->amount_paid) > 0.02) return redirect()->route('user.wallet')->with('error','Payment verification failed: paid amount does not match the pending deposit.');
        } else {
            $reference = $deposit->reference;
            $data = $this->paystack->verify($reference);
            if (!$data) return redirect()->route('user.wallet')->with('error','Payment could not be verified. If you have paid, contact support with reference '.$reference.'.');
            $paidAmount = (float)($data['amount'] ?? 0) / 100;
            if ($paidAmount <= 0 || abs($paidAmount - (float)$deposit->amount_paid) > 0.02) return redirect()->route('user.wallet')->with('error','Payment verification failed: paid amount does not match the pending deposit.');
        }

        DB::transaction(function () use ($deposit,$wallet,$paidAmount) {
            $locked = Deposit::whereKey($deposit->id)->lockForUpdate()->first();
            if (!$locked || $locked->status === 1) return;
            $locked->update(['status'=>1,'amount_paid'=>$paidAmount ?: $locked->amount_paid]);
            $wallet->credit($locked->user, (float)$locked->amount, 'deposit', [
                'reference'=>$locked->reference,'description'=>'Wallet deposit ($'.number_format((float)$locked->amount,2).')',
                'related_id'=>$locked->id,'related_type'=>Deposit::class,
            ]);
        });
        return redirect()->route('user.wallet')->with('success','Deposit successful! '.money((float)$deposit->amount).' has been added to your wallet.');
    }

    /* =========================================================
     * Withdrawals
     * ========================================================= */
    public function withdraw()
    {
        $user = auth('web')->user();
        $methods = WithdrawalMethod::where('active', true)->whereNull('parent_id')->orderBy('position')->get();
        $withdrawals = $user->withdrawals()->with('method')->latest()->paginate(10);
        $currency = $this->settings->defaultCurrency();
        $s = $this->settings->all();

        return view('user.withdraw', compact('methods', 'withdrawals', 'currency', 's'));
    }

    public function requestWithdraw(Request $request, WalletService $wallet)
    {
        $user = auth('web')->user();
        $s = $this->settings->all();
        $commission = (float) $s->withdraw_com; // percent

        $validated = $request->validate([
            'method_id' => 'required|exists:withdrawal_methods,id',
            'amount'    => 'required|numeric|min:1',
            'details'   => 'required|string|max:2000',
        ]);

        $amount = (float) $validated['amount'];
        if ($amount > (float) $user->balance) {
            return back()->with('error', 'Insufficient balance. Your available balance is '.money((float)$user->balance).'.')->withInput();
        }

        $method = WithdrawalMethod::find($validated['method_id']);
        if (! $method->active) {
            return back()->with('error', 'This withdrawal method is not available.')->withInput();
        }
        if ($method->min_amount > 0 && $amount < (float) $method->min_amount) {
            return back()->with('error', 'Minimum withdrawal for '.$method->name.' is '.money((float)$method->min_amount).'.')->withInput();
        }

        $fee = round($amount * ($commission / 100), 2);
        $paid = round($amount - $fee, 2);

        $debit = $wallet->debit($user, $amount, 'withdrawal', [
            'reference'   => $this->paystack->generateReference('WD'),
            'description' => 'Withdrawal request via '.$method->name,
        ]);

        if (! $debit) {
            return back()->with('error', 'Insufficient balance.')->withInput();
        }

        Withdrawal::create([
            'user_id'   => $user->id,
            'method_id' => $method->id,
            'amount'    => $amount,
            'fee'       => $fee,
            'paid'      => $paid,
            'details'   => $validated['details'],
            'status'    => 0,
            'date'      => now()->toDateString(),
        ]);

        return redirect()->route('user.withdraw')->with('success', 'Withdrawal request submitted. You will receive '.money($paid).' (after '.money($fee).' fee) once approved.');
    }

    public function supportUnreadCount()
    {
        $user = auth('web')->user();
        $count = $user ? Message::where('user_id',$user->id)->where('from_admin',true)->where('seen',false)->count() : 0;
        return response()->json(['count'=>$count]);
    }

    /* =========================================================
     * Transactions
     * ========================================================= */
    public function transactions(Request $request)
    {
        $user = auth('web')->user();
        $query = $user->transactions()->with('user:id,username');

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        $transactions = $query->latest()->paginate(20)->appends($request->query());

        return view('user.transactions', compact('transactions'));
    }

    /* =========================================================
     * Affiliate dashboard
     * ========================================================= */
    public function affiliate()
    {
        $user = auth('web')->user();
        $s = $this->settings->all();
        $reward = $this->settings->affiliateReward();
        $currency = $this->settings->defaultCurrency();

        $referrals = $user->affiliateReferrals()->with('referee:id,username,name,is_active,activated_at')->latest()->paginate(15);

        $total = $user->affiliateReferrals()->count();
        $paidCount = $user->affiliateReferrals()->where('status', 'paid')->count();
        $pendingCount = $user->affiliateReferrals()->where('status', 'pending')->count();
        $totalEarnings = $user->affiliateEarnings();

        $monthly = $user->affiliateReferrals()
            ->where('status', 'paid')
            ->whereNotNull('rewarded_at')
            ->selectRaw(config('database.default') === 'sqlite'
                ? "strftime('%Y-%m', rewarded_at) as month, COUNT(*) as count, SUM(reward_amount) as total"
                : "DATE_FORMAT(rewarded_at, '%Y-%m') as month, COUNT(*) as count, SUM(reward_amount) as total")
            ->groupBy('month')
            ->orderByDesc('month')
            ->limit(12)
            ->get();

        return view('user.affiliate', compact(
            'user', 's', 'reward', 'currency', 'referrals',
            'total', 'paidCount', 'pendingCount', 'totalEarnings', 'monthly'
        ));
    }

    /**
     * Public profile used by Messenger, task authors and other marketplace areas.
     */
    public function publicProfile(\App\Models\User $user)
    {
        if ($user->banned) {
            abort(404);
        }

        $user->load('country');
        $reviews = $user->approvedReviews()->with('user:id,username,name,image')->paginate(10);

        return view('user.public-profile', compact('user', 'reviews'));
    }

    /* =========================================================
     * Profile
     * ========================================================= */
    public function profile()
    {
        $user = auth('web')->user()->load('country');
        $countries = Country::orderBy('name')->get();
        $reviews = $user->approvedReviews()->with('user:id,username,name,image')->paginate(10);
        return view('user.profile', compact('user', 'countries', 'reviews'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth('web')->user();

        $validated = $request->validate([
            'name'         => 'required|string|max:120',
            'phone'        => 'nullable|string|max:30',
            'country_code' => 'nullable|string|max:4',
            'bio'          => 'nullable|string|max:2000',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateImage(Request $request)
    {
        $user = auth('web')->user();
        $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048']);

        $path = app(MediaUploadService::class)->storeAvatar($request->file('image'), $user->id);

        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        $user->update(['image' => $path]);

        return back()->with('success', 'Profile picture updated.');
    }

    /* =========================================================
     * Messages (support chat with admin)
     * ========================================================= */
    /* =========================================================
     * Notifications
     * ========================================================= */
    public function notifications(Request $request)
    {
        $user = auth('web')->user();
        $notifications = $user->notifications()->latest()->paginate(15);

        return view('user.notifications', compact('notifications'));
    }

    public function markNotificationRead(Request $request, $notification)
    {
        $user = auth('web')->user();
        $notif = Notification::where('user_id', $user->id)->where('id', $notification)->first();

        if ($notif && !$notif->is_read) {
            $notif->update(['is_read' => true]);
        }

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $user = auth('web')->user();
        Notification::where('user_id', $user->id)->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function unreadNotificationCount(Request $request)
    {
        $user = auth('web')->user();
        $count = $user->notifications()->where('is_read', false)->count();

        return response()->json(['count' => $count]);
    }

    public function messages()
    {
        $user = auth('web')->user();
        $messages = $user->messages()->orderBy('created_at')->get();

        // mark admin replies as seen when user opens the thread
        $user->messages()->where('from_admin', true)->where('seen', false)->update(['seen' => true]);

        return view('user.messages', compact('messages'));
    }

    public function sendMessage(Request $request)
    {
        $user = auth('web')->user();
        $validated = $request->validate(['message' => 'required|string|max:3000','attachments'=>'nullable|array|max:5','attachments.*'=>'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,zip']);
        $attachments=[];
        foreach(($request->file('attachments') ?? []) as $file){ $attachments[]= ['path'=>$file->store('support','public'),'name'=>$file->getClientOriginalName(),'mime'=>$file->getMimeType(),'size'=>$file->getSize()]; }

        Message::create([
            'user_id'    => $user->id,
            'message'    => $validated['message'],
            'attachments' => $attachments,
            'from_admin' => false,
            'seen'       => false,
        ]);

        return back()->with('success', 'Message sent to support.');
    }

    /* =========================================================
     * Helpers
     * ========================================================= */

}
