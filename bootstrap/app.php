<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
        then: function () {
            Route::middleware('api')
                ->prefix('api/admin')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\TrustProxies::class);
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);
        $middleware->append(\Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class);
        $middleware->append(\App\Http\Middleware\SystemHealthMiddleware::class);
        $middleware->append(\Illuminate\Http\Middleware\ValidatePostSize::class);
        $middleware->append(\Illuminate\Foundation\Http\Middleware\TrimStrings::class);
        $middleware->append(\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class);

        // Aliases
        $middleware->alias([
            'jwt.auth'    => \Tymon\JWTAuth\Middleware\Authenticate::class,
            'jwt.refresh' => \Tymon\JWTAuth\Middleware\RefreshToken::class,
            'admin'       => \App\Http\Middleware\AdminAuthenticated::class,
            'active'      => \App\Http\Middleware\UserIsActive::class,
            'verified'    => \App\Http\Middleware\UserVerified::class,
            'activated'   => \App\Http\Middleware\WebActivated::class,
            'installed'   => \App\Http\Middleware\ApplicationInstalled::class,
            'installer.key' => \App\Http\Middleware\EnsureAppKeyForInstaller::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        /*
        |--------------------------------------------------------------------------
        | Branded, user-friendly error handling
        |--------------------------------------------------------------------------
        | We NEVER expose raw exception details to end users — that irritates
        | the experience and leaks internals. Instead, every uncaught error is
        | logged (for the admin to review in storage/logs) and rendered through
        | the branded "We Couldn't Process Your Request." screen, which uses
        | the site logo + the admin-managed content from Appearance → Error
        | Screen. The branded screen only appears when a destination does not
        | exist or could not be fetched.
        |
        | - API/JSON/AJAX requests get a clean JSON error (status + message).
        | - Web requests get the branded error view.
        | - NotFoundHttpException (404) → branded screen.
        | - Maintenance (503) → Laravel's built-in maintenance view is kept.
        */
        $exceptions->render(function (\Throwable $e, Request $request) {

            // Skip the installer so the wizard can show its own validation
            // errors normally during /install.
            if ($request->is('install') || $request->is('install/*')) {

                // CRITICAL: On a fresh deployment the .env ships with an
                // empty APP_KEY, but SESSION_ENCRYPT=true means the session
                // service provider tries to resolve the encrypter and throws
                // a fatal MissingAppKeyException BEFORE any middleware or
                // controller can run. We catch it here, generate a key,
                // and redirect back so the page reloads with a valid key
                // and the installer wizard can finally load.
                if ($e instanceof \Illuminate\Encryption\MissingAppKeyException) {
                    // Generate a key and inject it into the running environment
                    // immediately (don't rely on Artisan which can be slow or
                    // fail on shared hosting). Also persist to .env for future
                    // requests.
                    try {
                        $envPath = base_path('.env');
                        if (!file_exists($envPath)) {
                            $example = base_path('.env.example');
                            if (file_exists($example)) {
                                @copy($example, $envPath);
                            }
                        }
                        $randKey = 'base64:'.base64_encode(\Illuminate\Encryption\Encrypter::generateKey('AES-256-CBC'));
                        if (file_exists($envPath)) {
                            $content = (string) file_get_contents($envPath);
                            if (preg_match('/^APP_KEY=.*$/m', $content)) {
                                $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$randKey, $content);
                            } else {
                                $content .= "\nAPP_KEY=".$randKey."\n";
                            }
                            file_put_contents($envPath, $content);
                        }
                        // Inject into running environment so the encrypter
                        // can resolve the key on this same request.
                        $_ENV['APP_KEY']    = $randKey;
                        $_SERVER['APP_KEY'] = $randKey;
                        putenv('APP_KEY='.$randKey);
                        config(['app.key' => $randKey]);
                        try {
                            app()->forgetInstance(\Illuminate\Encryption\Encrypter::class);
                            app()->forgetInstance('encrypter');
                        } catch (\Throwable $forgetErr) {
                            // ignore
                        }
                    } catch (\Throwable $keyErr) {
                        // Last resort: try Artisan key:generate.
                        try {
                            \Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
                        } catch (\Throwable $artisanErr) {
                            // Nothing more we can do.
                        }
                    }
                    return redirect()->to($request->fullUrl());
                }

                return null;
            }

            /*
            |----------------------------------------------------------------------
            | Pass-through exceptions (let Laravel handle them normally)
            |----------------------------------------------------------------------
            | These exception types already produce correct, user-friendly
            | behaviour and must NOT be hijacked by the branded error screen:
            |
            |  - ValidationException      -> redirects back with field errors
            |                                  (or 422 JSON for API).
            |  - HttpResponseException    -> carries an explicit Response the
            |                                  controller chose (redirects, etc).
            |  - AuthenticationException  -> redirects to login (web) / 401 (api).
            |  - AuthorizationException   -> 403, often with a custom message.
            |
            | Returning null tells Laravel to keep its built-in rendering.
            */
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }
            if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return null;
            }
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }
            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return null;
            }

            // Determine the HTTP status code.
            $status = 500;
            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
            } elseif (method_exists($e, 'getStatusCode')) {
                $status = $e->getStatusCode();
            }

            // Maintenance mode is handled by Laravel itself (503 page).
            if ($status === 503) {
                return null;
            }

            /*
            |----------------------------------------------------------------------
            | Friendly HTTP errors (404, 405, 419, etc.) for WEB requests
            |----------------------------------------------------------------------
            | The branded "We Couldn't Process Your Request." screen is shown for
            | destinations that do not exist or could not be fetched (404 / 405 /
            | 419 / other client 4xx) and for genuine server errors (500+).
            | 422 validation is already handled above (pass-through).
            */
            $isApi = $request->expectsJson() || $request->is('api') || $request->is('api/*');

            // CSRF token mismatch (419) -> friendly branded screen.
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                $status = 419;
            }

            // --- JSON / API responses: never leak internals ---
            if ($isApi) {
                $safeMessages = [
                    400 => 'Bad request.',
                    401 => 'Unauthenticated.',
                    403 => 'You do not have permission to do that.',
                    404 => 'The requested resource could not be found.',
                    405 => 'This action is not allowed.',
                    419 => 'Your session has expired. Please refresh and try again.',
                    429 => 'Too many requests. Please slow down.',
                ];
                return response()->json([
                    'success' => false,
                    'message' => $safeMessages[$status] ?? 'We couldn\'t process your request. Please try again later.',
                    'status'  => $status,
                ], $status);
            }

            // --- Web responses: branded error screen ---
            try {
                return response()->view('errors.generic', [
                    'status'    => $status,
                    'exception' => $e,
                ], $status);
            } catch (\Throwable $renderError) {
                // If even the error view can't render (e.g. view cache issue,
                // DB down), fall back to a minimal static page so users never
                // see a raw PHP/Laravel stack trace.
                return response()->make(
                    '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
                    .'<meta name="viewport" content="width=device-width,initial-scale=1">'
                    .'<title>We Couldn\'t Process Your Request.</title></head>'
                    .'<body style="font-family:Segoe UI,system-ui,sans-serif;background:#f8fafc;'
                    .'color:#0f172a;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;padding:1.5rem;">'
                    .'<div style="max-width:480px;text-align:center">'
                    .'<h1 style="font-size:1.6rem;margin-bottom:.6rem">We Couldn\'t Process Your Request.</h1>'
                    .'<p style="color:#64748b;line-height:1.6">The page you\'re looking for may have moved, is temporarily unavailable, or couldn\'t be loaded right now.</p>'
                    .'<p style="margin-top:1.5rem"><a href="'.e(url('/')).'" style="background:#2563eb;color:#fff;padding:.7rem 1.4rem;border-radius:10px;text-decoration:none;font-weight:600">Back to Home</a></p>'
                    .'</div></body></html>',
                    $status,
                    ['Content-Type' => 'text/html; charset=UTF-8']
                );
            }
        });

    })->create();
