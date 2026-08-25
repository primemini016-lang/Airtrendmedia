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
 *
 * Also allows access for users on the 3-day free trial (hasAccess() check).
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
        // Allow if the user is activated OR on a valid free trial
        if ($user->hasAccess()) {
            return $next($request);
        }
        // If the trial expired, inform the user
        if ($user->trial_ends_at && now()->gte($user->trial_ends_at) && ! $user->is_active) {
            return redirect()->route('user.activate')
                ->with('error', 'Your 3-day free trial has ended. Please pay the $5 activation fee to continue.');
        }
        return redirect()->route('user.activate')
            ->with('info', 'Please pay the $5 account activation fee or start a 3-day free trial to access the marketplace.');
    }
}
