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
        $stub = File::get(base_path('.env.example'));
        $env = strtr($stub, [
            'APP_NAME=MiniWorkers'     => 'APP_NAME='.$db['app_name'],
            'APP_URL=http://localhost' => 'APP_URL='.$db['app_url'],
            'DB_HOST=127.0.0.1'        => 'DB_HOST='.$db['db_host'],
            'DB_PORT=3306'             => 'DB_PORT='.$db['db_port'],
            'DB_DATABASE=miniworkers'  => 'DB_DATABASE='.$db['db_database'],
            'DB_USERNAME=root'         => 'DB_USERNAME='.$db['db_username'],
            'DB_PASSWORD='             => 'DB_PASSWORD='.$db['db_password'],
        ]);
        File::put(base_path('.env'), $env);
    }

    private function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        if (! File::exists($envPath)) {
            return;
        }
        $content = File::get($envPath);
        foreach ($data as $key => $value) {
            $value = (string) $value;
            // Quote values containing spaces.
            if (preg_match('/\s/', $value) && ! preg_match('/^".*"$/', $value)) {
                $value = '"'.$value.'"';
            }
            if (preg_match('/^'.$key.'=.*/m', $content)) {
                $content = preg_replace('/^'.$key.'=.*/m', $key.'='.$value, $content);
            } else {
                $content .= "\n".$key.'='.$value;
            }
        }
        File::put($envPath, $content);
    }
}
