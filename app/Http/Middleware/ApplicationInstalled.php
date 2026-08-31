<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access to the main application until the installer has completed.
 * The installer routes themselves bypass this via the 'installed' exclusion.
 *
 * Also guarantees a JWT_SECRET exists at runtime so that the default JWT
 * guard never throws "Secret is not set" on a misconfigured server.
 */
class ApplicationInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! file_exists(storage_path('app/installed.json'))) {
            return redirect()->to('/install');
        }
        $this->ensureStorageSymlink();


        // ── Guarantee JWT_SECRET at runtime ──────────────────────────
        // If the key is missing from .env (e.g. manual deployment without
        // running the installer's key-generation step), generate one
        // in-memory so the JWT guard doesn't crash every page load.
        if (empty(config('jwt.secret'))) {
            $secret = bin2hex(random_bytes(32));
            config(['jwt.secret' => $secret]);

            // Best-effort write to .env so it persists across requests.
            $this->writeEnvKey('JWT_SECRET', $secret);
        }

        return $next($request);
    }

    /**
     * Write a key=value pair to .env if the key doesn't already exist.
     */
    protected function writeEnvKey(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        // Replace an existing empty/stale value as well. This is important for
        // JWT_SECRET: leaving `JWT_SECRET=` unchanged would generate a new secret
        // on every request and invalidate every issued token.
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $key . '=' . $value, $content, 1);
            @file_put_contents($envPath, $content, LOCK_EX);
            return;
        }

        @file_put_contents($envPath, rtrim($content) . PHP_EOL . $key . '=' . $value . PHP_EOL, LOCK_EX);
    }

    /**
     * Ensure the public/storage symlink exists and points to
     * storage/app/public. This fixes the "blank/broken profile image"
     * bug that occurs when the repo ships public/storage as a regular
     * directory (with a .gitkeep placeholder) instead of a symlink.
     */
    protected function ensureStorageSymlink(): void
    {
        $target = storage_path('app/public');
        $link   = public_path('storage');

        // Already a valid symlink — nothing to do.
        if (is_link($link) && realpath($link) === realpath($target)) {
            return;
        }

        // Make sure the target directory exists.
        if (! is_dir($target)) {
            @mkdir($target, 0755, true);
        }

        // If public/storage is a regular directory, try to remove it
        // (only if it's empty or contains just a .gitkeep placeholder).
        if (is_dir($link) && ! is_link($link)) {
            $entries = array_diff(@scandir($link) ?: [], ['.', '..', '.gitkeep']);
            if (empty($entries)) {
                @unlink($link . DIRECTORY_SEPARATOR . '.gitkeep');
                @rmdir($link);
            }
        }

        // Create the symlink if the path is now free.
        if (! file_exists($link) && ! is_link($link)) {
            @symlink($target, $link);
        }
    }
}
