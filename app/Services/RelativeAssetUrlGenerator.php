<?php

namespace App\Services;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Request;

/**
 * Custom URL generator that emits root-relative asset URLs.
 *
 * Laravel's default UrlGenerator::asset() builds absolute URLs from the
 * configured APP_URL (e.g. https://airtrendmedia.com). On any other domain
 * every uploaded image (avatars, stories, ads, blog images, KYC docs, task
 * proofs) breaks and shows a blank/broken image.
 *
 * This override returns root-relative URLs (e.g. /storage/avatars/x.jpg) so
 * assets resolve correctly regardless of the host the app is served from.
 * Absolute external URLs (http/https) are passed through unchanged.
 */
class RelativeAssetUrlGenerator extends UrlGenerator
{
    /**
     * Generate a root-relative asset URL.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        if ($path === null || $path === '') {
            return '/';
        }

        // Pass through already-absolute external URLs.
        if (preg_match('#^https?://#i', (string) $path)) {
            return (string) $path;
        }

        // Data URIs and protocol-relative URLs pass through.
        if (str_starts_with((string) $path, 'data:') || str_starts_with((string) $path, '//')) {
            return (string) $path;
        }

        $path = ltrim((string) $path, '/');

        return '/' . $path;
    }

    /**
     * Generate a root-relative URL for the given path (used by route() etc.).
     *
     * We keep Laravel's default behaviour for non-asset URLs but strip the
     * forced APP_URL host so links stay relative and work on any domain.
     */
    public function to($path, $extra = [], $secure = null)
    {
        // If it's already an absolute external URL, leave it alone.
        if (preg_match('#^https?://#i', (string) $path)) {
            return parent::to($path, $extra, $secure);
        }

        // If it's already root-relative or relative, keep it that way.
        if (str_starts_with((string) $path, '/') || str_starts_with((string) $path, '#')) {
            if (is_array($extra) && ! empty($extra)) {
                $path = rtrim($path, '/') . '/' . implode('/', array_map('rawurlencode', $extra));
            }
            return (string) $path;
        }

        return parent::to($path, $extra, $secure);
    }
}
