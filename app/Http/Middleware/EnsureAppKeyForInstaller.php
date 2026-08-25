<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures a valid APP_KEY exists before the installer wizard loads.
 *
 * On a completely fresh deployment the .env file ships with an empty
 * APP_KEY (and empty JWT_SECRET). The installer wizard is supposed to
 * generate these keys during the "database" step, but Laravel's session
 * component is configured with SESSION_ENCRYPT=true, which means the
 * session service provider tries to use the encrypter — and the
 * encrypter throws a fatal MissingAppKeyException before any controller
 * code (or even the exception handler) can run. The result is that the
 * /install page crashes with a raw PHP fatal error, which is exactly
 * the "script is not running at all" experience users hit.
 *
 * This middleware runs only on the /install* routes and guarantees a
 * usable APP_KEY is present in .env before the request is handed to
 * the session machinery. If a key already exists it is left untouched.
 * The installer itself will (re)generate both keys during the database
 * step, so this is purely a "get the wizard to boot" safety net.
 */
class EnsureAppKeyForInstaller
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = config('app.key');

        // If a valid key is already configured, nothing to do.
        if (!empty($key) && str_starts_with($key, 'base64:')) {
            return $next($request);
        }

        // Attempt to generate one via artisan (writes to .env).
        try {
            \Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Throwable $e) {
            // Fall back to writing a random key directly into .env if
            // artisan is unavailable for any reason.
            $this->writeKeyManually();
        }

        // Reload the config so the rest of the request picks up the key.
        $newKey = config('app.key');
        if (empty($newKey)) {
            // Read directly from .env as a last resort.
            $this->writeKeyManually();
            $newKey = env('APP_KEY');
        }

        // Make the freshly-generated key immediately available to the
        // encrypter / session services without a full reboot.
        if (!empty($newKey)) {
            config(['app.key' => $newKey]);
            app()->forgetInstance(\Illuminate\Encryption\Encrypter::class);
            app()->forgetInstance('encrypter');
        }

        return $next($request);
    }

    /**
     * Write a random base64 APP_KEY into the .env file manually.
     */
    private function writeKeyManually(): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            // No .env at all — copy from .env.example first.
            $example = base_path('.env.example');
            if (file_exists($example)) {
                @copy($example, $envPath);
            } else {
                @file_put_contents($envPath, "APP_NAME=Airtrendmedia\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_INSTALL=false\n");
            }
        }

        $key = 'base64:'.base64_encode(
            \Illuminate\Encryption\Encrypter::generateKey(config('app.cipher') ?: 'AES-256-CBC')
        );

        $content = (string) file_get_contents($envPath);

        if (preg_match('/^APP_KEY=.*$/m', $content)) {
            $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $content);
        } else {
            $content .= "\nAPP_KEY=".$key."\n";
        }

        file_put_contents($envPath, $content);
        config(['app.key' => $key]);
    }
}
