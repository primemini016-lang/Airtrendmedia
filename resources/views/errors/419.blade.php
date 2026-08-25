<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired | {{ config('app.name', 'MiniWorkers') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-white flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-blue-600 to-blue-800 text-white text-5xl font-bold shadow-lg mb-6">419</div>
            <h1 class="text-3xl font-bold text-slate-800 mb-3">Session Expired</h1>
            <p class="text-slate-500 mb-2">Your session has expired due to inactivity.</p>
            <p class="text-sm text-slate-400 mb-8">Please refresh the page and try again.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors shadow-md">
                Back to Home
            </a>
            <button onclick="location.reload()" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border border-slate-300 text-slate-700 font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                Refresh Page
            </button>
        </div>
    </div>
</body>
</html>
