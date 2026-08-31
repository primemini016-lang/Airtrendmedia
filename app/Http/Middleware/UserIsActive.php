<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the user account is active (paid the $5 activation fee) before
 * performing tasks / getting paid. Read-only routes (browse, profile) are
 * still allowed for inactive users so they can explore before paying.
 */
class UserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Use the 'web' session guard for Blade frontend routes.
        // The default guard is 'user' (JWT) which crashes with
        // "Secret is not set" if JWT_SECRET is missing.
        $user = auth('web')->user() ?? auth()->user();
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }
        if ($user->banned) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Your account has been blocked.'], 403);
            }
            return redirect()->route('login')
                ->with('error', 'Your account has been blocked.');
        }
        if (! $user->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Account not activated. Please pay the account activation fee to continue.',
                    'code'   => 'NOT_ACTIVATED',
                ], 402);
            }
            return redirect()->route('activate-account')
                ->with('error', 'Please pay the account activation fee to continue.');
        }
        return $next($request);
    }
}
