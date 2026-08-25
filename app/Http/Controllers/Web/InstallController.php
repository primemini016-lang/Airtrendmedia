<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AppSetting;
use App\Models\Country;
use App\Models\Currency;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Web installation wizard.
 *
 * Steps: requirements -> database -> app settings -> admin account -> finish
 * On finish, writes storage/app/installed.json (the gate the rest of the app).
 */
class InstallController extends Controller
{
    private function isInstalled(): bool
    {
        return file_exists(storage_path('app/installed.json'));
    }

    public function index()
    {
        if ($this->isInstalled()) {
            return redirect()->route('home');
        }
        return view('install.index');
    }

    public function requirements()
    {
        $checks = [
            'PHP >= 8.3'            => version_compare(PHP_VERSION, '8.3.0', '>='),
            'PDO Extension'         => extension_loaded('pdo'),
            'MySQL (pdo_mysql)'     => extension_loaded('pdo_mysql'),
            'mbstring'              => extension_loaded('mbstring'),
            'openssl'               => extension_loaded('openssl'),
            'curl'                  => extension_loaded('curl'),
            'gd OR imagick'         => extension_loaded('gd') || extension_loaded('imagick'),
            'fileinfo'              => extension_loaded('fileinfo'),
            'json'                  => extension_loaded('json'),
            'storage writable'      => is_writable(storage_path()),
            'bootstrap/cache writable' => is_writable(base_path('bootstrap/cache')),
        ];
        $passed = count(array_filter($checks));
        $allPassed = $passed === count($checks);
        return view('install.requirements', compact('checks', 'passed', 'allPassed'));
    }

    public function database()
    {
        return view('install.database');
    }

    public function runDatabase(Request $request)
    {
        $validated = $request->validate([
            'db_host'     => 'required|string',
            'db_port'     => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
            'app_url'     => 'required|url',
            'app_name'    => 'required|string|max:120',
        ]);

        // Test the connection first.
        try {
            $dsn = "mysql:host={$validated['db_host']};port={$validated['db_port']};dbname={$validated['db_database']};charset=utf8mb4";
            new \PDO($dsn, $validated['db_username'], $validated['db_password'] ?? '', [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Database connection failed: '.$e->getMessage())->withInput();
        }

        // Write .env
        $this->writeEnv($validated);

        // Generate the application key and JWT secret (essential for boot).
        try {
            Artisan::call('key:generate', ['--force' => true]);
            Artisan::call('jwt:secret', ['--force' => true]);
            Artisan::call('config:clear');
        } catch (\Throwable $e) {
            // Non-fatal: keys may already be present.
        }

        // CRITICAL: After writing .env and generating keys, the running
        // application still holds the OLD in-memory config (from before
        // the .env was written). Artisan::call() reuses the same kernel,
        // so migrate:fresh would run against stale DB credentials. We
        // must reload the .env values into the config repository and
        // reconnect the DB so the migration uses the new credentials.
        $this->reloadEnvironment();

        // Run migrations + seeders.
        try {
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
        } catch (\Throwable $e) {
            return back()->with('error', 'Migration failed: '.$e->getMessage())->withInput();
        }

        return redirect()->route('install.app');
    }

    public function appSetup()
    {
        return view('install.app');
    }

    public function saveApp(Request $request)
    {
        $validated = $request->validate([
            'mail_host'           => 'nullable|string|max:191',
            'mail_port'           => 'nullable|numeric',
            'mail_username'       => 'nullable|string|max:191',
            'mail_password'       => 'nullable|string|max:191',
            'mail_from_address'   => 'nullable|email|max:191',
            'mail_from_name'      => 'nullable|string|max:120',
        ]);

        // NOTE: Payment API keys (Paystack) are intentionally NOT collected
        // during installation. They are configured from the Admin Panel under
        // Settings > Payment Keys after the installation is complete.
        $this->updateEnv([
            'MAIL_HOST'           => $validated['mail_host'] ?? '',
            'MAIL_PORT'           => $validated['mail_port'] ?? '587',
            'MAIL_USERNAME'       => $validated['mail_username'] ?? '',
            'MAIL_PASSWORD'       => $validated['mail_password'] ?? '',
            'MAIL_FROM_ADDRESS'   => $validated['mail_from_address'] ?? 'no-reply@airtrendmedia.com',
            'MAIL_FROM_NAME'      => $validated['mail_from_name'] ?? 'MiniWorkers',
        ]);

        Artisan::call('config:clear');

        return redirect()->route('install.admin');
    }

    public function adminSetup()
    {
        return view('install.admin');
    }

    public function saveAdmin(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:120',
            'username' => 'required|string|max:60|unique:admins,username',
            'email'    => 'required|email|max:191|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Admin::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'role'     => 'super',
        ]);

        // Mark installation complete.
        File::put(storage_path('app/installed.json'), json_encode([
            'installed_at' => now()->toDateTimeString(),
            'version'      => '2.0.0',
            'admin'        => $validated['email'],
        ]));

        return redirect()->route('install.finish');
    }

