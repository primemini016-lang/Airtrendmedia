<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $favSetting = app(\App\Services\SettingService::class)->all(); @endphp
    @if(!empty($favSetting->favicon))<link rel="icon" href="{{ Storage::url($favSetting->favicon) }}">@endif
    <title>@yield('title', 'Admin') — {{ $siteName ?? 'MiniWorkers' }}</title>
    <script>
        // Apply theme before render to avoid flash
        if (localStorage.getItem('admin-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
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
        $siteName = app(\App\Services\SettingService::class)->get('name', 'MiniWorkers');
        $admin = auth('admin')->user();
        $settingService = app(\App\Services\SettingService::class);
        $logoUrl = null;
        $s = $settingService->all();
        if (!empty($s->logo)) { $logoUrl = Storage::url($s->logo); }
    @endphp

    @if($bodyTopHtml){!! $bodyTopHtml !!}@endif

    <div class="flex min-h-screen">
        <div id="sidebar-backdrop" class="fixed inset-0 bg-black/40 z-40 lg:hidden hidden" onclick="document.getElementById('sidebar').classList.add('-translate-x-full');this.classList.add('hidden')"></div>

        <!-- Sidebar (Blue/White theme) -->
        <aside id="sidebar" class="w-64 bg-blue-700 dark:bg-slate-800 flex flex-col fixed lg:static inset-y-0 left-0 z-50 -translate-x-full lg:translate-x-0 transition-transform">
            <div class="h-16 flex items-center gap-2 px-5 border-b border-blue-800 dark:border-slate-700">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" class="h-8 w-auto" alt="{{ $siteName }}">
                @else
                    <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold">{{ strtoupper(substr($siteName ?? 'M',0,1)) }}</div>
                @endif
                <span class="font-bold text-white">{{ $siteName }}</span>
                <span class="ml-auto badge bg-white/20 text-white">Admin</span>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="nav-link-admin {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span><x-icon name="dashboard" class="w-4 h-4" /></span> Dashboard</a>

                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-200 dark:text-slate-400 uppercase tracking-wider">Users & Tasks</div>
                <a href="{{ route('admin.users') }}" class="nav-link-admin {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.show') ? 'active' : '' }}"><span><x-icon name="users" class="w-4 h-4" /></span> Users</a>
                <a href="{{ route('admin.tasks') }}" class="nav-link-admin {{ request()->routeIs('admin.tasks') ? 'active' : '' }}"><span><x-icon name="tasks" class="w-4 h-4" /></span> Tasks</a>
                <a href="{{ route('admin.gigs') }}" class="nav-link-admin {{ request()->routeIs('admin.gigs') ? 'active' : '' }}"><span><x-icon name="gigs" class="w-4 h-4" /></span> Gigs</a>
                <a href="{{ route('admin.marketplace') }}" class="nav-link-admin {{ request()->routeIs('admin.marketplace') ? 'active' : '' }}"><span><x-icon name="marketplace" class="w-4 h-4" /></span> Marketplace</a>
                <a href="{{ route('admin.categories') }}" class="nav-link-admin {{ request()->routeIs('admin.categories') ? 'active' : '' }}"><span><x-icon name="categories" class="w-4 h-4" /></span> Categories</a>

                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-200 dark:text-slate-400 uppercase tracking-wider">Finance</div>
                <a href="{{ route('admin.deposits') }}" class="nav-link-admin {{ request()->routeIs('admin.deposits') ? 'active' : '' }}"><span><x-icon name="deposits" class="w-4 h-4" /></span> Deposits</a>
                <a href="{{ route('admin.withdrawals') }}" class="nav-link-admin {{ request()->routeIs('admin.withdrawals') ? 'active' : '' }}"><span><x-icon name="withdraw" class="w-4 h-4" /></span> Withdrawals</a>
                <a href="{{ route('admin.transactions') }}" class="nav-link-admin {{ request()->routeIs('admin.transactions') ? 'active' : '' }}"><span><x-icon name="transactions" class="w-4 h-4" /></span> Transactions</a>
                <a href="{{ route('admin.currencies') }}" class="nav-link-admin {{ request()->routeIs('admin.currencies') ? 'active' : '' }}"><span><x-icon name="currencies" class="w-4 h-4" /></span> Currencies</a>
                <a href="{{ route('admin.methods') }}" class="nav-link-admin {{ request()->routeIs('admin.methods') ? 'active' : '' }}"><span><x-icon name="currencies" class="w-4 h-4" /></span> Payment Methods</a>
                <a href="{{ route('admin.payment-keys') }}" class="nav-link-admin {{ request()->routeIs('admin.payment-keys') ? 'active' : '' }}"><span><x-icon name="payment" class="w-4 h-4" /></span> Payment Keys</a>
                <a href="{{ route('admin.affiliate') }}" class="nav-link-admin {{ request()->routeIs('admin.affiliate') ? 'active' : '' }}"><span><x-icon name="affiliate" class="w-4 h-4" /></span> Affiliate</a>

                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-200 dark:text-slate-400 uppercase tracking-wider">Content & Social</div>
                <a href="{{ route('admin.complaints') }}" class="nav-link-admin {{ request()->routeIs('admin.complaints') ? 'active' : '' }}"><span><x-icon name="complaints" class="w-4 h-4" /></span> Complaints</a>
                <a href="{{ route('admin.messages') }}" class="nav-link-admin {{ request()->routeIs('admin.messages') || request()->routeIs('admin.messages.show') ? 'active' : '' }}"><span><x-icon name="messages" class="w-4 h-4" /></span> Messages</a>
                <a href="{{ route('admin.faqs') }}" class="nav-link-admin {{ request()->routeIs('admin.faqs') ? 'active' : '' }}"><span><x-icon name="faq" class="w-4 h-4" /></span> FAQs</a>
                <a href="{{ route('admin.ads') }}" class="nav-link-admin {{ request()->routeIs('admin.ads') ? 'active' : '' }}"><span><x-icon name="ads" class="w-4 h-4" /></span> Ads</a>
                <a href="{{ route('admin.blog') }}" class="nav-link-admin {{ request()->routeIs('admin.blog') || request()->routeIs('admin.blog.edit') ? 'active' : '' }}"><span><x-icon name="document-text" class="w-4 h-4" /></span> Blog</a>
                <a href="{{ route('admin.notifications') }}" class="nav-link-admin {{ request()->routeIs('admin.notifications') ? 'active' : '' }}"><span><x-icon name="notifications" class="w-4 h-4" /></span> Notifications</a>

                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-blue-200 dark:text-slate-400 uppercase tracking-wider">System</div>
                <a href="{{ route('admin.appearance') }}" class="nav-link-admin {{ request()->routeIs('admin.appearance') ? 'active' : '' }}"><span><x-icon name="appearance" class="w-4 h-4" /></span> Appearance</a>
                <a href="{{ route('admin.email-settings') }}" class="nav-link-admin {{ request()->routeIs('admin.email-settings') ? 'active' : '' }}"><span><x-icon name="email" class="w-4 h-4" /></span> Email Settings</a>
                <a href="{{ route('admin.settings') }}" class="nav-link-admin {{ request()->routeIs('admin.settings') ? 'active' : '' }}"><span><x-icon name="settings" class="w-4 h-4" /></span> Site Settings</a>
                <a href="{{ route('admin.system-update') }}" class="nav-link-admin {{ request()->routeIs('admin.system-update') ? 'active' : '' }}"><span><x-icon name="system-update" class="w-4 h-4" /></span> System Update</a>
            </nav>
            <div class="p-3 border-t border-blue-800 dark:border-slate-700">
                <div class="flex items-center gap-3 px-2 py-2 mb-2">
                    <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm">{{ strtoupper(substr($admin?->name ?? 'A',0,1)) }}</div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $admin?->name }}</p>
                        <p class="text-xs text-blue-200 dark:text-slate-400 truncate">{{ ucfirst($admin?->role ?? '') }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="nav-link-admin w-full text-red-200 dark:text-red-400 hover:bg-red-900/40"><span><x-icon name="logout" class="w-4 h-4" /></span> Logout</button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30 transition-colors">
                <div class="flex items-center gap-3">
                    <button class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300" onclick="document.getElementById('sidebar').classList.remove('-translate-x-full');document.getElementById('sidebar-backdrop').classList.remove('hidden')">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    </button>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100">@yield('heading', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    @if(session('impersonate'))
                        <a href="{{ route('stop-impersonating') }}" class="btn btn-danger text-xs"><x-icon name="x" class="w-3 h-3" /> Stop Impersonating</a>
                    @endif
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300" title="Toggle theme">
                        <svg id="theme-icon-light" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" class="hidden dark:block"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <svg id="theme-icon-dark" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" class="block dark:hidden"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                    <a href="{{ route('home') }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">View Site <x-icon name="arrow-right" class="w-3 h-3" /></a>
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
    .nav-link-admin { display:flex; align-items:center; gap:.65rem; padding:.55rem .85rem; border-radius:.6rem; color:rgba(255,255,255,.8); font-weight:500; font-size:.88rem; transition:all .12s; }
    .nav-link-admin:hover { background:rgba(255,255,255,.15); color:#fff; }
    .nav-link-admin.active { background:rgba(255,255,255,.25); color:#fff; font-weight:600; }
    .dark .nav-link-admin { color:rgba(226,232,240,.8); }
    .dark .nav-link-admin:hover { background:rgba(255,255,255,.08); color:#fff; }
    .dark .nav-link-admin.active { background:rgba(59,130,246,.3); color:#fff; }
    .dark .card { background:#1e293b; border-color:#334155; }
    .dark .input { background:#0f172a; border-color:#334155; color:#e2e8f0; }
    .dark .stat-value { color:#f1f5f9; }
    .dark .text-slate-800 { color:#f1f5f9 !important; }
    .dark table thead tr { color:#94a3b8; }
    .dark table tbody tr { border-color:#334155; }
    </style>

    @push('scripts')
    <script>
    document.getElementById('theme-toggle')?.addEventListener('click', function() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
    });
    </script>
    @endpush
    @stack('scripts')
</body>
</html>
