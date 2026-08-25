<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $favSetting = app(\App\Services\SettingService::class)->all(); @endphp
    @if(!empty($favSetting->favicon))<link rel="icon" href="{{ Storage::url($favSetting->favicon) }}">@endif
    <title>@yield('title', 'Dashboard') — {{ $siteName ?? 'MiniWorkers' }}</title>
    <script>
        if (localStorage.getItem('user-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
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
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-900 transition-colors">
    @php
        $settings = app(\App\Services\SettingService::class)->all();
        $siteName = $settings->name ?? 'MiniWorkers';
        $user = auth('web')->user();  // CRITICAL: use 'web' guard, not default JWT guard
        $logoUrl = null;
        if (!empty($settings->logo)) { $logoUrl = Storage::url($settings->logo); }
    @endphp

    @if($bodyTopHtml){!! $bodyTopHtml !!}@endif

    <div class="flex min-h-screen">
        <!-- Sidebar backdrop (mobile) -->
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 lg:hidden hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full');this.classList.add('hidden')"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col fixed lg:static inset-y-0 left-0 z-50 -translate-x-full lg:translate-x-0 transition-transform">
            <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-200 dark:border-slate-700">
                @if($logoUrl)<img src="{{ $logoUrl }}" class="h-8 w-auto" alt="{{ $siteName }}">@else<div class="w-9 h-9 rounded-lg auth-gradient flex items-center justify-center text-white font-bold">{{ strtoupper(substr($siteName ?? 'M',0,1)) }}</div>@endif
                <span class="font-bold text-slate-800 dark:text-slate-100">{{ $siteName }}</span>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <a href="{{ route('user.dashboard') }}" class="nav-link {{ request()->routeIs('user.dashboard') ? 'active' : '' }}"><span>📊</span> Dashboard</a>
                <a href="{{ route('user.tasks') }}" class="nav-link {{ request()->routeIs('user.tasks') || request()->routeIs('user.task') ? 'active' : '' }}"><span>🔍</span> Browse Tasks</a>
                <a href="{{ route('user.bookings') }}" class="nav-link {{ request()->routeIs('user.bookings') ? 'active' : '' }}"><span>📝</span> My Bookings</a>
                <a href="{{ route('user.offers') }}" class="nav-link {{ request()->routeIs('user.offers') || request()->routeIs('user.task.proofs') || request()->routeIs('user.task.create') ? 'active' : '' }}"><span>💼</span> My Offers</a>
                <a href="{{ route('user.notifications') }}" class="nav-link {{ request()->routeIs('user.notifications') ? 'active' : '' }}"><span>🔔</span> Notifications</a>
                <a href="{{ route('user.wallet') }}" class="nav-link {{ request()->routeIs('user.wallet') ? 'active' : '' }}"><span>💵</span> Wallet</a>
                <a href="{{ route('user.withdraw') }}" class="nav-link {{ request()->routeIs('user.withdraw') ? 'active' : '' }}"><span>🏦</span> Withdraw</a>
                <a href="{{ route('user.transactions') }}" class="nav-link {{ request()->routeIs('user.transactions') ? 'active' : '' }}"><span>🧾</span> Transactions</a>
                <a href="{{ route('user.affiliate') }}" class="nav-link {{ request()->routeIs('user.affiliate') ? 'active' : '' }}"><span>🤝</span> Affiliate</a>
                <a href="{{ route('user.messages') }}" class="nav-link {{ request()->routeIs('user.messages') ? 'active' : '' }}"><span>💬</span> Support</a>
                <a href="{{ route('user.profile') }}" class="nav-link {{ request()->routeIs('user.profile') ? 'active' : '' }}"><span>👤</span> Profile</a>
            </nav>
            <div class="p-3 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3 px-2 py-2 mb-2">
                    @if($user && $user->image)<img src="{{ asset('storage/'.$user->image) }}" class="w-9 h-9 rounded-full object-cover">@else<div class="w-9 h-9 rounded-full auth-gradient flex items-center justify-center text-white font-bold text-sm">{{ strtoupper(substr($user?->name ?? 'U',0,1)) }}</div>@endif
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $user?->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ number_format((float)($user?->balance ?? 0),2) }} USD</p>
                    </div>
                </div>
                <a href="{{ route('user.affiliate') }}" class="block text-xs text-center text-blue-600 dark:text-blue-400 hover:underline mb-2">Referral code: {{ $user?->referral_code }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-link w-full text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30"><span>⏻</span> Logout</button>
                </form>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 transition-colors">
                <div class="flex items-center gap-3">
                    <button class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300" onclick="document.getElementById('sidebar').classList.remove('-translate-x-full');document.getElementById('sidebar-backdrop').classList.remove('hidden')">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    </button>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100">@yield('heading', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    @if(session('impersonate'))
                        <a href="{{ route('stop-impersonating') }}" class="btn btn-danger text-xs">⏹ Stop Impersonating</a>
                    @endif
                    <div class="hidden sm:block px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-semibold text-sm">Balance: {{ number_format((float)($user?->balance ?? 0),2) }} USD</div>
                    <!-- Notification Bell -->
                    <div class="relative">
                        <a href="{{ route('user.notifications') }}" class="relative p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300" title="Notifications">
                            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            <span id="notif-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                        </a>
                    </div>
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300" title="Toggle theme">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" class="hidden dark:block"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" class="block dark:hidden"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                    @if($user && !$user->is_active)
                    <a href="{{ route('user.activate') }}" class="btn btn-primary text-xs">Activate Account</a>
                    @endif
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto dark:text-slate-200">
                @include('partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>

    @if($bodyBottomHtml){!! $bodyBottomHtml !!}@endif
    @if($footerHtml){!! $footerHtml !!}@endif

    <style>
    .dark .card { background:#1e293b; border-color:#334155; }
    .dark .input { background:#0f172a; border-color:#334155; color:#e2e8f0; }
    .dark .text-slate-800 { color:#f1f5f9 !important; }
    .dark .bg-white { background:#1e293b !important; }
    .dark .border-slate-200 { border-color:#334155 !important; }
    </style>

    @push('scripts')
    <script>
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
    setInterval(updateNotifCount, 30000); // Poll every 30 seconds
    </script>
    @endpush
    @stack('scripts')
</body>
</html>
