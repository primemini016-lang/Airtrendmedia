<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * SystemHealthService — Inbuilt "AI" Auto-Correction System
 *
 * This is the platform's self-healing layer. It runs automatically on every
 * web request (throttled to once per N minutes per server) and silently fixes
 * the most common deployment-breakage issues so the site keeps working even
 * when the environment degrades:
 *
 *  - Missing / blank APP_KEY  -> generates and persists one immediately.
 *  - Missing / blank JWT_SECRET (Tymon JWT Auth) -> generates and persists one.
 *  - storage/ and bootstrap/cache/ directories missing -> recreates them.
 *  - storage/framework/{sessions,views,cache} sub-folders missing -> recreates.
 *  - storage/logs missing -> recreates + writes an empty log file.
 *  - public/storage symlink broken or missing -> re-creates the storage:link.
 *  - Storage directories not writable -> attempts to chmod them to 0775/0755.
 *  - Stale view/config cache files that reference deleted classes -> cleared.
 *  - .env file missing entirely -> copied from .env.example (if present).
 *
 * The service is deliberately defensive: every single fix is wrapped in its
 * own try/catch so that one failing operation never stops the rest. Every
 * action is logged to storage/logs/laravel.log AND to the system_health cache
 * key so the admin "System Update" tab can display the last auto-fix report.
 *
 * This is the "inbuilt regular automatic system function AI" the owner
 * requested: it corrects errors and makes sure everything works whenever the
 * website is accessed.
 */
class SystemHealthService
{
    /** Minimum seconds between full health sweeps on the same server. */
    public const SWEEP_INTERVAL = 300; // 5 minutes

    /** Cache key for the last sweep timestamp. */
    public const LAST_SWEEP_KEY = 'system_health:last_sweep';

    /** Cache key for the running auto-fix report (array of fixes). */
    public const REPORT_KEY = 'system_health:report';

    /**
     * Run a full self-healing sweep if enough time has elapsed.
     * Returns the report (array of human-readable fixes) or null if skipped.
     */
    public static function sweepIfDue(): ?array
    {
        // Throttle: only run once per interval to avoid overhead on every hit.
        if (Cache::has(self::LAST_SWEEP_KEY)) {
            return null;
        }

        try {
            Cache::put(self::LAST_SWEEP_KEY, now()->timestamp, now()->addSeconds(self::SWEEP_INTERVAL));
        } catch (\Throwable $e) {
            // If the cache backend is broken we still try to run fixes below.
        }

        return self::sweep(true);
    }

    /**
     * Run the full health sweep now (used by the admin System Update tab
     * when the admin clicks "Run Diagnostics", bypassing the throttle).
     *
     * @param bool $throttled Whether this was called via the throttled path.
     * @return array Report of fixes applied.
     */
    public static function sweep(bool $throttled = false): array
    {
        $report = [];
        $startedAt = microtime(true);

        // Each check is isolated so a failure in one cannot abort the others.
        $report = array_merge($report, self::ensureEnvExists());
        $report = array_merge($report, self::ensureAppKey());
        $report = array_merge($report, self::ensureJwtSecret());
        $report = array_merge($report, self::ensureStorageDirectories());
        $report = array_merge($report, self::ensureStoragePermissions());
        $report = array_merge($report, self::ensurePublicStorageLink());
        $report = array_merge($report, self::clearStaleOptimizationCache());
        $report = array_merge($report, self::ensureLogFile());

        $elapsed = round((microtime(true) - $startedAt) * 1000, 1);
        $fixCount = count($report);
        $report[] = sprintf('[%s] AI self-healing sweep finished in %sms (%d fix(es) applied).', now()->toDateTimeString(), $elapsed, $fixCount);

        // Persist the report so the admin dashboard can show it.
        try {
            Cache::put(self::REPORT_KEY, $report, now()->addDay());
        } catch (\Throwable $e) {
            // ignore cache failures
        }

        // Log a summary line for server admins.
        try {
            Log::info('SystemHealth sweep complete', [
                'fixes' => count($report),
                'ms'    => $elapsed,
            ]);
        } catch (\Throwable $e) {
            // ignore
        }

        return $report;
    }

