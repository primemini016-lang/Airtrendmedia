<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | {{ config('app.name', 'MiniWorkers') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-white flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-blue-600 to-blue-800 text-white text-5xl font-bold shadow-lg mb-6">404</div>
            <h1 class="text-3xl font-bold text-slate-800 mb-3">Page Not Found</h1>
            <p class="text-slate-500 mb-2">The page you're looking for doesn't exist or has been moved.</p>
            <p class="text-sm text-slate-400 mb-8">The URL may be incorrect or the page may have been removed.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors shadow-md">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Back to Home
            </a>
            <a href="javascript:history.back()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                Go Back
            </a>
        </div>
        <div class="mt-12 text-sm text-slate-400">
            <p>Need help? <a href="/contact" class="text-blue-600 hover:underline">Contact us</a></p>
        </div>
    </div>
</body>
</html>
