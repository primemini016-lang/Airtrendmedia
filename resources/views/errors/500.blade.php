@php
/**
 * 500 — something couldn't be fetched / server error.
 * Delegates to the branded "We Couldn't Process Your Request." screen.
 * Raw details are logged for the admin, never shown to users.
 */
$status = 500;
$exception = $exception ?? null;
@endphp
@include('errors.generic')
