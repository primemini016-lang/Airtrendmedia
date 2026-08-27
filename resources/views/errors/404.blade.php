@php
/**
 * 404 — destination does not exist.
 * Delegates to the branded "We Couldn't Process Your Request." screen,
 * which uses the site logo + admin-managed content.
 */
$status = 404;
$exception = $exception ?? null;
@endphp
@include('errors.generic')
