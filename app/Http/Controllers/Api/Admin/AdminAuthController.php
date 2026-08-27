<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Admin authentication (JWT, admin guard). No demo credentials — admins are
 * created during the web installer or by a super-admin.
 */
class AdminAuthController extends Controller
{
    use ApiResponse;

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($validated['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $field    => $validated['username'],
            'password' => $validated['password'],
        ];

        if (! $token = auth('admin')->attempt($credentials)) {
            return $this->error('Invalid admin credentials.', 401);
        }

        $admin = auth('admin')->user();
        $admin->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return response()->json([
            'token' => $token,
            'admin' => $admin,
            'expires_in' => auth('admin')->factory()->getTTL() * 60,
        ]);
    }

    public function me(): JsonResponse
    {
        return $this->ok('Admin profile.', auth('admin')->user());
    }

    public function logout(): JsonResponse
    {
        auth('admin')->logout();
        return $this->ok('Logged out successfully.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $admin = auth('admin')->user();
        if (! Hash::check($validated['current_password'], $admin->password)) {
            return $this->error('Current password is incorrect.', 422);
        }

        $admin->update(['password' => $validated['password']]);
        return $this->ok('Password changed successfully.');
    }

    /**
     * Super-admin: create a new admin account.
     */
    public function store(Request $request): JsonResponse
    {
        $creator = auth('admin')->user();
        if (! $creator->isSuper()) {
            return $this->error('Only super-admins can create admin accounts.', 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:120',
            'username' => 'required|string|max:60|unique:admins,username',
            'email'    => 'required|email|max:191|unique:admins,email',
            'password' => 'required|string|min:8',
            'role'     => 'nullable|in:admin,super',
        ]);

        $admin = Admin::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'role'     => $validated['role'] ?? 'admin',
        ]);

        return $this->ok('Admin account created.', $admin, 201);
    }
}
