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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom Blade directive for rendering category social media icons.
        // Maps icon keys to emoji glyphs. Usage: @categoryIcon('facebook') or @categoryIcon($cat->icon)
        Blade::directive('categoryIcon', function ($icon) {
            // $icon is a raw PHP expression string (e.g. "'facebook'" or "$cat->icon")
            // We evaluate it at runtime. If it's an object with an icon property, use that;
            // otherwise treat the value itself as the icon key.
            return "<?php
                \$__icons = [
                    'facebook' => '📘', 'twitter' => '🐦', 'instagram' => '📷',
                    'youtube' => '▶️', 'tiktok' => '🎵', 'linkedin' => '💼',
                    'telegram' => '✈️', 'whatsapp' => '💬', 'writing' => '✍️',
                    'design' => '🎨', 'marketing' => '📈', 'music' => '🎵',
                    'video' => '🎬', 'tech' => '💻', 'briefcase' => '💼',
                    'star' => '⭐', 'heart' => '❤️', 'globe' => '🌐',
                ];
                \$__val = $icon;
                if (is_object(\$__val) && isset(\$__val->icon)) {
                    \$__val = \$__val->icon;
                }
                \$__val = is_string(\$__val) ? \$__val : 'briefcase';
                echo \$__icons[\$__val] ?? \$__icons['briefcase'];
            ?>";
        });
    }
}