    public function finish()
    {
        return view('install.finish');
    }

    /* ---------- helpers ---------- */

    private function writeEnv(array $db): void
    {
        // Update the existing .env in place, preserving any keys that
        // were already generated (e.g. APP_KEY auto-created by the
        // installer middleware on first load). This is far more robust
        // than rebuilding from .env.example with fragile string matching,
        // which previously wiped out the APP_KEY and broke the session.
        $this->updateEnv([
            'APP_NAME'     => $db['app_name'],
            'APP_URL'      => $db['app_url'],
            'APP_ENV'      => 'production',
            'APP_DEBUG'    => 'false',
            'APP_INSTALL'  => 'false',
            'DB_CONNECTION'=> 'mysql',
            'DB_HOST'      => $db['db_host'],
            'DB_PORT'      => $db['db_port'],
            'DB_DATABASE'  => $db['db_database'],
            'DB_USERNAME'  => $db['db_username'],
            'DB_PASSWORD'  => $db['db_password'] ?? '',
        ]);
    }

    /**
     * Set or replace one or more key=value lines in the .env file,
     * preserving every other line (including APP_KEY / JWT_SECRET).
     */
    private function updateEnv(array $data): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            // Fall back to .env.example if .env does not exist yet.
            if (file_exists(base_path('.env.example'))) {
                File::copy(base_path('.env.example'), $envPath);
            } else {
                File::put($envPath, '');
            }
        }

        $content = File::get($envPath);

        foreach ($data as $key => $value) {
            $value = (string) $value;
            // Quote values that contain spaces or special chars.
            if (preg_match('/[\s#"]/', $value) && $value !== '') {
                $value = '"'.str_replace('"', '\\"', $value).'"';
            }
            $pattern = '/^'.$key.'=.*/m';
            $replacement = $key.'='.$value;
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content = rtrim($content)."\n".$replacement."\n";
            }
        }

        File::put($envPath, $content);
    }

    /**
     * Reload .env values into the running application's config and
     * purge the database connection so subsequent Artisan calls (such
     * as migrate:fresh) use the freshly-written credentials.
     *
     * Without this, Artisan::call() reuses the in-memory config that
     * was loaded at the start of the request — before writeEnv() wrote
     * the new DB credentials — so migrations would run against the old
     * (often empty/invalid) connection and silently fail.
     */
    private function reloadEnvironment(): void
    {
        // Re-read the .env file into the application's environment
        // using the same Dotenv loader Laravel uses at boot. We use
        // createUnsafeMutable so that already-set env values from the
        // initial boot (with the old/empty .env) are overwritten with
        // the freshly-written credentials.
        try {
            if (file_exists(base_path('.env'))) {
                $dotenv = \Dotenv\Dotenv::createUnsafeMutable(base_path(), '.env');
                $dotenv->safeLoad();
            }
        } catch (\Throwable $e) {
            // Non-fatal — we'll set the critical values manually below.
        }

        // Explicitly set the critical config values. We read directly
        // from the .env file (via parseEnvFile) rather than env() because
        // env() may return stale cached values from the initial boot.
        $envValues = $this->parseEnvFile(base_path('.env'));

        app('config')->set([
            'database.default' => $envValues['DB_CONNECTION'] ?? 'mysql',
            'database.connections.mysql.host'     => $envValues['DB_HOST'] ?? '127.0.0.1',
            'database.connections.mysql.port'     => $envValues['DB_PORT'] ?? '3306',
            'database.connections.mysql.database' => $envValues['DB_DATABASE'] ?? '',
            'database.connections.mysql.username' => $envValues['DB_USERNAME'] ?? '',
            'database.connections.mysql.password' => $envValues['DB_PASSWORD'] ?? '',
            'app.key' => $envValues['APP_KEY'] ?? '',
        ]);

        // Purge any existing DB connection so the next query reconnects
        // using the new credentials.
        try {
            DB::purge();
        } catch (\Throwable $e) {
            // Ignore — connection may not exist yet.
        }
    }

    /**
     * Parse a .env file into an associative array of key => value.
     * This reads the file directly, bypassing any caching.
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
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove surrounding quotes.
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
                $value = substr($value, 1, -1);
            }
            $values[$key] = $value;
        }
        return $values;
    }
}
