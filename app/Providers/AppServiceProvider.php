<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\PaystackService::class, function ($app) {
            return new \App\Services\PaystackService();
        });

        $this->app->singleton('icon.renderer', function ($app) {
            return new \App\Services\IconRendererService();
        });
    }

    public function boot(): void
    {
        Blade::directive('categoryIcon', function ($icon) {
            return "<?php echo app('icon.renderer')->render($icon); ?>";
        });

        Blade::directive('categoryIcon3D', function ($expression) {
            return "<?php echo app('icon.renderer')->render3D($expression); ?>";
        });

        // Shared-hosting-safe media directory. Prefer a real public directory
        // instead of relying on storage:link, which many hosts disable.
        $public = public_path('storage');
        $legacy = storage_path('app/public');
        try {
            if (!is_link($public)) {
                if (!is_dir($public)) {
                    @mkdir($public, 0755, true);
                }

                if (is_dir($legacy) && is_dir($public)) {
                    $marker = $public . DIRECTORY_SEPARATOR . '.airtrend-media-v2';
                    if (!is_file($marker)) {
                        foreach (File::allFiles($legacy) as $file) {
                            $relative = ltrim(str_replace(rtrim($legacy, DIRECTORY_SEPARATOR), '', $file->getPathname()), DIRECTORY_SEPARATOR);
                            $destination = $public . DIRECTORY_SEPARATOR . $relative;
                            $dir = dirname($destination);
                            if (!is_dir($dir)) {
                                @mkdir($dir, 0755, true);
                            }
                            if (!is_file($destination)) {
                                @copy($file->getPathname(), $destination);
                            }
                        }
                        @file_put_contents($marker, 'Airtrendmedia public media directory v2' . PHP_EOL, LOCK_EX);
                    }
                }
            }
        } catch (\Throwable $e) {
            try { report($e); } catch (\Throwable $ignored) {}
        }
    }
}
