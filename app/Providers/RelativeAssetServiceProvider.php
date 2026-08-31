<?php

namespace App\Providers;

use App\Services\RelativeAssetUrlGenerator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

/**
 * Binds a custom URL generator that emits root-relative asset URLs so that
 * uploaded images and assets resolve correctly on ANY domain the app is
 * served from (not just the APP_URL configured in .env).
 */
class RelativeAssetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register(): void
    {
        // Bind our custom URL generator over Laravel's default so that the
        // asset() helper (which calls app('url')->asset(...)) produces
        // root-relative URLs.
        $this->app->extend('url', function ($service, $app) {
            $generator = new RelativeAssetUrlGenerator(
                $app['router']->getRoutes(),
                $app['request'],
                $app['config']->get('app.asset_url')
            );

            $generator->setSessionResolver(function () use ($app) {
                return $app['session'] ?? null;
            });

            $generator->setKeyResolver(function () use ($app) {
                return $app['config']->get('app.key');
            });

            // Set the root URL from the request so relative resolution works.
            try {
                $generator->forceRootUrl($app['request']->root());
            } catch (\Throwable $e) {
                // ignore during console / testing
            }

            return $generator;
        });
    }

    public function boot(): void
    {
        // Force generated URLs to be root-relative by default in production.
        // This ensures route() and url() helpers produce relative paths too.
        if ($this->app->bound('url')) {
            $url = $this->app->make('url');
            if (method_exists($url, 'forceRootUrl')) {
                // Use the actual request host, not APP_URL.
                try {
                    $url->forceRootUrl(request()->root());
                } catch (\Throwable $e) {
                    // ignore during console / testing
                }
            }
        }
    }
}
