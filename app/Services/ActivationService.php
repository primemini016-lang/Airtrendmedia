<?php

namespace App\Services;

use App\Events\UserActivationPaid;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles the $5 account activation flow.
 *
 * Flow:
 * 1. User registers & verifies email -> is_active = false, cannot perform tasks.
 * 2. User initiates activation payment -> a pending 'activation' deposit is created.
 * 3. User pays via Paystack -> on verified callback the deposit is marked paid,
 *    user.is_active = true, and UserActivationPaid event fires (affiliate reward).
 *
 * All state changes are atomic and idempotent (re-verifying the same reference
 * will not double-activate or double-reward).
 */
class ActivationService
{
    public function __construct(
        private PaystackService $paystack,
        private WalletService $wallet,
    ) {}

    /**
     * Create a pending activation deposit and initialise Paystack payment.
     * Returns the Paystack authorization URL.
     */
    public function initiate(User $user, string $callbackUrl): ?array
    {
        if ($user->is_active) {
            return null;
        }

        $settings = app(SettingService::class);
        $defaultCurrency = $settings->defaultCurrency();
        $paystackCurrency = Currency::where('paystack_supported', true)->where('active', true)
            ->orderBy('is_default', 'desc')->first();

        if (! $paystackCurrency) {
            // Fallback: use NGN with a sane default if admin hasn't configured one.
            $paystackCurrency = Currency::firstOrCreate(
                ['code' => 'NGN'],
                ['name' => 'Nigerian Naira', 'symbol' => '₦', 'usd_value' => 1500, 'paystack_supported' => true, 'active' => true]
            );
        }

        $feeUsd = $settings->activationFee();
        $amountInPaystackCurrency = round($defaultCurrency->fromUsd($feeUsd), 2);

        $reference = $this->paystack->generateReference('ACT');

        $deposit = Deposit::create([
            'user_id'    => $user->id,
            'method_id'  => null,
            'amount'     => $feeUsd,
            'amount_paid'=> $amountInPaystackCurrency,
            'type'       => 'activation',
            'reference'  => $reference,
            'manual'     => false,
            'status'     => 0,
            'date'       => now()->toDateString(),
        ]);

        $payload = $this->paystack->initialise([
            'email'         => $user->email,
            'amount'        => (int) round($amountInPaystackCurrency * 100), // minor units
            'currency'      => $paystackCurrency->code,
            'reference'     => $reference,
            'callback_url'  => $callbackUrl,
            'metadata'      => [
                'deposit_id'  => $deposit->id,
                'user_id'     => $user->id,
                'type'        => 'activation',
                'fee_usd'     => $feeUsd,
            ],
        ]);

        return $payload;
    }

    /**
     * Verify and confirm an activation payment by reference.
     */
    public function confirmByReference(string $reference): bool
    {
        $deposit = Deposit::where('reference', $reference)->where('type', 'activation')->first();
        if (! $deposit || $deposit->isPaid()) {
            return $deposit?->isPaid() ?? false;
        }

        $data = $this->paystack->verify($reference);
        if (! $data) {
            return false;
        }

        return $this->applyActivation($deposit, (float) ($data['amount'] / 100));
    }

    /**
     * Mark a deposit as paid, activate the user, fire affiliate reward event.
     */
    public function applyActivation(Deposit $deposit, float $amountPaid): bool
    {
        return DB::transaction(function () use ($deposit, $amountPaid) {
            if ($deposit->isPaid()) {
                return true;
            }

            $deposit->update([
                'status'      => 1,
                'amount_paid' => $amountPaid > 0 ? $amountPaid : $deposit->amount_paid,
            ]);

            $user = User::lockForUpdate()->find($deposit->user_id);
            $user->update([
                'is_active'    => true,
                'activated_at' => now(),
            ]);

            // Log a transaction record (informational — activation fee is not added to wallet).
            app(WalletService::class)->credit($user, 0, 'activation', [
                'reference'    => $deposit->reference,
                'description'  => 'Account activation fee paid ('.$deposit->amount.' USD)',
                'related_id'   => $deposit->id,
                'related_type' => Deposit::class,
                'status'       => 'completed',
            ]);

            event(new UserActivationPaid($deposit));

            Log::info('User activated', ['user_id' => $user->id, 'deposit' => $deposit->id]);
            return true;
        });
    }
}
