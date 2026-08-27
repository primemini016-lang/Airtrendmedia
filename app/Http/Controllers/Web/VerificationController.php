<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\VerificationBadge;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $autoApprove = (bool) $this->settings->get('verification_auto_approval', false);
        $enabled = (bool) $this->settings->get('verification_enabled', true);
        $userBalance = (float) $user->balance;

        $isBlue = $user->isBlueVerified();
        $daysLeft = 0;
        if ($badge && $badge->valid_until) {
            $daysLeft = max(0, now()->startOfDay()->diffInDays($badge->valid_until, false));
        }

        return view('user.verification', compact(
            'badge', 'monthlyFee', 'autoApprove', 'enabled',
            'userBalance', 'isBlue', 'daysLeft'
        ));
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
            'full_name'        => ['required', 'string', 'max:120'],
            'government_id'    => ['required', 'image', 'max:5120'],
            'verification_code'=> ['nullable', 'string', 'max:80'],
            'category'         => ['required', 'string', 'in:individual,business,organization,public_figure'],
        ]);

        $monthlyFee = (float) $this->settings->get('verification_monthly_fee', 5.00);

        if ((float) $user->balance < $monthlyFee) {
            return back()->with('error', "Insufficient balance. Verification requires \${$monthlyFee}. Please fund your wallet first.");
        }

        // Deduct fee
        $user->decrement('balance', $monthlyFee);

        // Store ID image
        $idPath = $request->file('government_id')->store('verification', 'public');

        $autoApprove = (bool) $this->settings->get('verification_auto_approval', false);
        $status = $autoApprove ? 'verified' : 'pending';
        $validUntil = $autoApprove ? now()->addDays(30) : null;

        $badge = VerificationBadge::create([
            'user_id'           => $user->id,
            'full_name'         => $validated['full_name'],
            'government_id_path'=> $idPath,
            'verification_code' => $validated['verification_code'] ?? null,
            'category'          => $validated['category'],
            'monthly_fee'       => $monthlyFee,
            'valid_from'        => now(),
            'valid_until'       => $validUntil,
            'status'            => $status,
            'paid_at'           => now(),
        ]);

        if ($autoApprove) {
            $user->update([
                'verification_status'        => 'verified',
                'verification_valid_until'   => $validUntil,
                'is_verified'                => true,
            ]);
            return redirect()->route('user.verification')->with('success', 'Congratulations! Your blue verification badge is now active for 30 days.');
        }

        $user->update(['verification_status' => 'pending']);

        return redirect()->route('user.verification')->with('success', 'Verification application submitted with $' . number_format($monthlyFee, 2) . ' payment. Awaiting admin review.');
    }

    /**
     * Renew an active/expired verification badge (30 more days, $5).
     */
    public function renew(Request $request)
    {
        $user = auth('web')->user();
        $badge = $user->verificationBadge;

        if (!$badge) {
            return back()->with('error', 'No verification record found. Please apply first.');
        }

        $monthlyFee = (float) $this->settings->get('verification_monthly_fee', 5.00);

        if ((float) $user->balance < $monthlyFee) {
            return back()->with('error', "Insufficient balance. Renewal requires \${$monthlyFee}.");
        }

        $user->decrement('balance', $monthlyFee);

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
        $validUntil = now()->addDays(30);
        $badge->update([
            'status'      => 'verified',
            'valid_from'  => now(),
            'valid_until' => $validUntil,
            'reviewed_at' => now(),
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
