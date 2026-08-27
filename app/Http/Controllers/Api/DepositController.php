<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\DepositMethod;
use App\Services\PaystackService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Manual deposit methods (e.g. bank transfer) + deposit history.
 * Paystack wallet deposits are handled by PaymentController.
 */
class DepositController extends Controller
{
    use ApiResponse;

    public function __construct(private WalletService $wallet) {}

    /**
     * List active deposit methods.
     */
    public function methods(): JsonResponse
    {
        $methods = DepositMethod::where('active', true)->orderBy('position')->get();
        return $this->ok('Deposit methods retrieved.', $methods);
    }

    /**
     * Submit a manual deposit request (admin will approve after confirming payment).
     */
    public function manualStore(Request $request): JsonResponse
    {
        $user = $request->user('user');

        $validated = $request->validate([
            'method_id' => 'required|exists:deposit_methods,id',
            'amount'    => 'required|numeric|min:1',
            'reference' => 'required|string|max:80',
            'note'      => 'nullable|string|max:1000',
        ]);

        $method = DepositMethod::find($validated['method_id']);
        if (! $method->active) {
            return $this->error('This deposit method is not active.', 400);
        }

        $deposit = Deposit::create([
            'user_id'   => $user->id,
            'method_id' => $method->id,
            'amount'    => $validated['amount'],
            'type'      => 'wallet',
            'reference' => $validated['reference'],
            'manual'    => true,
            'note'      => $validated['note'] ?? null,
            'status'    => 0,
            'date'      => now()->toDateString(),
        ]);

        return $this->ok('Manual deposit submitted. It will be credited after admin approval.', $deposit, 201);
    }

    /**
     * List the current user's deposits.
     */
    public function index(Request $request): JsonResponse
    {
        $deposits = Deposit::with('method')
            ->where('user_id', $request->user('user')->id)
            ->latest()
            ->paginate(20);

        return $this->ok('Deposits retrieved.', $deposits);
    }
}
