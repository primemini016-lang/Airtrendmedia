<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Auto-Create .env File (For Fresh Deployments)
|--------------------------------------------------------------------------
|
| On shared hosting (e.g. GoogieHost) there is no SSH/Composer access, so
| the .env file – which is excluded from the deployment ZIP for security –
| does not exist on first upload.  Without an APP_KEY Laravel cannot boot
| and the web installer never runs.  This block copies .env.example to
| .env and injects a freshly generated APP_KEY so the installer wizard
| can load.  The installer later overwrites DB and APP settings.
|
*/

if (! file_exists(__DIR__.'/../.env')) {
    $examplePath = __DIR__.'/../.env.example';
    $envPath     = __DIR__.'/../.env';

    if (file_exists($examplePath)) {
        @copy($examplePath, $envPath);
    }

    if (function_exists('random_bytes')) {
        $key         = 'base64:'.base64_encode(random_bytes(32));
        $envContents = @file_get_contents($envPath);
        if ($envContents !== false) {
            $envContents = preg_replace('/^APP_KEY=.*/m', 'APP_KEY='.$key, $envContents);
            @file_put_contents($envPath, $envContents);
        }
        // Also set as environment variable so Laravel boots even if the
        // .env file write failed (e.g. read-only root on shared hosting).
        $_ENV['APP_KEY']     = $key;
        $_SERVER['APP_KEY']  = $key;
        putenv('APP_KEY='.$key);
    }
} else {
    // .env exists — make sure it has a valid APP_KEY. If it's empty or
    // missing, inject one so the app can boot (the installer will
    // regenerate it properly later).
    $envContents = @file_get_contents(__DIR__.'/../.env');
    if ($envContents !== false) {
        $hasValidKey = preg_match('/^APP_KEY=base64:[A-Za-z0-9\/+=]{40,}$/m', $envContents);
        if (!$hasValidKey) {
            if (function_exists('random_bytes')) {
                $key = 'base64:'.base64_encode(random_bytes(32));
                $envContents = preg_replace('/^APP_KEY=.*/m', 'APP_KEY='.$key, $envContents);
                @file_put_contents(__DIR__.'/../.env', $envContents);
                $_ENV['APP_KEY']    = $key;
                $_SERVER['APP_KEY'] = $key;
                putenv('APP_KEY='.$key);
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Ensure Required Storage Directories Exist
|--------------------------------------------------------------------------
|
| Deployment zips sometimes omit the empty storage/framework subdirectories
| (sessions, views, cache) because they contain only .gitkeep files.
| Without them Laravel throws a fatal error on the very first request
| because the session driver, view compiler, and cache store all need
| writable directories. This block creates them if missing so the app
| can boot even from a stripped-down deployment.
|
*/

$storageBase = __DIR__.'/../storage';
$requiredDirs = [
    $storageBase.'/app',
    $storageBase.'/app/public',
    $storageBase.'/app/private',
    $storageBase.'/framework',
    $storageBase.'/framework/cache',
    $storageBase.'/framework/cache/data',
    $storageBase.'/framework/sessions',
    $storageBase.'/framework/views',
    $storageBase.'/logs',
    __DIR__.'/../bootstrap/cache',
];

foreach ($requiredDirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }
}

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
)->send();

$kernel->terminate($request, $response);
