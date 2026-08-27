<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin user management: list, view, ban/unban, manual activation,
 * balance adjustment (with full transaction log), and search.
 */
class AdminUserController extends Controller
{
    use ApiResponse;

    public function __construct(private WalletService $wallet) {}

    public function index(Request $request): JsonResponse
    {
        $query = User::with('country')->latest();

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->has('active')) {
            $query->where('is_active', (bool) $request->boolean('active'));
        }
        if ($request->has('banned')) {
            $query->where('banned', (bool) $request->boolean('banned'));
        }

        return $this->ok('Users retrieved.', $query->paginate(25));
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['country', 'referrer:id,username,name', 'transactions' => fn ($q) => $q->limit(50)]);
        return $this->ok('User retrieved.', $user);
    }

    public function toggleBan(User $user): JsonResponse
    {
        $user->update(['banned' => ! $user->banned]);
        return $this->ok($user->banned ? 'User banned.' : 'User unbanned.', $user->fresh());
    }

    public function manualActivate(User $user): JsonResponse
    {
        if ($user->is_active) {
            return $this->error('User is already active.', 400);
        }
        $user->update(['is_active' => true, 'activated_at' => now()]);
        return $this->ok('User manually activated.', $user->fresh());
    }

    /**
     * Admin manual balance adjustment (logged in the transactions ledger).
     */
    public function adjustBalance(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|not_in:0',
            'description' => 'required|string|max:500',
        ]);

        $txn = $this->wallet->adminAdjust($user, (float) $validated['amount'], $validated['description']);

        return $this->ok('Balance adjusted.', [
            'user'     => $user->fresh(),
            'transaction' => $txn,
        ]);
    }

    public function transactions(User $user): JsonResponse
    {
        return $this->ok('User transactions retrieved.', $user->transactions()->paginate(25));
    }

    public function destroy(User $user): JsonResponse
    {
        $admin = auth('admin')->user();
        if (! $admin->isSuper()) {
            return $this->error('Only super-admins can delete users.', 403);
        }
        $user->delete();
        return $this->ok('User deleted.');
    }
}
