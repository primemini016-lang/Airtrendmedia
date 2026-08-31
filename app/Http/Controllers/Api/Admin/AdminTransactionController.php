<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin transaction ledger: full oversight of every balance movement,
 * filterable by user, type, status, and date range.
 */
class AdminTransactionController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with('user:id,username,name,email');

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to.' 23:59:59');
        }

        $transactions = $query->latest()->paginate(25);

        // Summary aggregates for the current filter.
        $summary = [
            'total_credits' => (float) (clone $query)->where('amount', '>', 0)->sum('amount'),
            'total_debits'  => (float) (clone $query)->where('amount', '<', 0)->sum('amount'),
            'count'         => (clone $query)->count(),
        ];

        return $this->ok('Transactions retrieved.', ['transactions' => $transactions, 'summary' => $summary]);
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load('user');
        return $this->ok('Transaction retrieved.', $transaction);
    }
}
