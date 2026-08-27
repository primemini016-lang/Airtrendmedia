<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Installation') — Airtrendmedia</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen flex items-center justify-center p-4 auth-gradient">
    <div class="w-full max-w-2xl">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-lg mb-3">
                <span class="text-3xl font-extrabold gradient-text">M</span>
            </div>
            <h1 class="text-2xl font-bold text-white">@yield('auth-heading', 'Airtrendmedia Installer')</h1>
            <p class="text-blue-100 text-sm mt-1">@yield('auth-subtitle', 'Setup wizard — get your marketplace running in minutes')</p>
        </div>
        @include('partials.alerts')
        <div class="card">
            <div class="card-body">
                @yield('content')
            </div>
        </div>
        <p class="text-center text-blue-100 text-xs mt-6">@yield('auth-footer', 'Airtrendmedia v2.0') · PHP {{ PHP_VERSION }}</p>
    </div>
    @stack('scripts')
</body>
</html>
