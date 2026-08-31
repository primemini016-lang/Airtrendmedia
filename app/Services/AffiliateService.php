<?php

namespace App\Services;

use App\Models\AffiliateReferral;
use App\Models\AppSetting;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Affiliate program service.
 *
 * Reward rule: when a referred user pays the $5 account activation fee,
 * the referrer earns $1.50 (configurable in admin settings). The reward is
 * credited to the referrer's wallet and recorded as an affiliate_reward
 * transaction + an affiliate_referrals row marked "paid".
 *
 * Fraud guards:
 * - Only one reward per referee (unique referee_id on affiliate_referrals).
 * - Referrer cannot be the same as referee.
 * - Activation deposit must be verified/paid.
 * - Affiliate program must be enabled in settings.
 * - Reward processed atomically inside a DB transaction with row locking.
 */
class AffiliateService
{
    public function __construct(private WalletService $wallet) {}

    /**
     * Create (or fetch) a pending referral record when a user registers with a referrer.
     */
    public function registerReferral(User $referee): ?AffiliateReferral
    {
        if (! $referee->referrer_id || $referee->referrer_id === $referee->id) {
            return null;
        }

        return AffiliateReferral::firstOrCreate(
            ['referee_id' => $referee->id],
            [
                'referrer_id' => $referee->referrer_id,
                'status'      => 'pending',
                'attribution_source' => 'referral_link',
                'attribution_ip' => $referee->registration_ip,
                'attributed_at' => now(),
            ]
        );
    }

    /**
     * Process the affiliate reward once a referee's activation deposit is confirmed.
     */
    public function processActivationReward(Deposit $activationDeposit): ?Transaction
    {
        if (! $activationDeposit->isActivation() || ! $activationDeposit->isPaid()) {
            return null;
        }

        $settings = AppSetting::find(1);
        if (! $settings || ! $settings->affiliate_enabled) {
            return null;
        }

        return DB::transaction(function () use ($activationDeposit, $settings) {
            $referral = AffiliateReferral::where('referee_id', $activationDeposit->user_id)
                ->lockForUpdate()
                ->first();

            if (! $referral || $referral->status === 'paid') {
                return null; // already rewarded or no referral
            }

            $referrer = User::lockForUpdate()->find($referral->referrer_id);
            if (! $referrer) {
                return null;
            }

            $reward = (float) $settings->affiliate_reward;

            $txn = $this->wallet->credit($referrer, $reward, 'affiliate_reward', [
                'reference'    => 'AFF-'.$activationDeposit->reference,
                'description'  => 'Affiliate reward for referral '.$referral->referee->username,
                'related_id'   => $referral->id,
                'related_type' => AffiliateReferral::class,
            ]);

            $referral->update([
                'status'                 => 'paid',
                'reward_amount'          => $reward,
                'activation_deposit_id'  => $activationDeposit->id,
                'reward_transaction_id'  => $txn->id,
                'rewarded_at'            => now(),
            ]);

            Log::info('Affiliate reward processed', [
                'referrer' => $referrer->id,
                'referee'  => $activationDeposit->user_id,
                'reward'   => $reward,
            ]);

            return $txn;
        });
    }
}
