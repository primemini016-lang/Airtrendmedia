@php
/**
 * Airtrendmedia — branded "We Couldn't Process Your Request" error screen.
 *
 * This is the single shared template used by the exception handler for ALL
 * uncaught exceptions (500 / generic) AND by the 404 / 419 / 503 pages.
 *
 * It only ever shows when a destination does not exist or could not be
 * fetched — it NEVER exposes raw exception details to end users (those are
 * only written to the log so the admin can review them).
 *
 * The heading, subtext, accent color and logo are ALL admin-managed from
 * Admin Panel → Appearance → "Error Screen". If the admin has not set them
 * yet (or the DB is unreachable), sensible defaults are used so the page
 * still renders cleanly even during a deep failure.
 */

use App\Models\SiteSetting;

// Defaults (used when DB/setting is unavailable — keeps the page resilient).
$heading    = 'We Couldn\'t Process Your Request.';
$subtext    = 'The page you\'re looking for may have moved, is temporarily unavailable, or couldn\'t be loaded right now. Please try again in a moment.';
$accent     = '#2563eb';   // website primary blue
$logoUrl    = asset('images/logo.png');

// Try to pull admin-managed content. Everything is wrapped in try/catch so a
// broken DB never breaks the error page itself.
try {
    $h = SiteSetting::get('error_heading');
    if (!empty($h)) { $heading = $h; }
    $st = SiteSetting::get('error_subtext');
    if (!empty($st)) { $subtext = $st; }
    $ac = SiteSetting::get('error_accent_color');
    if (!empty($ac)) { $accent = $ac; }
    $lg = SiteSetting::get('error_logo');
    if (!empty($lg)) { $logoUrl = Storage::url($lg); }
} catch (\Throwable $e) {
    // Ignore — use defaults.
}

$siteName = config('app.name', 'Airtrendmedia');
$homeUrl  = url('/');
$contactUrl = url('/contact');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="{{ $accent }}">
    <title>{{ $heading }} | {{ $siteName }}</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <style>
        :root { --brand: {{ $accent }}; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(circle at 30% 20%, rgba(37,99,235,0.07), transparent 60%),
                        linear-gradient(160deg, #f8fafc 0%, #eef2ff 60%, #e0e7ff 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { width: 100%; max-width: 540px; text-align: center; }
        .logo {
            width: 88px; height: 88px;
            margin: 0 auto 1.75rem;
            display: block;
            object-fit: contain;
            filter: drop-shadow(0 10px 24px rgba(37,99,235,0.25));
        }
        h1 {
            font-size: clamp(1.6rem, 4vw, 2.1rem);
            font-weight: 800; line-height: 1.25; color: #0f172a;
            margin-bottom: .85rem;
        }
        p.lead { font-size: 1.05rem; color: #475569; line-height: 1.6; margin-bottom: .35rem; }
        p.sub  { font-size: .9rem;  color: #94a3b8; margin-bottom: 2rem; }
        .actions { display: flex; flex-direction: column; gap: .75rem; align-items: center; }
        @media (min-width: 480px) { .actions { flex-direction: row; justify-content: center; } }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
            padding: .8rem 1.6rem; border-radius: 12px; font-weight: 600; font-size: .95rem;
            text-decoration: none; cursor: pointer; border: 1px solid transparent;
            transition: background .15s ease, transform .08s ease, box-shadow .15s ease;
        }
        .btn:active { transform: translateY(1px); }
        .btn-primary { background: var(--brand); color: #fff; box-shadow: 0 8px 18px rgba(37,99,235,0.28); }
        .btn-primary:hover { filter: brightness(1.08); }
        .btn-secondary { background: #fff; color: #334155; border-color: #e2e8f0; }
        .btn-secondary:hover { background: #f8fafc; }
        .footer { margin-top: 2.5rem; font-size: .85rem; color: #94a3b8; }
        .footer a { color: var(--brand); text-decoration: none; font-weight: 600; }
        .footer a:hover { text-decoration: underline; }
        svg { width: 18px; height: 18px; flex-shrink: 0; }
    </style>
</head>
<body>
    <div class="wrap">
        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="logo">

        <h1>{{ $heading }}</h1>
        <p class="lead">{{ $subtext }}</p>

        <div class="actions">
            <a href="{{ $homeUrl }}" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Back to Home
            </a>
            <button onclick="location.reload()" class="btn btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Try Again
            </button>
        </div>

        <div class="footer">
            Need help? <a href="{{ $contactUrl }}">Contact us</a>
        </div>
    </div>
</body>
</html>
