<?php

namespace App\Http\Middleware;

use App\Services\SystemHealthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SystemHealthMiddleware
 *
 * Hooks the inbuilt AI auto-correction system into every web request. The
 * actual sweep is throttled inside SystemHealthService (once per ~5 min), so
 * the per-request overhead is essentially a single cache lookup. When the
 * throttle window is open the sweep runs and silently fixes common issues
 * (missing APP_KEY, broken storage perms, dead symlinks, etc.) before the
 * request continues to the controller.
 *
 * This middleware is intentionally registered LAST (so it runs first on the
 * way in) and is scoped to the "installed" group only — it never runs during
 * the /install wizard so the installer can manage its own bootstrap.
 */
class SystemHealthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Skip the installer — it has its own APP_KEY bootstrap logic.
        if ($request->is('install') || $request->is('install/*')) {
            return $next($request);
        }

        // Throttled self-healing sweep. Errors here must NEVER break the
        // user's request — they are swallowed entirely.
        try {
            SystemHealthService::sweepIfDue();
        } catch (\Throwable $e) {
            // Intentionally silent: the AI fixer must never become the cause
            // of a 500 itself.
        }

        return $next($request);
    }
}
