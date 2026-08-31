<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\WithdrawalMethod;
use App\Services\SettingService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Withdrawal requests: list methods, request a withdrawal (debit on approval),
 * and history. The platform withdrawal commission is retained.
 */
class WithdrawalController extends Controller
{
    use ApiResponse;

    public function __construct(private WalletService $wallet) {}

    /**
     * List active withdrawal methods (parents + gift card options).
     */
    public function methods(): JsonResponse
    {
        $methods = WithdrawalMethod::with(['options' => fn ($q) => $q->where('active', true)])
            ->whereNull('parent_id')
            ->where('active', true)
            ->orderBy('position')
            ->get();

        return $this->ok('Withdrawal methods retrieved.', $methods);
    }

    /**
     * Request a new withdrawal.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user('user');

        $validated = $request->validate([
            'method_id' => 'required|exists:withdrawal_methods,id',
            'amount'    => 'required|numeric|min:1',
            'details'   => 'required|string|max:2000',
        ]);

        $method = WithdrawalMethod::find($validated['method_id']);
        if (! $method->active) {
            return $this->error('This withdrawal method is not active.', 400);
        }
        // Gift-card options carry their own min amount.
        $minAmount = $method->gift_card ? (float) $method->min_amount : 1;
        if ((float) $validated['amount'] < $minAmount) {
            return $this->error('Minimum withdrawal amount for this method is '.$minAmount.'.', 400);
        }

        $settings = app(SettingService::class);
        $commissionPct = (float) $settings->get('withdraw_com', 0);

        $amount = (float) $validated['amount'];
        $fee = round($amount * $commissionPct / 100, 2);
        $paid = round($amount - $fee, 2);

        if ((float) $user->balance < $amount) {
            return $this->error('Insufficient wallet balance.', 402, [
                'balance' => (float) $user->balance,
            ]);
        }

        $withdrawal = DB::transaction(function () use ($user, $validated, $method, $amount, $fee, $paid) {
            // Debit the full amount immediately (held until admin pays / rejects).
            // The fee is retained by the platform; `paid` is what the user receives.
            $debit = $this->wallet->debit($user, $amount, 'withdrawal', [
                'reference'   => 'WDR-'.strtoupper(\Illuminate\Support\Str::random(12)),
                'description' => 'Withdrawal request via '.$method->name.' (receive '.$paid.' after '.$fee.' fee)',
            ]);

            if (! $debit) {
                throw new \Exception('Insufficient balance.');
            }

            return Withdrawal::create([
                'user_id'   => $user->id,
                'method_id' => $method->id,
                'amount'    => $amount,
                'fee'       => $fee,
                'paid'      => $paid,
                'details'   => $validated['details'],
                'status'    => 0,
                'date'      => now()->toDateString(),
            ]);
        });

        return $this->ok('Withdrawal request submitted. You will be paid after admin approval.', $withdrawal, 201);
    }

    /**
     * List the current user's withdrawals.
     */
    public function index(Request $request): JsonResponse
    {
        $withdrawals = Withdrawal::with('method')
            ->where('user_id', $request->user('user')->id)
            ->latest()
            ->paginate(20);

        return $this->ok('Withdrawals retrieved.', $withdrawals);
    }
}
