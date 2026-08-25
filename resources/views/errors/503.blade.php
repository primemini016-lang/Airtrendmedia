<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance | {{ config('app.name', 'MiniWorkers') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-white flex items-center justify-center px-4">
    <div class="max-w-lg w-full text-center">
        <div class="mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-3xl bg-gradient-to-br from-blue-600 to-blue-800 text-white text-5xl font-bold shadow-lg mb-6">503</div>
            <h1 class="text-3xl font-bold text-slate-800 mb-3">Under Maintenance</h1>
            <p class="text-slate-500 mb-2">We're performing some maintenance to improve your experience.</p>
            <p class="text-sm text-slate-400 mb-8">We'll be back shortly. Thank you for your patience.</p>
        </div>
        <a href="/" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors shadow-md">
            Back to Home
        </a>
    </div>
</body>
</html>
