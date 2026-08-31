<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VerificationBadge;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;

class VerificationController extends Controller
{
    public function __construct(
        private SettingService $settings,
    ) {}

    /**
     * Show the blue-verification badge page (apply / status / renew).
     */
    public function index()
    {
        $user = auth('web')->user();
        $badge = $user->verificationBadge;
        $monthlyFee = (float) $this->settings->get('verification_monthly_fee', 5.00);
        $trialDays = (int) $this->settings->get('verification_trial_days', 3);
        $autoApprove = (bool) $this->settings->get('verification_auto_approval', false);
        $enabled = (bool) $this->settings->get('verification_enabled', true);
        $userBalance = (float) $user->balance;

        $isBlue = $user->isBlueVerified();
        $daysLeft = 0;
        if ($badge && $badge->valid_until) {
            $daysLeft = max(0, now()->startOfDay()->diffInDays($badge->valid_until, false));
        }

        return view('user.verification', compact(
            'badge', 'monthlyFee', 'trialDays', 'autoApprove', 'enabled',
            'userBalance', 'isBlue', 'daysLeft'
        ));
    }

    public function startTrial(Request $request)
    {
        $user = auth('web')->user();
        $days = max(1, (int)$this->settings->get('verification_trial_days', 3));
        if (!(bool)$this->settings->get('verification_enabled', true)) return back()->with('error','Blue verification is currently disabled.');
        $existing = $user->verificationBadge;
        if ($existing && $existing->trial_started_at) return back()->with('error','Your verification trial has already been used.');
        if ($existing && $existing->status === 'verified' && $existing->valid_until && $existing->valid_until->isFuture()) return back()->with('error','Your badge is already active.');
        $validUntil = now()->addDays($days);

        // Keep the verification row internally complete on fresh installs.
        // user_id is the canonical account owner; polymorphic ownership is optional.
        $badge = \App\Models\VerificationBadge::updateOrCreate(
            ['user_id' => $user->id],
            [
                'verifiable_type' => \App\Models\User::class,
                'verifiable_id' => $user->id,
                'full_name' => $user->name,
                'category' => 'individual',
                'monthly_fee' => (float) $this->settings->get('verification_monthly_fee', 5.00),
                'amount_paid' => 0,
                'status' => 'verified',
                'valid_from' => now(),
                'valid_until' => $validUntil,
                'paid_at' => null,
                'payment_reference' => 'VER-TRIAL-' . str()->upper(str()->random(10)),
                'trial_started_at' => now(),
                'trial_ends_at' => $validUntil,
            ]
        );
        $user->update(['verification_status'=>'verified','verification_valid_until'=>$validUntil,'is_verified'=>true]);
        return redirect()->route('user.verification')->with('success','Your 3-day blue badge trial is active until '.$validUntil->format('M d, Y H:i').'.');
    }

