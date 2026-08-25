<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        if (! $user->is_verified) {
            return response()->json(['error' => 'Account not verified.'], 403);
        }
        return $next($request);
    }
}
