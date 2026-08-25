<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
