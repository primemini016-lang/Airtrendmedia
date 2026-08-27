<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the Paystack service as a singleton.
        $this->app->singleton(\App\Services\PaystackService::class, function ($app) {
            return new \App\Services\PaystackService();
        });

        // Bind the icon renderer (used by the @categoryIcon blade directive).
        $this->app->singleton('icon.renderer', function ($app) {
            return new \App\Services\IconRendererService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom Blade directive for rendering category/social icons as inline SVG.
        // Usage: @categoryIcon('facebook') or @categoryIcon($cat->icon)
        Blade::directive('categoryIcon', function ($icon) {
            return "<?php echo app('icon.renderer')->render($icon); ?>";
        });
    }
}