    /**
     * Apply for verification: requires government ID upload + $5 payment from balance.
     * Validity is 30 days from approval.
     */
    public function apply(Request $request)
    {
        $user = auth('web')->user();

        if (!(bool) $this->settings->get('verification_enabled', true)) {
            return back()->with('error', 'Blue verification is currently disabled.');
        }
        if ($user->isBlueVerified()) {
            return back()->with('error', 'You are already verified.');
        }
        if ($user->verification_status === 'pending') {
            return back()->with('error', 'Your verification application is under review.');
        }

        $validated = $request->validate([
            'full_name'         => ['required', 'string', 'max:120'],
            'government_id'     => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'verification_code' => ['nullable', 'string', 'max:80'],
            'category'          => ['required', 'string', 'in:individual,business,organization,public_figure'],
        ]);

        $monthlyFee = (float) $this->settings->get('verification_monthly_fee', 5.00);
        $duration = max(1, (int) $this->settings->get('verification_badge_duration_days', 30));
        $autoApprove = (bool) $this->settings->get('verification_auto_approval', false);

        try {
            [$badge, $autoApproved] = DB::transaction(function () use ($user, $request, $validated, $monthlyFee, $duration, $autoApprove) {
                $locked = \App\Models\User::lockForUpdate()->findOrFail($user->id);
                if ((float) $locked->balance < $monthlyFee) {
                    throw new \RuntimeException('Insufficient balance. Verification requires $'.number_format($monthlyFee, 2).'.');
                }

                $idPath = $request->file('government_id')->store('verification', 'public');
                $reference = 'VER-' . strtoupper(bin2hex(random_bytes(8)));
                $status = $autoApprove ? 'verified' : 'pending';
                $validUntil = $autoApprove ? now()->addDays($duration) : null;

                $locked->balance = (float)$locked->balance - $monthlyFee;
                $locked->save();

                $badge = VerificationBadge::create([
                    'user_id' => $locked->id,
                    'verifiable_type' => \App\Models\User::class,
                    'verifiable_id' => $locked->id,
                    'full_name' => $validated['full_name'],
                    'government_id_path' => $idPath,
                    'verification_code' => $validated['verification_code'] ?? null,
                    'category' => $validated['category'],
                    'monthly_fee' => $monthlyFee,
                    'amount_paid' => $monthlyFee,
                    'valid_from' => $autoApprove ? now() : null,
                    'valid_until' => $validUntil,
                    'status' => $status,
                    'paid_at' => now(),
                    'payment_reference' => $reference,
                ]);

                $locked->update([
                    'verification_status' => $status,
                    'verification_valid_until' => $validUntil,
                    'is_verified' => $autoApprove,
                ]);

                \App\Models\Transaction::create([
                    'user_id' => $locked->id,
                    'type' => 'verification_badge',
                    'amount' => -$monthlyFee,
                    'balance_after' => $locked->balance,
                    'currency' => 'USD',
                    'reference' => $reference,
                    'description' => 'Blue verification badge payment',
                    'related_id' => $badge->id,
                    'related_type' => VerificationBadge::class,
                    'status' => 'completed',
                ]);

                return [$badge, $autoApprove];
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', $e instanceof \RuntimeException ? $e->getMessage() : 'We could not submit the verification request. Your wallet was not charged.')->withInput();
        }

        if ($autoApproved) {
            return redirect()->route('user.verification')->with('success', 'Congratulations! Your blue verification badge is active for '.$badge->daysLeft().' days.');
        }

        return redirect()->route('user.verification')->with('success', 'Verification request sent successfully. Your $'.number_format($monthlyFee, 2).' payment is recorded and your application is now in the admin review queue.');
    }

    /**
     * Renew an active/expired verification badge (30 more days, $5).
     */
    public function renew(Request $request, \App\Services\WalletService $wallet)
    {
        $user = auth('web')->user();
        $badge = $user->verificationBadge;

        if (!$badge) {
            return back()->with('error', 'No verification record found. Please apply first.');
        }

        $monthlyFee = (float) $this->settings->get('verification_monthly_fee', 5.00);

        $debit = $wallet->debit($user, $monthlyFee, 'verification_badge', [
            'reference' => 'VER-RENEW-'.str()->upper(str()->random(12)),
            'description' => 'Blue verification badge renewal',
        ]);
        if (!$debit) {
            return back()->with('error', 'Insufficient balance. Renewal requires $'.number_format($monthlyFee, 2).'.');
        }

        $base = ($badge->valid_until && $badge->valid_until->isFuture()) ? $badge->valid_until : now();
        $newValidUntil = $base->copy()->addDays(30);

        $badge->update([
            'valid_until' => $newValidUntil,
            'valid_from'  => now(),
            'status'      => 'verified',
            'paid_at'     => now(),
        ]);

        $user->update([
            'verification_status'      => 'verified',
            'verification_valid_until' => $newValidUntil,
            'is_verified'              => true,
        ]);

        return back()->with('success', 'Verification renewed for 30 more days. Thank you!');
    }

    /**
     * Admin: list all verification badges.
     */
    public function adminIndex(Request $request)
    {
        $query = VerificationBadge::with('user:id,username,name,email,image');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $badges = $query->latest()->paginate(20);
        $autoApprove = (bool) $this->settings->get('verification_auto_approval', false);
        $monthlyFee = (float) $this->settings->get('verification_monthly_fee', 5.00);

        return view('admin.verification', compact('badges', 'autoApprove', 'monthlyFee'));
    }

    /**
     * Admin: view a single verification request with the government ID.
     */
    public function adminShow(VerificationBadge $badge)
    {
        $badge->load('user');
        return view('admin.verification-show', compact('badge'));
    }

    /**
     * Admin: approve a verification (sets valid 30 days from now).
     */
    public function adminApprove(Request $request, VerificationBadge $badge)
    {
        $validUntil = now()->addDays((int)$this->settings->get('verification_badge_duration_days',30));
        $badge->update([
            'status'      => 'verified',
            'valid_from'  => now(),
            'valid_until' => $validUntil,
            'reviewed_at' => now(), 'reviewed_by' => auth('admin')->id(),
        ]);
        $badge->user->update([
            'verification_status'      => 'verified',
            'verification_valid_until' => $validUntil,
            'is_verified'              => true,
        ]);

        return back()->with('success', 'Verification approved — blue badge active for 30 days.');
    }

    /**
     * Admin: reject a verification request.
     */
    public function adminReject(Request $request, VerificationBadge $badge)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $badge->update([
            'status'            => 'rejected',
            'rejection_reason'  => $validated['rejection_reason'],
            'reviewed_at'       => now(),
            'reviewed_by'       => auth('admin')->id(),
        ]);
        $badge->user->update([
            'verification_status' => 'rejected',
            'is_verified'         => false,
        ]);

        return back()->with('success', 'Verification request rejected.');
    }

    /**
     * Admin: revoke an active badge (admin god-mode).
     */
    public function adminRevoke(VerificationBadge $badge)
    {
        $badge->update([
            'status'      => 'revoked',
            'valid_until' => now(),
            'reviewed_at' => now(),
        ]);
        $badge->user->update([
            'verification_status' => 'expired',
            'is_verified'         => false,
        ]);

        return back()->with('success', 'Blue verification badge revoked.');
    }

    /**
     * Admin: toggle auto-approval and set monthly fee.
     */
    public function adminSettings(Request $request)
    {
        $validated = $request->validate([
            'verification_auto_approval' => ['nullable', 'boolean'],
            'verification_monthly_fee'   => ['required', 'numeric', 'min:0'],
            'verification_enabled'       => ['nullable', 'boolean'],
        ]);

        $s = $this->settings->all();
        $s->update([
            'verification_auto_approval' => $request->boolean('verification_auto_approval'),
            'verification_monthly_fee'   => $validated['verification_monthly_fee'],
            'verification_enabled'       => $request->boolean('verification_enabled'),
        ]);
        $this->settings->flush();

        return back()->with('success', 'Verification settings updated.');
    }
}
