<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates admin API requests using the 'admin' JWT guard.
 */
class AdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth('admin')->check()) {
            return response()->json(['error' => 'Admin unauthenticated.'], 401);
        }
        return $next($request);
    }
}
