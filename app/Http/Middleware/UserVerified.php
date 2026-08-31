<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserVerified
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
        if (! $user->is_verified) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Account not verified.'], 403);
            }
            return redirect()->route('dashboard')
                ->with('error', 'Please verify your account to access this page.');
        }
        return $next($request);
    }
}
