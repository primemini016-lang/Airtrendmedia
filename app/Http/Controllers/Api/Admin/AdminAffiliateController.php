<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AffiliateReferral;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin affiliate program oversight: global stats, referral list, and
 * the ability to revoke a fraudulently-awarded reward.
 */
class AdminAffiliateController extends Controller
{
    use ApiResponse;

    public function stats(): JsonResponse
    {
        $totalReferrals = AffiliateReferral::count();
        $paidReferrals  = AffiliateReferral::where('status', 'paid')->count();
        $pendingReferrals = AffiliateReferral::where('status', 'pending')->count();
        $totalPaid      = (float) AffiliateReferral::where('status', 'paid')->sum('reward_amount');
        $topReferrers   = User::withCount(['affiliateReferrals as paid' => fn ($q) => $q->where('status', 'paid')])
            ->having('paid', '>', 0)
            ->orderByDesc('paid')
            ->limit(10)
            ->get(['id', 'username', 'name', 'email']);

        return $this->ok('Affiliate stats retrieved.', [
            'total_referrals'   => $totalReferrals,
            'paid_referrals'    => $paidReferrals,
            'pending_referrals' => $pendingReferrals,
            'total_paid_out'    => $totalPaid,
            'top_referrers'     => $topReferrers,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = AffiliateReferral::with(['referrer:id,username,name,email', 'referee:id,username,name,email']);
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        return $this->ok('Referrals retrieved.', $query->latest()->paginate(25));
    }

    /**
     * Revoke a reward (mark as revoked with a reason). Does NOT auto-recover funds;
     * admin should adjust the referrer's balance separately if needed.
     */
    public function revoke(Request $request, AffiliateReferral $referral): JsonResponse
    {
        if ($referral->status !== 'paid') {
            return $this->error('Only paid referrals can be revoked.', 400);
        }

        $validated = $request->validate([
            'revoke_reason' => 'required|string|max:500',
        ]);

        $referral->update([
            'status'        => 'revoked',
            'revoke_reason' => $validated['revoke_reason'],
        ]);

        return $this->ok('Affiliate reward revoked.', $referral->fresh());
    }
}
