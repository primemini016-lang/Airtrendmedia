<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Web (Blade) variant of the activation guard: redirects inactive users to the
 * activation/pay page instead of returning JSON.
 *
 * Uses the 'web' (session) guard explicitly because the application default
 * guard is 'user' (JWT) which is only for the REST API.
 */
class WebActivated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();
        if (! $user) {
            return redirect()->route('login');
        }
        if ($user->banned) {
            Auth::guard('web')->logout();
            return redirect()->route('login')->with('error', 'Your account has been blocked.');
        }
        if (! $user->is_active) {
            return redirect()->route('user.activate')->with('info', 'Please pay the $5 account activation fee to access the marketplace.');
        }
        return $next($request);
    }
}
