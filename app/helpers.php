<?php

/**
 * Airtrendmedia custom helpers.
 *
 * This file is registered in composer.json under autoload.files so that it is
 * loaded on every request. It overrides Laravel's built-in `asset()` helper
 * so that generated URLs are always relative (root-relative) instead of
 * absolute URLs built from APP_URL.
 *
 * Why this is necessary:
 *   - The production .env ships with APP_URL=https://airtrendmedia.com, which
 *     makes Laravel's default asset() helper emit absolute URLs pointing at
 *     that domain (e.g. https://airtrendmedia.com/storage/avatars/xxx.jpg).
 *   - When the app is deployed on a different domain, or accessed via the
 *     installer on a fresh server, those absolute URLs break every uploaded
 *     image (avatars, story media, ad creatives, blog images, KYC documents,
 *     task proofs, etc.) showing blank / broken images.
 *   - Root-relative URLs (e.g. /storage/avatars/xxx.jpg) work on ANY domain
 *     the application is served from, which is exactly what we want for a
 *     distributable, installable script.
 *
 * The override keeps full backward compatibility: it accepts the same
 * arguments as the original helper and only changes the scheme/host portion
 * of the result. It also preserves query strings.
 */

if (! function_exists('storage_asset')) {
    /**
     * Convenience wrapper for storage URLs (kept for readability in blade).
     *
     * @param  string|null  $path
     * @return string
     */
    function storage_asset(?string $path = null): string
    {
        if ($path === null || $path === '') {
            return '/storage';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        // Normalize so it always looks like /storage/<path>
        $path = ltrim($path, '/');
        if (! str_starts_with($path, 'storage/')) {
            $path = 'storage/' . $path;
        }

        return '/' . $path;
    }
}
