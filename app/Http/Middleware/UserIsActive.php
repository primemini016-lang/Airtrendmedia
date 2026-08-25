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
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        if ($user->banned) {
            return response()->json(['error' => 'Your account has been blocked.'], 403);
        }
        if (! $user->is_active) {
            return response()->json([
                'error' => 'Account not activated. Please pay the account activation fee to continue.',
                'code'   => 'NOT_ACTIVATED',
            ], 402);
        }
        return $next($request);
    }
}
