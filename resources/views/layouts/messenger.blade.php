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
    @if(!empty($favSetting->favicon))<link rel="icon" href="{{ storage_asset($favSetting->favicon) }}">@else<link rel="icon" href="{{ asset('images/favicon.png') }}">@endif
    <title>@yield('title', 'Messenger') — {{ $siteName ?? 'Airtrendmedia' }}</title>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ asset("sw.js") }}')
                    .catch(function (err) { console.warn('SW registration failed:', err); });
            });
        }
    </script>
    <script>
        if (localStorage.getItem('user-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/messenger.css') }}">
    {{-- Alpine.js — required for chat dropdowns, emoji picker, typing indicator --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    {{-- Airtrendmedia messenger design system variables --}}
    <style>
        :root {
            --fb-blue: #1877F2;
            --fb-blue-hover: #166FE5;
            --fb-bg: #F0F2F5;
            --fb-card: #FFFFFF;
            --fb-text: #050505;
            --fb-text-secondary: #65676B;
            --fb-border: #CED0D4;
            --fb-hover: #E4E6EB;
            --fb-green: #42B72A;
        }
        .dark {
            --fb-bg: #18191A;
            --fb-card: #242526;
            --fb-text: #E4E6EB;
            --fb-text-secondary: #B0B3B8;
            --fb-border: #3E4042;
            --fb-hover: #3A3B3C;
        }
        body.fb-body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--fb-bg);
            color: var(--fb-text);
        }
        .fb-card { background: var(--fb-card); border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        [x-cloak] { display: none !important; }
    </style>
    @php
        $customCss = \App\Models\SiteSetting::get('custom_css', '');
        $headerHtml = \App\Models\SiteSetting::get('header_html', '');
        $bodyTopHtml = \App\Models\SiteSetting::get('body_top_html', '');
        $bodyBottomHtml = \App\Models\SiteSetting::get('body_bottom_html', '');
        $footerHtml = \App\Models\SiteSetting::get('footer_html', '');
    @endphp
    @if($customCss)<style>{!! site_injected_html($customCss) !!}</style>@endif
    @if($headerHtml){!! site_injected_html($headerHtml) !!}@endif
    @stack('styles')
</head>
<body class="fb-body min-h-screen transition-colors">
    @php
        $settings = app(\App\Services\SettingService::class)->all();
        $siteName = $settings->name ?? 'Airtrendmedia';
        $user = auth('web')->user();
        $logoUrl = null;
        if (!empty($settings->logo)) { $logoUrl = storage_asset($settings->logo); }
    @endphp

    @if($bodyTopHtml){!! site_injected_html($bodyTopHtml) !!}@endif

    {{-- Messenger top bar --}}
    <header class="fb-msg-topbar">
        <div class="fb-msg-topbar-inner">
            <div class="flex items-center gap-3">
                <a href="{{ route('user.dashboard') }}" class="fb-msg-logo-link" title="Back to dashboard">
                    @if($logoUrl)<img src="{{ $logoUrl }}" class="h-8 w-auto" alt="{{ $siteName }}">@else<div class="w-9 h-9 rounded-lg auth-gradient flex items-center justify-center text-white font-bold">{{ strtoupper(substr($siteName ?? 'M',0,1)) }}</div>@endif
                    <span class="font-bold fb-text">{{ $siteName }}</span>
                </a>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('messenger.chat') }}" class="fb-nav-icon-btn {{ request()->routeIs('messenger.*') ? 'fb-msg-active' : '' }}" title="Messenger">
                    <x-icon name="messenger" class="w-5 h-5" />
                    <span id="msg-badge" class="fb-badge hidden">0</span>
                </a>
                {{-- Notification bell with unread badge --}}
                <div class="relative">
                    <a href="{{ route('user.notifications') }}" class="fb-nav-icon-btn" title="Notifications">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span id="notif-badge" class="fb-badge hidden">0</span>
                    </a>
                </div>
                {{-- Theme toggle --}}
                <button id="theme-toggle" class="fb-nav-icon-btn" title="Toggle theme">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" class="hidden dark:block"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" class="block dark:hidden"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
                <div class="hidden sm:block px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-semibold text-sm">{{ money((float)($user?->balance ?? 0)) }}</div>
                @if($user)
                <a href="{{ route('user.profile') }}" class="flex items-center gap-2">
                    @if($user->image)<img src="{{ storage_asset($user->image) }}" class="w-8 h-8 rounded-full object-cover">@else<div class="w-8 h-8 rounded-full auth-gradient flex items-center justify-center text-white font-bold text-sm">{{ strtoupper(substr($user->name ?? 'U',0,1)) }}</div>@endif
                </a>
                @endif
            </div>
        </div>
    </header>

    <main class="fb-msg-main">
        @include('partials.alerts')
        @yield('content')
    </main>

    @if($bodyBottomHtml){!! site_injected_html($bodyBottomHtml) !!}@endif
    @if($footerHtml){!! site_injected_html($footerHtml) !!}@endif

    <style>
    .dark .fb-card { background:#242526; }
    </style>

    @push('scripts')
    <script>
    let __lastMessengerCount=-1;
    function atmMsgPing(){try{const C=window.AudioContext||window.webkitAudioContext;if(!C)return;const c=new C(),o=c.createOscillator(),g=c.createGain();o.frequency.value=880;g.gain.setValueAtTime(.0001,c.currentTime);g.gain.exponentialRampToValueAtTime(.05,c.currentTime+.01);g.gain.exponentialRampToValueAtTime(.0001,c.currentTime+.18);o.connect(g);g.connect(c.destination);o.start();o.stop(c.currentTime+.2);}catch(e){}}
    function updateMessageCount(){ fetch('{{ route('user.messages.unread-count') }}',{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}).then(r=>r.json()).then(d=>{const b=document.getElementById('msg-badge'); if(!b)return; const n=Number(d.count||0); b.textContent=n>99?'99+':n; b.classList.toggle('hidden',!(n>0)); if(__lastMessengerCount>=0&&n>__lastMessengerCount)atmMsgPing(); __lastMessengerCount=n;}).catch(()=>{}); }
    updateMessageCount(); setInterval(updateMessageCount,15000);

    // Theme toggle
    document.getElementById('theme-toggle')?.addEventListener('click', function() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('user-theme', isDark ? 'dark' : 'light');
    });
    // Notification count polling
    function updateNotifCount() {
        fetch('{{ route("user.notifications.unread-count") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notif-badge');
                if (badge && data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.classList.remove('hidden');
                } else if (badge) {
                    badge.classList.add('hidden');
                }
            })
            .catch(() => {});
    }
    updateNotifCount();
    setInterval(updateNotifCount, 30000);
    </script>
    @endpush
    @stack('scripts')
</body>
</html>
