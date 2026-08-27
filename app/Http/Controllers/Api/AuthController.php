<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\ResetCode;
use App\Mail\ResetPassword as ResetPasswordMail;
use App\Mail\VerificationCode;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\Verification;
use App\Services\AffiliateService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
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
            $field   => $validated['username'],
            'password' => $validated['password'],
        ];

        if (! $token = auth('user')->attempt($credentials)) {
            return $this->error('Invalid username/email or password.', 401);
        }

        $user = auth('user')->user();

        if ($user->banned) {
            auth('user')->logout();
            return $this->error('Your account has been blocked. Please contact support.', 403);
        }

        $needVerification = AppSetting::where('id', 1)->value('need_verification');
        if ($needVerification && ! $user->is_verified) {
            auth('user')->logout();
            return response()->json([
                'error'  => 'Your account is not verified.',
                'email'  => $user->email,
                'code'   => 'NOT_VERIFIED',
            ], 417);
        }

        return response()->json([
            'token'   => $token,
            'user'    => $this->userResource($user),
            'expires' => now()->addMinutes((int) config('jwt.ttl', 10080))->timestamp,
        ]);
    }

    public function register(Request $request, AffiliateService $affiliate): JsonResponse
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:120',
            'username'     => 'required|min:4|max:32|alpha_dash|unique:users,username',
            'email'        => 'required|email|max:191|unique:users,email',
            'phone'        => 'nullable|string|max:30',
            'country_code' => 'nullable|string|max:4',
            'password'     => 'required|string|min:6|confirmed',
            'referrer'     => 'nullable|string|max:32',
        ]);

        $referrer = null;
        if (! empty($validated['referrer'])) {
            $referrer = User::where('username', $validated['referrer'])->where('is_active', true)->first();
        }

        $user = User::create([
            'name'          => $validated['name'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'] ?? null,
            'country_code'  => $validated['country_code'] ?? null,
            'password'      => $validated['password'],
            'referrer_id'   => $referrer?->id,
            'referral_code' => strtoupper(Str::random(8)),
            'balance'       => 0,
            'total_earned'  => 0,
            'is_verified'   => false,
            'is_active'     => false,
        ]);

        // Register affiliate referral (pending until activation fee is paid).
        $affiliate->registerReferral($user);

        $needVerification = AppSetting::where('id', 1)->value('need_verification');
        if ($needVerification) {
            $this->sendVerification($user);
            return $this->ok('Registration successful. Please verify your email with the code sent to you.', [
                'verification_required' => true,
                'email' => $user->email,
            ]);
        }

        // No email verification required -> mark verified, still must pay activation fee.
        $user->update(['is_verified' => true, 'email_verified_at' => now()]);
        $token = auth('user')->login($user);
        return $this->ok('Registration successful. Please pay the account activation fee to start earning.', [
            'token' => $token,
            'verification_required' => false,
            'activation_required' => true,
        ]);
    }

    public function verifyCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first(['id', 'is_verified', 'email', 'name']);
        if (! $user) {
            return $this->error('Email not found.', 404);
        }

        $info = Verification::where('user_id', $user->id)->where('type', 'email')->latest()->first();
        if (! $info) {
            return $this->error('No verification code found. Please request a new one.', 404);
        }
        if ($info->isExpired()) {
            return $this->error('Verification code expired. Please request a new one.', 403);
        }
        if (! hash_equals((string) $info->code, (string) $validated['code'])) {
            return $this->error('Invalid verification code.', 422);
        }

        $user->update(['is_verified' => true, 'email_verified_at' => now()]);
        $info->delete();

        $token = auth('user')->login($user);
        return $this->ok('Email verified successfully.', [
            'token' => $token,
            'activation_required' => ! $user->is_active,
        ]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => 'required|email']);
        $user = User::where('email', $validated['email'])->first(['id', 'is_verified', 'name', 'email']);
        if (! $user) {
            return $this->error('User not found.', 404);
        }
        if ($user->is_verified) {
            return $this->error('Account already verified.', 406);
        }

        $info = Verification::where('user_id', $user->id)->where('type', 'email')->latest()->first();
        if ($info && $info->expire_in > Carbon::now()->addMinutes(25)) {
            return $this->error('Please wait a few minutes before requesting a new code.', 406);
        }
        if ($info) {
            $info->delete();
        }

        $this->sendVerification($user);
        return $this->ok('A new verification code has been sent to your email.');
    }

    public function forgetOtp(Request $request): JsonResponse
    {
        $validated = $request->validate(['email' => 'required|email']);
        $user = User::where('email', $validated['email'])->first(['id', 'name', 'email']);
        if (! $user) {
            return $this->error('User not found.', 404);
        }

        $info = Verification::where('user_id', $user->id)->where('type', 'reset')->latest()->first();
        if ($info && $info->expire_in > Carbon::now()->addMinutes(25)) {
            return $this->error('Please wait a few minutes before requesting a new code.', 406);
        }
        if ($info) {
            $info->delete();
        }

        $otp = random_int(100001, 999999);
        Verification::create([
            'user_id'  => $user->id,
            'code'     => (string) $otp,
            'type'     => 'reset',
            'expire_in'=> Carbon::now()->addMinutes(30),
        ]);

        Mail::to($user->email)->send(new ResetCode(['otp' => $otp, 'user' => $user->name]));
        return $this->ok('A password reset code has been sent to your email.');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $validated['email'])->first(['id', 'name', 'email']);
        if (! $user) {
            return $this->error('Email not found.', 404);
        }

        $info = Verification::where('user_id', $user->id)->where('type', 'reset')->latest()->first();
        if (! $info) {
            return $this->error('No reset code found. Please request a new one.', 404);
        }
        if ($info->isExpired()) {
            return $this->error('Reset code expired. Please request a new one.', 403);
        }
        if (! hash_equals((string) $info->code, (string) $validated['code'])) {
            return $this->error('Invalid reset code.', 422);
        }

        $user->update(['password' => $validated['password']]);
        $info->delete();

        Mail::to($user->email)->send(new ResetPasswordMail(['user' => $user->name]));
        return $this->ok('Password reset successful. You can now log in with your new password.');
    }

    public function me(): JsonResponse
    {
        $user = auth('user')->user();
        if (! $user) {
            return $this->error('Unauthenticated.', 401);
        }
        if ($user->banned) {
            return $this->error('Your account has been blocked.', 403);
        }
        return $this->ok('authorized', $this->userResource($user));
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ]);
        $user = auth('user')->user();
        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password is incorrect.', 403);
        }
        $user->update(['password' => $validated['password']]);
        return $this->ok('Password changed successfully.');
    }

    public function logout(): JsonResponse
    {
        auth('user')->logout();
        return $this->ok('Successfully logged out.');
    }

    /* ---------- helpers ---------- */
    private function sendVerification(User $user): void
    {
        $otp = random_int(100001, 999999);
        Verification::create([
            'user_id'   => $user->id,
            'code'      => (string) $otp,
            'type'      => 'email',
            'expire_in' => Carbon::now()->addMinutes(30),
        ]);
        Mail::to($user->email)->send(new VerificationCode(['otp' => $otp, 'user' => $user->name]));
    }

    private function userResource(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'username'      => $user->username,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'country_code'  => $user->country_code,
            'image'         => $user->image ? asset($user->image) : null,
            'bio'           => $user->bio,
            'balance'       => (float) $user->balance,
            'total_earned'  => (float) $user->total_earned,
            'is_verified'   => (bool) $user->is_verified,
            'is_active'     => (bool) $user->is_active,
            'banned'        => (bool) $user->banned,
            'referral_code' => $user->referral_code,
            'referrer'      => $user->referrer ? ['username' => $user->referrer->username] : null,
            'created_at'    => $user->created_at,
        ];
    }
}
