<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Services\ActivationService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin deposit management: list, approve (credit wallet / activate account),
 * and reject manual deposits.
 */
class AdminDepositController extends Controller
{
    use ApiResponse;

    public function __construct(
        private WalletService $wallet,
        private ActivationService $activation,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Deposit::with(['user:id,username,name,email', 'method:id,name,slug']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        return $this->ok('Deposits retrieved.', $query->latest()->paginate(25));
    }

    /**
     * Approve a deposit:
     *  - activation type -> activate the user
     *  - wallet type -> credit the user's wallet
     */
    public function approve(Request $request, Deposit $deposit): JsonResponse
    {
        if ($deposit->isPaid()) {
            return $this->error('Deposit already approved.', 400);
        }

        DB::transaction(function () use ($deposit) {
            $deposit->update(['status' => 1, 'reject_note' => null]);

            if ($deposit->isActivation()) {
                $user = $deposit->user;
                $user->update(['is_active' => true, 'activated_at' => now()]);
                // Fire the affiliate reward via the existing service.
                app(\App\Services\AffiliateService::class)->processActivationReward($deposit);
            } else {
                $this->wallet->credit($deposit->user, (float) $deposit->amount, 'deposit', [
                    'reference'    => $deposit->reference,
                    'description'  => 'Wallet deposit (admin approved)',
                    'related_id'   => $deposit->id,
                    'related_type' => Deposit::class,
                ]);
            }
        });

        return $this->ok('Deposit approved.', $deposit->fresh());
    }

    public function reject(Request $request, Deposit $deposit): JsonResponse
    {
        if ($deposit->isPaid()) {
            return $this->error('Cannot reject an already-approved deposit.', 400);
        }

        $validated = $request->validate([
            'reject_note' => 'nullable|string|max:1000',
        ]);

        $deposit->update([
            'status'      => 2,
            'reject_note' => $validated['reject_note'] ?? 'Deposit rejected by admin.',
        ]);

        return $this->ok('Deposit rejected.', $deposit->fresh());
    }
}
