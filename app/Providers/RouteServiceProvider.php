<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        Route::pattern('id', '[0-9]+');
        Route::pattern('code', '[a-zA-Z0-9\-]+');
        Route::pattern('slug', '[a-z0-9\-]+');
        Route::pattern('username', '[a-zA-Z0-9_\.]+');
    }
}
