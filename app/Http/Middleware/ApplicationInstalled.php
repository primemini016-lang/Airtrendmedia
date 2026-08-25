<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to the main application until the installer has completed.
 * The installer routes themselves bypass this via the 'installed' exclusion.
 */
class ApplicationInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! file_exists(storage_path('app/installed.json'))) {
            return redirect()->to('/install');
        }
        return $next($request);
    }
}
