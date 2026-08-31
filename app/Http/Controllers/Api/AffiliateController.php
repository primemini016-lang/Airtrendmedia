<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AffiliateReferral;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Affiliate program endpoints: real-time statistics, referral link,
 * and the full referral history with reward status.
 */
class AffiliateController extends Controller
{
    use ApiResponse;

    /**
     * Affiliate dashboard statistics for the authenticated user.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user('user');
        $settings = app(SettingService::class)->all();

        $totalReferrals = User::where('referrer_id', $user->id)->count();
        $paidReferrals  = AffiliateReferral::where('referrer_id', $user->id)->where('status', 'paid')->count();
        $pendingReferrals = AffiliateReferral::where('referrer_id', $user->id)->where('status', 'pending')->count();
        $totalEarnings  = (float) AffiliateReferral::where('referrer_id', $user->id)->where('status', 'paid')->sum('reward_amount');

        // This-month breakdown for the chart.
        $monthly = AffiliateReferral::selectRaw(config('database.default') === 'sqlite'
                ? "strftime('%Y-%m', created_at) as month, COUNT(*) as referrals, COALESCE(SUM(CASE WHEN status='paid' THEN reward_amount ELSE 0 END),0) as earnings"
                : "DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as referrals, COALESCE(SUM(CASE WHEN status='paid' THEN reward_amount ELSE 0 END),0) as earnings")
            ->where('referrer_id', $user->id)
            ->where('created_at', '>=', now()->startOfYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $this->ok('Affiliate stats retrieved.', [
            'referral_code'      => $user->referral_code,
            'referral_link'      => route('register', ['ref' => $user->referral_code]),
            'reward_per_referral'=> (float) $settings->affiliate_reward,
            'affiliate_enabled'  => (bool) $settings->affiliate_enabled,
            'total_referrals'    => $totalReferrals,
            'paid_referrals'     => $paidReferrals,
            'pending_referrals'  => $pendingReferrals,
            'total_earnings'     => $totalEarnings,
            'monthly'            => $monthly,
        ]);
    }

    /**
     * List referrals (paginated) with status.
     */
    public function referrals(Request $request): JsonResponse
    {
        $user = $request->user('user');

        $referrals = AffiliateReferral::with(['referee:id,username,name,email,activated_at,created_at'])
            ->where('referrer_id', $user->id)
            ->latest()
            ->paginate(20);

        return $this->ok('Referrals retrieved.', $referrals);
    }

    /**
     * Top-level summary card (reused by dashboard widgets).
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user('user');
        $settings = app(SettingService::class)->all();

        return $this->ok('Affiliate summary retrieved.', [
            'referral_code'    => $user->referral_code,
            'referral_link'    => route('register', ['ref' => $user->referral_code]),
            'paid_referrals'   => $user->creditedReferralsCount(),
            'total_earnings'   => (float) $user->affiliateEarnings(),
            'reward_amount'    => (float) $settings->affiliate_reward,
            'enabled'          => (bool) $settings->affiliate_enabled,
        ]);
    }
}
