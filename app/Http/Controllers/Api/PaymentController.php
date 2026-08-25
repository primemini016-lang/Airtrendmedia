<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\DepositMethod;
use App\Services\ActivationService;
use App\Services\PaystackService;
use App\Services\SettingService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Handles all Paystack payment flows:
 *  - Account activation fee ($5)
 *  - Wallet top-up deposits
 *  - Server-side payment verification (callback + manual verify)
 */
class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private ActivationService $activation,
        private PaystackService $paystack,
        private WalletService $wallet,
    ) {}

    /**
     * Initialise the $5 account activation payment.
     */
    public function initiateActivation(Request $request): JsonResponse
    {
        $user = auth('user')->user();

        if ($user->is_active) {
            return $this->error('Your account is already activated.', 409);
        }

        if (! $this->paystack->configured()) {
            return $this->error('Paystack is not configured. Please contact the administrator.', 503);
        }

        $callbackUrl = $request->input('callback_url', route('payment.callback'));

        $payload = $this->activation->initiate($user, $callbackUrl);

        if (! $payload || empty($payload['authorization_url'])) {
            return $this->error('Unable to initialise payment with Paystack. Please try again.', 502);
        }

        return $this->ok('Activation payment initialised.', [
            'authorization_url' => $payload['authorization_url'],
            'access_code'       => $payload['access_code'] ?? null,
            'reference'         => $payload['reference'] ?? null,
        ]);
    }

    /**
     * Verify an activation payment by reference (called after redirect).
     */
    public function verifyActivation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:80',
        ]);

        $ok = $this->activation->confirmByReference($validated['reference']);

        if (! $ok) {
            return $this->error('Payment could not be verified. If you have paid, please wait a moment and try again or contact support.', 402);
        }

        $user = auth('user')->user()->fresh();

        return $this->ok('Account activated successfully! You can now perform tasks and earn.', [
            'is_active' => $user->is_active,
        ]);
    }

    /**
     * Initialise a wallet top-up deposit via Paystack.
     */
    public function initiateDeposit(Request $request): JsonResponse
    {
        $user = auth('user')->user();

        $validated = $request->validate([
            'amount'        => 'required|numeric|min:1',
            'method_id'     => 'nullable|exists:deposit_methods,id',
            'callback_url'  => 'nullable|url',
        ]);

        if (! $this->paystack->configured()) {
            return $this->error('Paystack is not configured. Please contact the administrator.', 503);
        }

        $settings = app(SettingService::class);
        $defaultCurrency = $settings->defaultCurrency();

        // Choose a paystack-supported currency for charging.
        $paystackCurrency = Currency::where('paystack_supported', true)
            ->where('active', true)
            ->orderBy('is_default', 'desc')
            ->first();

        if (! $paystackCurrency) {
            return $this->error('No Paystack-supported currency is configured. Please contact the administrator.', 503);
        }

        $amountUsd = (float) $validated['amount'];
        $amountInPaystackCurrency = round($defaultCurrency->fromUsd($amountUsd), 2);
        $reference = $this->paystack->generateReference('DEP');

        $method = null;
        if (! empty($validated['method_id'])) {
            $method = DepositMethod::find($validated['method_id']);
        }
        // Default to the paystack method if present.
        if (! $method) {
            $method = DepositMethod::where('slug', 'paystack')->first();
        }

        $deposit = Deposit::create([
            'user_id'    => $user->id,
            'method_id'  => $method?->id,
            'amount'     => $amountUsd,
            'amount_paid'=> $amountInPaystackCurrency,
            'type'       => 'wallet',
            'reference'  => $reference,
            'manual'     => false,
            'status'     => 0,
            'date'       => now()->toDateString(),
        ]);

        $payload = $this->paystack->initialise([
            'email'        => $user->email,
            'amount'       => (int) round($amountInPaystackCurrency * 100),
            'currency'     => $paystackCurrency->code,
            'reference'    => $reference,
            'callback_url' => $validated['callback_url'] ?? route('payment.callback'),
            'metadata'     => [
                'deposit_id' => $deposit->id,
                'user_id'    => $user->id,
                'type'       => 'wallet',
                'amount_usd' => $amountUsd,
            ],
        ]);

        if (! $payload || empty($payload['authorization_url'])) {
            return $this->error('Unable to initialise deposit payment with Paystack.', 502);
        }

        return $this->ok('Deposit payment initialised.', [
            'authorization_url' => $payload['authorization_url'],
            'access_code'       => $payload['access_code'] ?? null,
            'reference'         => $reference,
            'deposit_id'        => $deposit->id,
        ]);
    }

    /**
     * Verify a wallet deposit payment by reference and credit the wallet.
     */
    public function verifyDeposit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:80',
        ]);

        $deposit = Deposit::where('reference', $validated['reference'])
            ->where('type', 'wallet')
            ->where('user_id', auth('user')->id())
            ->first();

        if (! $deposit) {
            return $this->error('Deposit not found.', 404);
        }

        if ($deposit->isPaid()) {
            return $this->ok('Deposit already confirmed.', [
                'amount'  => (float) $deposit->amount,
                'balance' => (float) auth('user')->user()->balance,
            ]);
        }

        $data = $this->paystack->verify($validated['reference']);
        if (! $data) {
            return $this->error('Payment could not be verified.', 402);
        }

        $this->applyWalletDeposit($deposit);

        return $this->ok('Deposit confirmed. Your wallet has been credited.', [
            'amount'  => (float) $deposit->amount,
            'balance' => (float) auth('user')->user()->fresh()->balance,
        ]);
    }

    /**
     * Mark a wallet deposit paid and credit the wallet (atomic, idempotent).
     */
    private function applyWalletDeposit(Deposit $deposit): void
    {
        DB::transaction(function () use ($deposit) {
            if ($deposit->isPaid()) {
                return;
            }

            $deposit->update(['status' => 1]);

            $user = $deposit->user;
            $this->wallet->credit($user, (float) $deposit->amount, 'deposit', [
                'reference'    => $deposit->reference,
                'description'  => 'Wallet deposit via Paystack',
                'related_id'   => $deposit->id,
                'related_type' => Deposit::class,
            ]);
        });
    }

    /**
     * Public Paystack callback endpoint (server-side verification, never trusts client).
     */
    public function callback(Request $request): JsonResponse
    {
        $reference = $request->query('reference') ?: $request->input('reference');

        if (! $reference) {
            return $this->error('Missing payment reference.', 400);
        }

        $deposit = Deposit::where('reference', $reference)->first();
        if (! $deposit) {
            return $this->error('Invalid payment reference.', 404);
        }

        if ($deposit->isPaid()) {
            return $this->ok('Payment already verified.');
        }

        $data = $this->paystack->verify($reference);
        if (! $data) {
            return $this->error('Payment verification failed.', 402);
        }

        if ($deposit->isActivation()) {
            $this->activation->applyActivation($deposit, (float) ($data['amount'] / 100));
        } else {
            $this->applyWalletDeposit($deposit);
        }

        return $this->ok('Payment verified successfully.');
    }
}
