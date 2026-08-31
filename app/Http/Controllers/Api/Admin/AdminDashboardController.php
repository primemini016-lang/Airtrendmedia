<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Deposit;
use App\Models\Task;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;

/**
 * Admin dashboard overview statistics.
 */
class AdminDashboardController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->ok('Dashboard stats retrieved.', [
            'users'           => User::count(),
            'active_users'    => User::where('is_active', true)->count(),
            'banned_users'    => User::where('banned', true)->count(),
            'tasks'           => Task::count(),
            'pending_tasks'   => Task::where('status', 0)->count(),
            'active_tasks'    => Task::where('status', 1)->count(),
            'deposits'        => Deposit::count(),
            'pending_deposits'=> Deposit::where('status', 0)->count(),
            'deposits_total'  => (float) Deposit::where('status', 1)->sum('amount'),
            'withdrawals'     => Withdrawal::count(),
            'pending_withdrawals' => Withdrawal::where('status', 0)->count(),
            'withdrawals_total'   => (float) Withdrawal::where('status', 1)->sum('paid'),
            'open_complaints' => Complaint::where('status', 1)->count(),
            'wallet_balances' => (float) User::sum('balance'),
            'activation_fees' => (float) Deposit::where('type', 'activation')->where('status', 1)->sum('amount'),
            'recent_users'    => User::latest()->limit(8)->get(['id', 'username', 'name', 'email', 'is_active', 'created_at']),
            'recent_transactions' => Transaction::with('user:id,username,name')->latest()->limit(10)->get(),
        ]);
    }
}
