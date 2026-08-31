<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCode;
use App\Models\AppSetting;
use App\Models\Country;
use App\Models\User;
use App\Models\Verification;
use App\Services\AffiliateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

/**
 * Session-based authentication for the Blade frontend (public + user).
 *
 * IMPORTANT: The application's default guard is 'user' (JWT) which is used
 * by the REST API.  The Blade frontend uses session-based authentication
 * via the 'web' guard, so every Auth:: call here MUST explicitly specify
 * Auth::guard('web') — otherwise it falls back to the JWT guard and
 * Auth::user() returns null after login, causing a 500 error.
 */
class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($validated['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Use the 'web' (session) guard explicitly — NOT the default JWT guard.
        if (Auth::guard('web')->attempt([$field => $validated['username'], 'password' => $validated['password']], $request->boolean('remember'))) {
            $user = Auth::guard('web')->user();

            // Regenerate the session AFTER we already have the user object.
            $request->session()->regenerate();

            if ($user->banned) {
                Auth::guard('web')->logout();
                return back()->with('error', 'Your account has been blocked.')->withInput();
            }

            $needVerification = AppSetting::where('id', 1)->value('need_verification');
            if ($needVerification && ! $user->is_verified) {
                Auth::guard('web')->logout();
                $request->session()->put('verify_email', $user->email);
                return redirect()->route('verify')->with('info', 'Please verify your email with the code sent to you.');
            }

            return $user->is_active
                ? redirect()->intended(route('user.dashboard'))
                : redirect()->route('user.activate');
        }

        return back()->with('error', 'Invalid username/email or password.')->withInput();
    }

    public function showRegister(Request $request)
    {
        $countries = Country::orderBy('name')->get();
        $referrer = $request->query('ref') ?: $request->cookie('airtrend_ref');
        if ($referrer) { Cookie::queue(Cookie::make('airtrend_ref', $referrer, 60 * 24 * 120, '/', null, $request->isSecure(), true, false, 'lax')); }
        return view('auth.register', compact('countries', 'referrer'));
    }

    public function register(Request $request, AffiliateService $affiliate)
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

        $referrerUser = null;
        if (! empty($validated['referrer'])) {
            $referrerUser = User::where('username', $validated['referrer'])->orWhere('referral_code', $validated['referrer'])->first();
        }
        if (!$referrerUser) {
            $cookieRef = $request->cookie('airtrend_ref');
            if ($cookieRef) {
                $referrerUser = User::where('username',$cookieRef)->orWhere('referral_code',$cookieRef)->first();
            }
        }

        $user = User::create([
            'name'          => $validated['name'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'] ?? null,
            'country_code'  => $validated['country_code'] ?? null,
            'password'      => $validated['password'],
            'referrer_id'   => $referrerUser?->id,
            'referral_code' => strtoupper(Str::random(8)),
            'balance'       => 0,
            'total_earned'  => 0,
            'is_verified'   => false,
            'is_active'     => false,
            'registration_ip' => $request->ip(),
        ]);

        $affiliate->registerReferral($user);

        $needVerification = AppSetting::where('id', 1)->value('need_verification');
        if ($needVerification) {
            $this->sendVerification($user);
            $request->session()->put('verify_email', $user->email);
            return redirect()->route('verify')->with('success', 'Registration successful! Check your email for the verification code.');
        }

        $user->update(['is_verified' => true, 'email_verified_at' => now()]);
        Auth::guard('web')->login($user);
        return redirect()->route('user.activate')->with('info', 'Welcome! Pay the '.money(app(\App\Services\SettingService::class)->activationFee()).' activation fee to start earning.');
    }

    public function showVerify(Request $request)
    {
        $email = $request->session()->get('verify_email');
        if (! $email) {
            return redirect()->route('login');
        }
        return view('auth.verify', compact('email'));
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (! $user) {
            return back()->with('error', 'User not found.');
        }

        $record = Verification::where('user_id', $user->id)->where('type', 'email')->latest()->first();
        if (! $record || $record->isExpired()) {
            return back()->with('error', 'Verification code expired. Please request a new one.');
        }

        if (! hash_equals((string) $record->code, (string) $validated['code'])) {
            return back()->with('error', 'Invalid verification code.');
        }

        $user->update(['is_verified' => true, 'email_verified_at' => now()]);
        $record->delete();
        Auth::guard('web')->login($user);
        $request->session()->forget('verify_email');

        return redirect()->route('user.activate')->with('success', 'Email verified! Pay the $5 activation fee to start earning.');
    }

    public function showReset()
    {
        return view('auth.reset');
    }

    public function sendReset(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email']);
        $user = User::where('email', $validated['email'])->first();
        if ($user) {
            $otp = random_int(100001, 999999);
            Verification::create([
                'user_id'   => $user->id,
                'code'      => (string) $otp,
                'type'      => 'reset',
                'expire_in' => Carbon::now()->addMinutes(30),
            ]);
            $this->sendMailSafely($user->email, new VerificationCode(['otp' => $otp, 'user' => $user->name]));
        }
        $request->session()->put('reset_email', $validated['email']);
        return redirect()->route('password.reset')->with('success', 'If that email exists, a reset code has been sent.');
    }

    public function showResetConfirm(Request $request)
    {
        $email = $request->session()->get('reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }
        return view('auth.reset-confirm', compact('email'));
    }

    public function resetConfirm(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'code'     => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $validated['email'])->first();
        if (! $user) {
            return back()->with('error', 'User not found.');
        }
        $record = Verification::where('user_id', $user->id)->where('type', 'reset')->latest()->first();
        if (! $record || $record->isExpired() || ! hash_equals((string) $record->code, (string) $validated['code'])) {
            return back()->with('error', 'Invalid or expired reset code.');
        }

        $user->update(['password' => $validated['password']]);
        $record->delete();
        $request->session()->forget('reset_email');

        return redirect()->route('login')->with('success', 'Password reset successful. Please log in.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    private function sendVerification(User $user): void
    {
        $otp = random_int(100001, 999999);
        Verification::create([
            'user_id'   => $user->id,
            'code'      => (string) $otp,
            'type'      => 'email',
            'expire_in' => Carbon::now()->addMinutes(30),
        ]);
        $this->sendMailSafely($user->email, new VerificationCode(['otp' => $otp, 'user' => $user->name]));
    }

    /**
     * Send an email safely — if SMTP is not configured or the mail server
     * is unreachable, log the error instead of throwing a 500 exception.
     * This prevents registration/login from breaking when email settings
     * have not yet been set up in the admin panel.
     */
    private function sendMailSafely(string $email, $mailable): void
    {
        try {
            Mail::to($email)->send($mailable);
        } catch (\Throwable $e) {
            Log::warning('Email could not be sent (SMTP may not be configured yet): ' . $e->getMessage());
        }
    }
}