    /**
     * Get the most recent stored report (for the admin UI).
     */
    public static function lastReport(): array
    {
        try {
            return Cache::get(self::REPORT_KEY, []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* -----------------------------------------------------------------
     * Individual self-healing checks
     * Each returns an array of human-readable strings for the report.
     * ----------------------------------------------------------------- */

    /**
     * Ensure a .env file exists (copy from .env.example if missing).
     */
    protected static function ensureEnvExists(): array
    {
        $report = [];
        try {
            $env = base_path('.env');
            if (! file_exists($env)) {
                $example = base_path('.env.example');
                if (file_exists($example)) {
                    copy($example, $env);
                    $report[] = 'Restored missing .env file from .env.example.';
                } else {
                    // Write a minimal .env so the app can boot.
                    file_put_contents($env, "APP_NAME=Airtrendmedia\nAPP_ENV=production\nAPP_DEBUG=false\nAPP_KEY=\n");
                    $report[] = 'Created minimal .env file (no .env.example found).';
                }
            }
        } catch (\Throwable $e) {
            $report[] = 'Could not ensure .env exists: ' . $e->getMessage();
        }
        return $report;
    }

    /**
     * Generate a valid APP_KEY if the current one is blank/missing.
     */
    protected static function ensureAppKey(): array
    {
        $report = [];
        try {
            $key = config('app.key');
            if (empty($key) || $key === 'base64:' || strlen($key) < 20) {
                $newKey = 'base64:' . base64_encode(\Illuminate\Encryption\Encrypter::generateKey('AES-256-CBC'));
                self::writeEnv('APP_KEY', $newKey);
                // Inject into the running request so the encrypter resolves.
                config(['app.key' => $newKey]);
                $_ENV['APP_KEY'] = $newKey;
                putenv('APP_KEY=' . $newKey);
                $report[] = 'Generated and persisted a missing APP_KEY.';
            }
        } catch (\Throwable $e) {
            $report[] = 'Could not ensure APP_KEY: ' . $e->getMessage();
        }
        return $report;
    }

    /**
     * Generate a JWT_SECRET if Tymon JWT Auth is installed and the secret is
     * blank/missing. Without this, every API login 500s with "JWT secret not
     * set".
     */
    protected static function ensureJwtSecret(): array
    {
        $report = [];
        try {
            // Only act if the jwt config file is published.
            $jwtConfig = config('jwt.secret');
            if ($jwtConfig === null) {
                return $report; // JWT not configured at all — skip.
            }
            if (empty($jwtConfig) || strlen($jwtConfig) < 16) {
                $newSecret = bin2hex(random_bytes(32)); // 64-char hex string
                self::writeEnv('JWT_SECRET', $newSecret);
                config(['jwt.secret' => $newSecret]);
                $_ENV['JWT_SECRET'] = $newSecret;
                putenv('JWT_SECRET=' . $newSecret);
                $report[] = 'Generated and persisted a missing JWT_SECRET.';
            }
        } catch (\Throwable $e) {
            $report[] = 'Could not ensure JWT_SECRET: ' . $e->getMessage();
        }
        return $report;
    }

    /**
     * Ensure all required storage directories exist.
     */
    protected static function ensureStorageDirectories(): array
    {
        $report = [];
        try {
            $dirs = [
                storage_path(),
                storage_path('app'),
                storage_path('app/public'),
                storage_path('app/public/avatars'),
                storage_path('app/public/posts'),
                storage_path('app/public/cover'),
                storage_path('app/public/kyc'),
                storage_path('app/public/gigs'),
                storage_path('app/public/marketplace'),
                storage_path('app/public/listings'),
                storage_path('app/public/stories'),
                storage_path('app/public/blogs'),
                storage_path('app/public/sponsored'),
                storage_path('framework'),
                storage_path('framework/cache'),
                storage_path('framework/cache/data'),
                storage_path('framework/sessions'),
                storage_path('framework/views'),
                storage_path('logs'),
                base_path('bootstrap/cache'),
            ];
            $created = 0;
            foreach ($dirs as $dir) {
                if (! is_dir($dir)) {
                    @mkdir($dir, 0775, true);
                    $created++;
                }
            }
            if ($created > 0) {
                $report[] = sprintf('Recreated %d missing storage/framework directory(ies).', $created);
            }
        } catch (\Throwable $e) {
            $report[] = 'Could not ensure storage directories: ' . $e->getMessage();
        }
        return $report;
    }

    /**
     * Ensure storage directories are writable; attempt to fix perms if not.
     */
    protected static function ensureStoragePermissions(): array
    {
        $report = [];
        try {
            $targets = [
                storage_path(),
                base_path('bootstrap/cache'),
            ];
            $fixed = 0;
            foreach ($targets as $target) {
                if (! is_dir($target)) {
                    continue;
                }
                if (! is_writable($target)) {
                    @chmod($target, 0775);
                    if (is_writable($target)) {
                        $fixed++;
                    } else {
                        @chmod($target, 0777);
                        if (is_writable($target)) {
                            $fixed++;
                        }
                    }
                }
            }
            if ($fixed > 0) {
                $report[] = sprintf('Fixed write permissions on %d directory(ies).', $fixed);
            }
        } catch (\Throwable $e) {
            $report[] = 'Could not ensure storage permissions: ' . $e->getMessage();
        }
        return $report;
    }

    /**
     * Ensure the public/storage symlink exists and points to storage/app/public.
     */
    protected static function ensurePublicStorageLink(): array
    {
        $report = [];
        try {
            $link = public_path('storage');
            $target = storage_path('app/public');

            if (! is_dir($target)) {
                @mkdir($target, 0775, true);
            }

            if (! file_exists($link)) {
                @symlink($target, $link);
                if (file_exists($link)) {
                    $report[] = 'Created missing public/storage symlink.';
                }
            } elseif (is_link($link) && readlink($link) !== $target) {
                // Broken / wrong symlink — recreate it.
                @unlink($link);
                @symlink($target, $link);
                $report[] = 'Repaired broken public/storage symlink.';
            }
        } catch (\Throwable $e) {
            $report[] = 'Could not ensure public/storage symlink: ' . $e->getMessage();
        }
        return $report;
    }

    /**
     * If a cached bootstrap/cache/services.php / config.php / routes.php
     * exists but references a stale class layout, it can hard-crash the app.
     * We don't always wipe these (they help performance in production), but
     * if the app is currently failing to boot we can't detect that here —
     * so this check is intentionally conservative: it only clears the cache
     * files if the compiled config references a missing .env value pattern.
     */
    protected static function clearStaleOptimizationCache(): array
    {
        $report = [];
        try {
            $cacheDir = base_path('bootstrap/cache');
            // Only clear if the cache files contain the literal string "${"
            // (an unexpanded env placeholder), which means the .env was not
            // present when the config was cached.
            $stale = false;
            foreach (glob($cacheDir . '/*.php') as $cached) {
                $contents = (string) @file_get_contents($cached);
                if (strpos($contents, '${') !== false) {
                    $stale = true;
                    break;
                }
            }
            if ($stale) {
                foreach (glob($cacheDir . '/*.php') as $cached) {
                    @unlink($cached);
                }
                $report[] = 'Cleared stale bootstrap cache with unexpanded env placeholders.';
            }
        } catch (\Throwable $e) {
            $report[] = 'Could not clear stale optimization cache: ' . $e->getMessage();
        }
        return $report;
    }

    /**
     * Ensure storage/logs/laravel.log exists and is writable.
     */
    protected static function ensureLogFile(): array
    {
        $report = [];
        try {
            $log = storage_path('logs/laravel.log');
            if (! file_exists($log)) {
                @touch($log);
                @chmod($log, 0664);
                $report[] = 'Created missing storage/logs/laravel.log file.';
            } elseif (! is_writable($log)) {
                @chmod($log, 0664);
                if (is_writable($log)) {
                    $report[] = 'Fixed write permission on laravel.log.';
                }
            }
        } catch (\Throwable $e) {
            $report[] = 'Could not ensure log file: ' . $e->getMessage();
        }
        return $report;
    }

    /* -----------------------------------------------------------------
     * Helpers
     * ----------------------------------------------------------------- */

    /**
     * Write or update a key=value line in the .env file.
     */
    protected static function writeEnv(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            // Create a minimal .env so we have something to write to.
            file_put_contents($envPath, $key . '=' . $value . "\n");
            return;
        }

        $content = (string) file_get_contents($envPath);

        // Escape values that contain spaces or special chars.
        $escapedValue = $value;
        if (preg_match('/[\s#"]/', $escapedValue)) {
            $escapedValue = '"' . str_replace('"', '\\"', $escapedValue) . '"';
        }

        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $key . '=' . $escapedValue, $content);
        } else {
            $content = rtrim($content) . "\n" . $key . '=' . $escapedValue . "\n";
        }

        file_put_contents($envPath, $content);
    }
}
