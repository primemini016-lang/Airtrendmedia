<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures a valid APP_KEY exists before the installer wizard loads.
 *
 * On a completely fresh deployment the .env file ships with an empty
 * APP_KEY. Because SESSION_ENCRYPT=true, the session service provider
 * resolves the encrypter — which throws a fatal MissingAppKeyException
 * before any controller can run.
 *
 * This middleware runs on /install* routes and guarantees a usable
 * APP_KEY is present BOTH in .env AND in the running config on the
 * SAME request — no server restart required. This is what makes the
 * installer work instantly on real hosting (Apache/Nginx + PHP-FPM)
 * where there is no "artisan serve" auto-restart.
 */
class EnsureAppKeyForInstaller
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = config('app.key');

        // If a valid key is already configured in-memory, we're good.
        if (!empty($key) && str_starts_with($key, 'base64:')) {
            // Still verify it's persisted to .env (defensive).
            $this->ensureKeyPersisted($key);
            return $next($request);
        }

        // Generate + persist + load into the running config in one shot.
        $key = $this->generateAndPersistKey();

        if (!empty($key)) {
            // Push the key into the live config so the encrypter resolves it.
            config(['app.key' => $key]);

            // Force the container to forget the cached encrypter instance so
            // the next resolution rebuilds it with the new key.
            try {
                app()->forgetInstance(\Illuminate\Encryption\Encrypter::class);
                app()->forgetInstance('encrypter');
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $next($request);
    }

    /**
     * Generate a fresh base64 APP_KEY, write it to .env, and return it.
     */
    private function generateAndPersistKey(): string
    {
        $key = 'base64:' . base64_encode(
            \Illuminate\Encryption\Encrypter::generateKey(config('app.cipher') ?: 'AES-256-CBC')
        );
        $this->writeKeyToEnv($key);
        return $key;
    }

    /**
     * Make sure the given key is written to .env (idempotent).
     */
    private function ensureKeyPersisted(string $key): void
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            $this->writeKeyToEnv($key);
            return;
        }
        $content = (string) file_get_contents($envPath);
        if (!preg_match('/^APP_KEY=base64:.*$/m', $content)) {
            $this->writeKeyToEnv($key);
        }
    }

    /**
     * Write (or replace) the APP_KEY line in .env.
     */
    private function writeKeyToEnv(string $key): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $example = base_path('.env.example');
            if (file_exists($example)) {
                @copy($example, $envPath);
            } else {
                @file_put_contents($envPath, "APP_NAME=Airtrendmedia\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=\nAPP_INSTALL=false\n");
            }
        }

        $content = (string) file_get_contents($envPath);
        if (preg_match('/^APP_KEY=.*$/m', $content)) {
            $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $content);
        } else {
            $content = rtrim($content) . "\nAPP_KEY=" . $key . "\n";
        }
        file_put_contents($envPath, $content);
    }
}
