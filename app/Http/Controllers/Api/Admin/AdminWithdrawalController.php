<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin withdrawal management: list, mark paid, or reject (refund the
 * debited amount back to the user's wallet with a transaction log).
 */
class AdminWithdrawalController extends Controller
{
    use ApiResponse;

    public function __construct(private WalletService $wallet) {}

    public function index(Request $request): JsonResponse
    {
        $query = Withdrawal::with(['user:id,username,name,email', 'method:id,name,slug']);
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        return $this->ok('Withdrawals retrieved.', $query->latest()->paginate(25));
    }

    public function show(Withdrawal $withdrawal): JsonResponse
    {
        $withdrawal->load(['user', 'method']);
        return $this->ok('Withdrawal retrieved.', $withdrawal);
    }

    public function markPaid(Withdrawal $withdrawal): JsonResponse
    {
        if ((int) $withdrawal->status !== 0) {
            return $this->error('Withdrawal already processed.', 400);
        }
        $withdrawal->update(['status' => 1]);
        return $this->ok('Withdrawal marked as paid.', $withdrawal->fresh());
    }

    /**
     * Reject a withdrawal and refund the held amount to the user's wallet.
     */
    public function reject(Request $request, Withdrawal $withdrawal): JsonResponse
    {
        if ((int) $withdrawal->status !== 0) {
            return $this->error('Withdrawal already processed.', 400);
        }

        $validated = $request->validate([
            'reject_note' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($withdrawal, $validated) {
            $withdrawal->update([
                'status'      => 2,
                'reject_note' => $validated['reject_note'] ?? 'Withdrawal rejected by admin.',
            ]);

            // Refund the full amount (the fee was never separately debited).
            $this->wallet->credit($withdrawal->user, (float) $withdrawal->amount, 'withdraw_refund', [
                'reference'    => 'WRF-'.$withdrawal->id,
                'description'  => 'Withdrawal rejected — funds refunded',
                'related_id'   => $withdrawal->id,
                'related_type' => Withdrawal::class,
            ]);
        });

        return $this->ok('Withdrawal rejected and amount refunded.', $withdrawal->fresh());
    }
}
