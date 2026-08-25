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
        copy($examplePath, $envPath);
    }

    if (function_exists('random_bytes')) {
        $key         = 'base64:'.base64_encode(random_bytes(32));
        $envContents = file_get_contents($envPath);
        $envContents = preg_replace('/^APP_KEY=.*/m', 'APP_KEY='.$key, $envContents);
        file_put_contents($envPath, $envContents);
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
