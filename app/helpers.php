<?php

/**
 * Airtrendmedia helper functions.
 *
 * Uploaded public media is served from /storage so it remains valid on the
 * current host, during installation, and on shared hosting where symlinks can
 * be disabled. The helper also sanitizes accidental raw Blade-comment markers
 * before administrator-injected HTML is rendered.
 */

if (! function_exists('storage_asset')) {
    function storage_asset(?string $path = null): string
    {
        if ($path === null || $path === '') {
            return '/storage';
        }

        $path = trim((string) $path);
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $relative = str_starts_with($path, 'storage/') ? substr($path, 8) : $path;

        // Root-relative URLs work for airtrendmedia.com and remain install-safe
        // when the same project is mounted below a web subdirectory.
        $base = '';
        try {
            if (app()->bound('request')) {
                $base = rtrim((string) app('request')->getBaseUrl(), '/');
            }
        } catch (Throwable $ignored) {}

        $url = $base . '/storage/' . ltrim($relative, '/');
        try {
            $version = (int) cache()->get('airtrend:image_cache_version', 1);
            $url .= '?v=' . max(1, $version);
        } catch (Throwable $ignored) {}
        return $url;
    }
}

if (! function_exists('money')) {
    /**
     * Format a monetary amount using the platform's default currency symbol.
     *
     * This replaces every hard-coded "$" sign in the views so that the admin
     * can change the platform currency (e.g. to Nigerian Naira ₦) and have
     * every amount on the front-end and back-end update automatically.
     *
     * @param  float|int|string|null  $amount
     * @param  int                    $decimals
     * @return string
     */
    function money($amount, int $decimals = 2): string
    {
        $value = (float) ($amount ?? 0);

        try {
            $settings = app(\App\Services\SettingService::class);
            $currency = $settings->defaultCurrency();
            if ($currency) {
                return $currency->symbol . number_format($value, $decimals);
            }
        } catch (\Throwable $e) {
            // Fall through to the Naira default below if the container is not
            // available yet (e.g. during early bootstrap or unit tests).
        }

        // Sensible fallback so amounts are never blank.
        return '₦' . number_format($value, $decimals);
    }
}

if (! function_exists('site_injected_html')) {
    /**
     * Prevent old administrator content such as @* ... *@ from being shown
     * literally in the page when it was saved as HTML rather than Blade.
     * This does not strip normal HTML comments or ordinary @ symbols.
     */
    function site_injected_html(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $clean = preg_replace('/@\\*.*?\\*@/s', '', $html);
        return is_string($clean) ? $clean : $html;
    }
}
