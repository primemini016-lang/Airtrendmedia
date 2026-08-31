@php
/**
 * 419 — session expired / CSRF token mismatch.
 * Delegates to the branded error screen so the experience stays consistent.
 */
$status = 419;
$exception = $exception ?? null;
@endphp
@include('errors.generic')
