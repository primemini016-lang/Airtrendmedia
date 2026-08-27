<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $favSetting = app(\App\Services\SettingService::class)->all(); @endphp
    <meta name="theme-color" content="{{ $favSetting->theme_color ?? '#1877F2' }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
    @if(!empty($favSetting->favicon))<link rel="icon" href="{{ Storage::url($favSetting->favicon) }}">@else<link rel="icon" href="{{ asset('images/favicon.png') }}">@endif
    <title>@yield('title', 'Airtrendmedia')</title>
    <script>
        if (localStorage.getItem('site-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    {{-- Alpine powers interactive tabs/dropdowns. A small local fallback below keeps the public home tabs usable if the CDN is unavailable. --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @php
        $customCss = \App\Models\SiteSetting::get('custom_css', '');
        $headerHtml = \App\Models\SiteSetting::get('header_html', '');
        $bodyTopHtml = \App\Models\SiteSetting::get('body_top_html', '');
        $bodyBottomHtml = \App\Models\SiteSetting::get('body_bottom_html', '');
        $footerHtml = \App\Models\SiteSetting::get('footer_html', '');
    @endphp
    @if($customCss)<style>{!! $customCss !!}</style>@endif
    @if($headerHtml){!! $headerHtml !!}@endif
    @stack('styles')
    <script>
    // Dependency-free fallback for the public three-system tabs. Alpine enhances
    // these tabs when available; this keeps the core navigation functional even
    // when a browser/network blocks the CDN.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[x-data]').forEach(function (root) {
            var data = root.getAttribute('x-data') || '';
            if (data.indexOf('tab:') === -1) return;
            var current = 'social';
            var buttons = root.querySelectorAll('[\@click]');
            var panels = root.querySelectorAll('[x-show]');
            function render() {
                panels.forEach(function (panel) {
                    var expr = panel.getAttribute('x-show') || '';
                    var m = expr.match(/tab\s*===?\s*[\"\']([^\"\']+)[\"\']/);
                    panel.style.display = m && m[1] === current ? '' : 'none';
                });
                buttons.forEach(function (button) {
                    var expr = button.getAttribute('@click') || '';
                    var m = expr.match(/tab\s*=\s*[\"\']([^\"\']+)[\"\']/);
                    if (m) {
                        button.classList.toggle('btn-primary', m[1] === current);
                        button.classList.toggle('btn-ghost', m[1] !== current);
                    }
                });
            }
            buttons.forEach(function (button) {
                button.addEventListener('click', function () {
                    var expr = button.getAttribute('@click') || '';
                    var m = expr.match(/tab\s*=\s*[\"\']([^\"\']+)[\"\']/);
                    if (m) { current = m[1]; render(); }
                });
            });
            render();
        });
    });
    </script>
</head>
<body class="min-h-screen flex flex-col bg-white dark:bg-slate-900 transition-colors">
    @include('partials.banners')
    {{-- PWA service worker registration --}}
    @php $pwaEnabled = \App\Models\SiteSetting::get('pwa_enabled', true); @endphp
    @if($pwaEnabled)
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset("sw.js") }}')
                .then(function (reg) { console.log('Airtrendmedia SW registered:', reg.scope); })
                .catch(function (err) { console.warn('SW registration failed:', err); });
        });
    }
    </script>
    @endif
    @php
        $settings = app(\App\Services\SettingService::class)->all();
        $s = $settings;
        $siteName = $settings->name ?? 'Airtrendmedia';
        $contactEmail = $settings->contact_email ?? null;
        $phone = $settings->phone ?? null;
        $address = $settings->address ?? null;
        $footerText = $settings->footer_text ?? null;
        $logoUrl = null;
        if (!empty($settings->logo)) { $logoUrl = Storage::url($settings->logo); }
        $authUser = auth('web')->user();  // CRITICAL: use 'web' guard
    @endphp

    {{-- Render ads at header top --}}
    @php $headerTopAds = \App\Models\Ad::atPosition('header_top')->get(); @endphp
    @if($headerTopAds->isNotEmpty())
        @foreach($headerTopAds as $ad)<div class="ad-container">{!! \App\Models\Ad::render($ad) !!}</div>@endforeach
    @endif

    @if($bodyTopHtml){!! $bodyTopHtml !!}@endif

    @if($settings && $settings->ann_status && $settings->ann_text)
    <div class="bg-blue-600 text-white text-center text-sm py-2 px-4">{{ $settings->ann_text }}</div>
    @endif

    <!-- Public navbar -->
    <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-40 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                @if($logoUrl)<img src="{{ $logoUrl }}" class="h-8 w-auto" alt="{{ $siteName }}">@else<div class="w-9 h-9 rounded-lg auth-gradient flex items-center justify-center text-white font-bold">{{ strtoupper(substr($siteName,0,1)) }}</div>@endif
                <span class="font-bold text-lg text-slate-800 dark:text-slate-100">{{ $siteName }}</span>
            </a>
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700">Home</a>
                <a href="{{ route('browse') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700">Browse Tasks</a>
                <a href="{{ route('gigs.browse') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700">Gigs</a>
                <a href="{{ route('marketplace') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700">Marketplace</a>
                <a href="{{ route('blog.index') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700">Blog</a>
                <a href="{{ route('faqs') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700">FAQ</a>
                <a href="{{ route('affiliate.info') }}" class="px-3 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-blue-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700">Affiliate</a>
            </nav>
            <div class="flex items-center gap-2">
                <!-- Theme Toggle -->
                <button id="theme-toggle" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300" title="Toggle theme">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="hidden dark:block"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" class="block dark:hidden"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
                @if($authUser)
                    <a href="{{ route('user.dashboard') }}" class="btn btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline hidden sm:inline-flex">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Sign Up</a>
                @endif
            </div>
        </div>
    </header>

    {{-- Ads below header --}}
    @php $headerBottomAds = \App\Models\Ad::atPosition('header_bottom')->get(); @endphp
    @if($headerBottomAds->isNotEmpty())
        <div class="max-w-7xl mx-auto px-4 py-2">@foreach($headerBottomAds as $ad)<div class="ad-container mb-2">{!! \App\Models\Ad::render($ad) !!}</div>@endforeach</div>
    @endif

    <main class="flex-1 dark:text-slate-200">
        @include('partials.alerts')
        @yield('content')
    </main>

    @if($bodyBottomHtml){!! $bodyBottomHtml !!}@endif

    {{-- Ads above footer --}}
    @php $footerTopAds = \App\Models\Ad::atPosition('footer_top')->get(); @endphp
    @if($footerTopAds->isNotEmpty())
        <div class="max-w-7xl mx-auto px-4 py-2">@foreach($footerTopAds as $ad)<div class="ad-container mb-2">{!! \App\Models\Ad::render($ad) !!}</div>@endforeach</div>
    @endif

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 grid md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    @if($logoUrl)<img src="{{ $logoUrl }}" class="h-8 w-auto" alt="{{ $siteName }}">@else<div class="w-9 h-9 rounded-lg auth-gradient flex items-center justify-center text-white font-bold">{{ strtoupper(substr($siteName,0,1)) }}</div>@endif
                    <span class="font-bold text-lg text-white">{{ $siteName }}</span>
                </div>
                <p class="text-sm text-slate-400">{{ $footerText ?? 'Complete small tasks and earn money, or post jobs and get them done by skilled workers worldwide.' }}</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm">Marketplace</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('browse') }}" class="hover:text-white">Browse Tasks</a></li>
                    <li><a href="{{ route('gigs.browse') }}" class="hover:text-white">Gigs</a></li>
                    <li><a href="{{ route('marketplace') }}" class="hover:text-white">Marketplace</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Become a Worker</a></li>
                    <li><a href="{{ route('affiliate.info') }}" class="hover:text-white">Affiliate Program</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white">Blog</a></li>
                    <li><a href="{{ route('ptc.index') }}" class="hover:text-white">PTC Ads</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm">Support</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('faqs') }}" class="hover:text-white">FAQs</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                    <li><a href="{{ route('terms') }}" class="hover:text-white">Terms of Service</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm">Contact</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    @if(!empty($contactEmail))<li>{{ $contactEmail }}</li>@endif
                    @if(!empty($phone))<li>{{ $phone }}</li>@endif
                    @if(!empty($address))<li>{{ $address }}</li>@endif
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 py-4 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
        </div>
    </footer>

    @if($footerHtml){!! $footerHtml !!}@endif

    <style>
    .dark .bg-white { background:#1e293b !important; }
    .dark .text-slate-800 { color:#f1f5f9 !important; }
    .dark .text-slate-600 { color:#cbd5e1 !important; }
    .dark .border-slate-200 { border-color:#334155 !important; }
    .dark .card { background:#1e293b; border-color:#334155; }
    .dark .input { background:#0f172a; border-color:#334155; color:#e2e8f0; }
    .ad-container { text-align: center; }
    </style>

    @push('scripts')
    <script>
    document.getElementById('theme-toggle')?.addEventListener('click', function() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('site-theme', isDark ? 'dark' : 'light');
    });
    </script>
    @endpush
    @stack('scripts')
</body>
</html>
