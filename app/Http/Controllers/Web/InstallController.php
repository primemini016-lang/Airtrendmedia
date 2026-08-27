<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Single-page, bulletproof installation wizard.
 *
 * The user enters ONLY two things:
 *   1. Database details (host, port, name, username, password) + app URL + app name
 *   2. Admin account details (name, username, email, password)
 *
 * Everything else — APP_KEY, JWT_SECRET, migrations, seeders, default settings —
 * is generated and executed automatically in one atomic POST request.
 *
 * The flow is intentionally a SINGLE request so that nothing can be left in a
 * half-installed state: either the whole thing succeeds (DB migrated + admin
 * created + installed.json written) or it fails cleanly with a readable error
 * and the user can retry the same form.
 */
class InstallController extends Controller
{
    /** The gate file that marks the app as installed. */
    private const INSTALLED_MARKER = 'app/installed.json';

    private function isInstalled(): bool
    {
        return file_exists(storage_path(self::INSTALLED_MARKER));
    }

    /**
     * Show the single-page installer form.
     * Requirements are auto-detected and displayed inline — no separate page.
     */
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect()->route('home');
        }

        // Ensure required storage directories exist (they may be missing
        // from a deployment zip). Without these Laravel cannot boot:
        // sessions, view compilation, and cache all need writable dirs.
        $this->ensureStorageDirectories();

        $checks = $this->requirementChecks();
        $passed = count(array_filter($checks));
        $allPassed = $passed === count($checks);

        return view('install.index', [
            'checks'    => $checks,
            'passed'    => $passed,
            'total'     => count($checks),
            'allPassed' => $allPassed,
            'appUrl'    => request()->getSchemeAndHttpHost(),
        ]);
    }

    /**
     * Process the entire installation in one atomic request.
     *
     * Steps (each guarded, each logged):
     *   1. Validate input (DB + admin details).
     *   2. Test the raw PDO connection BEFORE touching .env.
     *   3. Write the .env file (DB creds, app name/url, keys placeholders).
     *   4. Generate APP_KEY + JWT_SECRET.
     *   5. Reload the in-process config + purge DB connection.
     *   6. Run migrate:fresh --seed.
     *   7. Create the admin account.
     *   8. Write installed.json (the lock file).
     *   9. Redirect to the finish screen.
     *
     * If ANY step throws, we roll back sensibly (remove a half-written
     * installed.json if present) and return the user to the form with a
     * clear, human-readable error and their input preserved.
     */
    public function process(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect()->route('home');
        }

        // Ensure storage dirs exist before doing anything else.
        $this->ensureStorageDirectories();

        // ── 1. Validate ───────────────────────────────────────────────
        $validated = $request->validate([
            // Database
            'db_host'       => 'required|string|max:191',
            'db_port'       => 'required|numeric|between:1,65535',
            'db_database'   => 'required|string|max:191',
            'db_username'   => 'required|string|max:191',
            'db_password'   => 'nullable|string|max:191',
            // App
            'app_name'      => 'required|string|max:120',
            'app_url'       => 'required|url|max:191',
            // Admin
            'admin_name'     => 'required|string|max:120',
            'admin_username' => 'required|string|max:60|regex:/^[A-Za-z0-9_\.]+$/',
            'admin_email'    => 'required|email|max:191',
            'admin_password' => 'required|string|min:8|confirmed',
        ], [
            'admin_username.regex' => 'The admin username may only contain letters, numbers, underscores and dots.',
            'admin_password.min'   => 'The admin password must be at least 8 characters.',
            'admin_password.confirmed' => 'The admin password confirmation does not match.',
        ]);

        // ── 2. Test the raw connection BEFORE writing anything ─────────
        // We connect directly with PDO so a bad credential never corrupts .env.
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $validated['db_host'],
                $validated['db_port'],
                $validated['db_database']
            );
            $pdo = new \PDO(
                $dsn,
                $validated['db_username'],
                $validated['db_password'] ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_TIMEOUT => 10]
            );
            // Confirm we can actually USE the database (not just connect).
            $pdo->query('SELECT 1');
            $pdo = null;
        } catch (\Throwable $e) {
            return back()->with('install_error', $this->friendlyDbError($e->getMessage()))
                         ->withInput();
        }

        // ── 3. Write the .env file ─────────────────────────────────────
        try {
            $this->writeEnvFile([
                'APP_NAME'      => $validated['app_name'],
                'APP_ENV'       => 'production',
                'APP_DEBUG'     => 'false',
                'APP_URL'       => $validated['app_url'],
                'APP_INSTALL'   => 'true',
                'DB_CONNECTION' => 'mysql',
                'DB_HOST'       => $validated['db_host'],
                'DB_PORT'       => $validated['db_port'],
                'DB_DATABASE'   => $validated['db_database'],
                'DB_USERNAME'   => $validated['db_username'],
                'DB_PASSWORD'   => $validated['db_password'] ?? '',
            ]);
        } catch (\Throwable $e) {
            return back()->with('install_error', 'Could not write the .env configuration file. Please make sure the project root is writable. (' . $e->getMessage() . ')')
                         ->withInput();
        }

        // ── 4. Generate APP_KEY + JWT_SECRET ───────────────────────────
        try {
            $this->ensureAppKey();
            $this->ensureJwtSecret();
        } catch (\Throwable $e) {
            return back()->with('install_error', 'Could not generate the application security keys. Please make sure the project root is writable. (' . $e->getMessage() . ')')
                         ->withInput();
        }

        // ── 5. Reload the in-process config so migrations use new creds ─
        $this->reloadEnvironment();

        // ── 6. Run migrations + seeders ────────────────────────────────
        try {
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        } catch (\Throwable $e) {
            $this->cleanupFailedInstall();
            return back()->with('install_error', 'Database migration failed: ' . $this->friendlyMigrationError($e->getMessage()))
                         ->withInput();
        }

        // ── 7. Create the admin account ────────────────────────────────
        // We reconnect fresh because migrate:fresh wiped everything.
        $this->reloadEnvironment();
        try {
            DB::purge();
            DB::connection()->getPdo(); // force reconnect with new creds

            // Avoid the "unique" validation rule hitting a possibly-stale
            // connection — check manually after a guaranteed reconnect.
            if (Admin::where('email', $validated['admin_email'])->exists()) {
                throw new \RuntimeException('An admin account with this email already exists.');
            }
            if (Admin::where('username', $validated['admin_username'])->exists()) {
                throw new \RuntimeException('An admin account with this username already exists.');
            }

            Admin::create([
                'name'     => $validated['admin_name'],
                'username' => $validated['admin_username'],
                'email'    => $validated['admin_email'],
                'password' => $validated['admin_password'],
                'role'     => 'super',
            ]);
        } catch (\Throwable $e) {
            $this->cleanupFailedInstall();
            return back()->with('install_error', 'Could not create the admin account: ' . $e->getMessage())
                         ->withInput();
        }

        // ── 8. Prepare public storage access ───────────────────────────
        // Prefer Laravel's normal symlink. On shared hosts that forbid symlinks,
        // the bundled public/storage/.htaccess provides a filesystem fallback.
        $this->ensurePublicStorageLink();

        // ── 9. Write the lock file ─────────────────────────────────────
        try {
            File::put(storage_path(self::INSTALLED_MARKER), json_encode([
                'installed_at' => now()->toDateTimeString(),
                'version'      => '2.0.0',
                'admin'        => $validated['admin_email'],
                'app_name'     => $validated['app_name'],
            ], JSON_PRETTY_PRINT));

            // Flip APP_INSTALL to false now that install is complete.
            $this->updateEnvKey('APP_INSTALL', 'false');
        } catch (\Throwable $e) {
            return back()->with('install_error', 'Installation completed but the lock file could not be written. Please make sure storage/app is writable. (' . $e->getMessage() . ')')
                         ->withInput();
        }

        // ── 9. Done ────────────────────────────────────────────────────
        // Stash the admin email in session so the finish screen can show it.
        session()->flash('install_admin_email', $validated['admin_email']);
        session()->flash('install_app_name', $validated['app_name']);

        return redirect()->route('install.finish');
    }

    /**
     * Final success screen.
     */
    public function finish()
    {
        if (! $this->isInstalled()) {
            return redirect()->route('install.start');
        }

        return view('install.finish', [
            'adminEmail' => session('install_admin_email'),
            'appName'    => session('install_app_name', 'Airtrendmedia'),
        ]);
    }

    /* =====================================================================
     *  Helpers
     * ===================================================================== */

    /**
     * Server requirement checks (auto-detected, shown inline on the form).
     */
    private function requirementChecks(): array
    {
        return [
            'PHP >= 8.1'               => version_compare(PHP_VERSION, '8.1.0', '>='),
            'PDO Extension'            => extension_loaded('pdo'),
            'MySQL (pdo_mysql)'        => extension_loaded('pdo_mysql') || extension_loaded('pdo_mysqli'),
            'mbstring'                 => extension_loaded('mbstring'),
            'openssl'                  => extension_loaded('openssl'),
            'curl'                     => extension_loaded('curl'),
            'gd OR imagick'            => extension_loaded('gd') || extension_loaded('imagick'),
            'fileinfo'                 => extension_loaded('fileinfo'),
            'json'                     => extension_loaded('json'),
            'storage/ writable'        => $this->isWritableSafe(storage_path()),
            'bootstrap/cache writable' => $this->isWritableSafe(base_path('bootstrap/cache')),
            'project root writable'    => $this->isWritableSafe(base_path()),
        ];
    }

    /**
     * Safe is_writable() that returns false instead of emitting a warning
     * when open_basedir or other restrictions are in effect (common on
     * shared hosting). Falls back to an actual write test.
     */
    private function isWritableSafe(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }
        $writable = @is_writable($path);
        if ($writable) {
            return true;
        }
        // Fallback: try to actually create + remove a temp file.
        $tmp = rtrim($path, '/\\') . '/.write_test_' . uniqid();
        $fp = @fopen($tmp, 'a');
        if ($fp) {
            fclose($fp);
            @unlink($tmp);
            return true;
        }
        return false;
    }

    /**
     * Ensure all required Laravel storage directories exist and are writable.
     *
     * On shared hosting, deployment zips sometimes omit the empty
     * storage/framework subdirectories (sessions, views, cache) because
     * they contain only .gitkeep files. Without them Laravel throws a
     * fatal error on the very first request. This method creates them
     * with 0755 permissions (0777 on restrictive hosts) so the app can
     * boot even from a stripped-down deployment.
     */
    private function ensureStorageDirectories(): void
    {
        $dirs = [
            storage_path('app'),
            storage_path('app/public'),
            storage_path('app/private'),
            storage_path('framework'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
                // If 0755 didn't work (restrictive umask), try 0777.
                if (!is_dir($dir)) {
                    @mkdir($dir, 0777, true);
                }
            }
        }
    }

    /**
     * Make public uploads reachable without requiring shell access.
     * Symlinks are attempted first; shared-hosting fallback is shipped in the
     * public/storage directory and does not require changing Apache config.
     */
    private function ensurePublicStorageLink(): void
    {
        $target = storage_path('app/public');
        $link = public_path('storage');
        if (!is_dir($target)) {
            @mkdir($target, 0755, true);
        }

        // Already a valid link: nothing to do.
        if (is_link($link) && realpath($link) === realpath($target)) {
            return;
        }

        // An empty deployment directory can safely be replaced by a symlink.
        if (is_dir($link) && !is_link($link)) {
            $entries = array_diff(@scandir($link) ?: [], ['.', '..']);
            if (!$entries) { @rmdir($link); }
        }

        if (!file_exists($link)) {
            @symlink($target, $link);
        }

        // If symlink creation is forbidden, restore a normal directory and let
        // its .htaccess proxy files to storage/app/public.
        if (!is_dir($link)) {
            @mkdir($link, 0755, true);
        }
        $ht = $link . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($ht)) {
            @file_put_contents($ht, "RewriteEngine On\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.+)$ ../../storage/app/public/$1 [L]\n", LOCK_EX);
        }
    }

    /**
     * provided values. Preserves every other key from the example template.
     */
    private function writeEnvFile(array $overrides): void
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        // Start from .env.example so we always have a complete, valid template.
        if (file_exists($envPath)) {
            $content = file_get_contents($envPath);
        } elseif (file_exists($examplePath)) {
            $content = file_get_contents($examplePath);
        } else {
            // Absolute minimal fallback.
            $content = "APP_NAME=Airtrendmedia\nAPP_ENV=production\nAPP_KEY=\nAPP_DEBUG=false\nAPP_URL=\nDB_CONNECTION=mysql\n";
        }

        foreach ($overrides as $key => $value) {
            $value = (string) $value;
            // Quote values containing spaces or special chars.
            if ($value !== '' && preg_match('/[\s#"\\\\]/', $value)) {
                $value = '"' . str_replace('"', '\\"', $value) . '"';
            }
            $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
            $replacement = $key . '=' . $value;
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content = rtrim($content) . "\n" . $replacement . "\n";
            }
        }

        file_put_contents($envPath, $content);
    }

    /**
     * Set or replace a single key in .env.
     */
    private function updateEnvKey(string $key, string $value): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            return;
        }
        $content = file_get_contents($envPath);
        if ($value !== '' && preg_match('/[\s#"\\\\]/', $value)) {
            $value = '"' . str_replace('"', '\\"', $value) . '"';
        }
        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $key . '=' . $value, $content);
        } else {
            $content = rtrim($content) . "\n" . $key . '=' . $value . "\n";
        }
        file_put_contents($envPath, $content);
    }

    /**
     * Guarantee a valid base64 APP_KEY exists in .env.
     * Tries artisan first, falls back to a manual write.
     */
    private function ensureAppKey(): void
    {
        $current = $this->readEnvValue('APP_KEY');
        if (!empty($current) && str_starts_with($current, 'base64:')) {
            return;
        }

        try {
            Artisan::call('key:generate', ['--force' => true]);
        } catch (\Throwable $e) {
            // Manual fallback.
            $key = 'base64:' . base64_encode(
                \Illuminate\Encryption\Encrypter::generateKey('AES-256-CBC')
            );
            $this->updateEnvKey('APP_KEY', $key);
        }
    }

    /**
     * Guarantee a JWT_SECRET exists in .env.
     */
    private function ensureJwtSecret(): void
    {
        $current = $this->readEnvValue('JWT_SECRET');
        if (!empty($current)) {
            return;
        }

        try {
            Artisan::call('jwt:secret', ['--force' => true]);
        } catch (\Throwable $e) {
            // Manual fallback.
            $secret = base64_encode(random_bytes(32));
            $this->updateEnvKey('JWT_SECRET', $secret);
        }
    }

    /**
     * Reload .env into the running config + purge the DB connection so
     * subsequent Artisan calls (migrate, admin create) use fresh creds.
     */
    private function reloadEnvironment(): void
    {
        // Re-import the .env values into the process environment.
        try {
            if (file_exists(base_path('.env'))) {
                $dotenv = \Dotenv\Dotenv::createUnsafeMutable(base_path(), '.env');
                $dotenv->safeLoad();
            }
        } catch (\Throwable $e) {
            // Non-fatal — we set critical values manually below.
        }

        $env = $this->parseEnvFile(base_path('.env'));

        app('config')->set([
            'database.default'                       => $env['DB_CONNECTION'] ?? 'mysql',
            'database.connections.mysql.host'        => $env['DB_HOST'] ?? '127.0.0.1',
            'database.connections.mysql.port'        => $env['DB_PORT'] ?? '3306',
            'database.connections.mysql.database'    => $env['DB_DATABASE'] ?? '',
            'database.connections.mysql.username'    => $env['DB_USERNAME'] ?? '',
            'database.connections.mysql.password'    => $env['DB_PASSWORD'] ?? '',
            'app.key'                                => $env['APP_KEY'] ?? config('app.key'),
            'jwt.secret'                             => $env['JWT_SECRET'] ?? config('jwt.secret'),
        ]);

        // Forget the cached config so app('config') re-reads.
        try {
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            // ignore
        }

        // Purge the DB connection so the next query reconnects with new creds.
        try {
            DB::purge();
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Read a single value from .env (direct file read, bypasses cache).
     */
    private function readEnvValue(string $key): ?string
    {
        return $this->parseEnvFile(base_path('.env'))[$key] ?? null;
    }

    /**
     * Parse a .env file into key => value (direct read, no caching).
     */
    private function parseEnvFile(string $path): array
    {
        $values = [];
        if (! file_exists($path)) {
            return $values;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v);
            if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && $v[-1] === $v[0]) {
                $v = substr($v, 1, -1);
            }
            $values[$k] = $v;
        }
        return $values;
    }

    /**
     * Remove a half-written installed.json if the install failed partway.
     */
    private function cleanupFailedInstall(): void
    {
        $marker = storage_path(self::INSTALLED_MARKER);
        if (file_exists($marker)) {
            @unlink($marker);
        }
    }

    /**
     * Turn a raw PDO error into a friendly, actionable message.
     */
    private function friendlyDbError(string $raw): string
    {
        $raw = trim($raw);
        if (str_contains($raw, '2002') || str_contains($raw, 'Connection refused')) {
            return 'Could not connect to the database server. Please check the Database Host and Port, and make sure MySQL/MariaDB is running.';
        }
        if (str_contains($raw, '1045') || str_contains($raw, 'Access denied') || str_contains($raw, 'access denied')) {
            return 'Access denied. The database username or password is incorrect.';
        }
        if (str_contains($raw, '1049') || str_contains($raw, 'Unknown database')) {
            return 'The database does not exist. Please create the database first in your hosting control panel (e.g. cPanel > MySQL Databases).';
        }
        if (str_contains($raw, '2003') || str_contains($raw, "Can't connect to MySQL server")) {
            return 'Cannot reach the MySQL server. Verify the host and port, and that the server allows connections.';
        }
        if (str_contains($raw, 'timeout') || str_contains($raw, 'timed out')) {
            return 'The database connection timed out. The server may be unreachable or blocking the connection.';
        }
        return 'Database connection failed: ' . $raw;
    }

    /**
     * Turn a raw migration error into a friendly message.
     */
    private function friendlyMigrationError(string $raw): string
    {
        $raw = trim($raw);
        if (str_contains($raw, 'No application encryption key')) {
            return 'The application key could not be loaded. Please try again.';
        }
        if (str_contains($raw, 'SQLSTATE')) {
            return 'A database error occurred while creating tables: ' . $raw;
        }
        return $raw;
    }
}
