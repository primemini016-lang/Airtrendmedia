<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $favSetting = app(\App\Services\SettingService::class)->all(); @endphp
    @if(!empty($favSetting->favicon))<link rel="icon" href="{{ storage_asset($favSetting->favicon) }}">@endif
    <title>@yield('title', 'Admin') — {{ $siteName ?? 'Airtrendmedia' }}</title>
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
            theme: { extend: { colors: { brand: { 50:'#eff6ff',100:'#dbeafe',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' } } } }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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
<body class="min-h-screen bg-slate-50 dark:bg-slate-950 transition-colors">
    @php
        $siteName = app(\App\Services\SettingService::class)->get('name', 'Airtrendmedia');
        $admin = auth('admin')->user();
        $settingService = app(\App\Services\SettingService::class);
        $logoUrl = null;
        $s = $settingService->all();
        if (!empty($s->logo)) { $logoUrl = storage_asset($s->logo); }
    @endphp

    @if($bodyTopHtml){!! site_injected_html($bodyTopHtml) !!}@endif

    {{-- Mobile backdrop --}}
    <div id="admin-sidebar-backdrop" class="admin-sidebar-backdrop fixed inset-0 bg-black/50 z-40 hidden opacity-0 transition-opacity duration-200"></div>

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside id="admin-sidebar" class="admin-sidebar w-64 flex flex-col fixed lg:static inset-y-0 left-0 z-50 -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-out">
            {{-- Logo / brand --}}
            <div class="admin-brand">
                <div class="flex items-center gap-2.5">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" class="h-9 w-9 rounded-lg object-cover" alt="{{ $siteName }}">
                    @else
                        <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">{{ strtoupper(substr($siteName ?? 'A',0,1)) }}</div>
                    @endif
                    <div class="min-w-0">
                        <div class="font-bold text-white text-sm truncate leading-tight">{{ $siteName }}</div>
                        <div class="text-blue-200/70 text-[10px] uppercase tracking-wider">Admin Panel</div>
                    </div>
                </div>
                <button onclick="AdminMenu.close()" class="lg:hidden text-white/60 hover:text-white p-1" title="Close menu">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Nav --}}
            <nav class="admin-nav flex-1 overflow-y-auto px-3 pb-4">
                <a href="{{ route('admin.dashboard') }}" class="nav-link-admin {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span><x-icon name="dashboard" class="w-[18px] h-[18px]" /></span> Dashboard</a>

                <div class="nav-section">Users & Tasks</div>
                <a href="{{ route('admin.users') }}" class="nav-link-admin {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.show') || request()->routeIs('admin.users.login-as') ? 'active' : '' }}"><span><x-icon name="users" class="w-[18px] h-[18px]" /></span> Users</a>
                <a href="{{ route('admin.tasks') }}" class="nav-link-admin {{ request()->routeIs('admin.tasks') ? 'active' : '' }}"><span><x-icon name="tasks" class="w-[18px] h-[18px]" /></span> Tasks</a>
                <a href="{{ route('admin.gigs') }}" class="nav-link-admin {{ request()->routeIs('admin.gigs') ? 'active' : '' }}"><span><x-icon name="gigs" class="w-[18px] h-[18px]" /></span> Gigs</a>
                <a href="{{ route('admin.marketplace') }}" class="nav-link-admin {{ request()->routeIs('admin.marketplace') ? 'active' : '' }}"><span><x-icon name="marketplace" class="w-[18px] h-[18px]" /></span> Marketplace</a>
                <a href="{{ route('admin.categories') }}" class="nav-link-admin {{ request()->routeIs('admin.categories') ? 'active' : '' }}"><span><x-icon name="categories" class="w-[18px] h-[18px]" /></span> Categories</a>

                <div class="nav-section">Finance</div>
                <a href="{{ route('admin.deposits') }}" class="nav-link-admin {{ request()->routeIs('admin.deposits') ? 'active' : '' }}"><span><x-icon name="deposits" class="w-[18px] h-[18px]" /></span> Deposits</a>
                <a href="{{ route('admin.withdrawals') }}" class="nav-link-admin {{ request()->routeIs('admin.withdrawals') ? 'active' : '' }}"><span><x-icon name="withdraw" class="w-[18px] h-[18px]" /></span> Withdrawals</a>
                <a href="{{ route('admin.transactions') }}" class="nav-link-admin {{ request()->routeIs('admin.transactions') ? 'active' : '' }}"><span><x-icon name="transactions" class="w-[18px] h-[18px]" /></span> Transactions</a>
                <a href="{{ route('admin.currencies') }}" class="nav-link-admin {{ request()->routeIs('admin.currencies') ? 'active' : '' }}"><span><x-icon name="currencies" class="w-[18px] h-[18px]" /></span> Currencies</a>
                <a href="{{ route('admin.methods') }}" class="nav-link-admin {{ request()->routeIs('admin.methods') ? 'active' : '' }}"><span><x-icon name="currencies" class="w-[18px] h-[18px]" /></span> Payment Methods</a>
                <a href="{{ route('admin.payment-keys') }}" class="nav-link-admin {{ request()->routeIs('admin.payment-keys') ? 'active' : '' }}"><span><x-icon name="payment" class="w-[18px] h-[18px]" /></span> Payment Keys</a>
                <a href="{{ route('admin.affiliate') }}" class="nav-link-admin {{ request()->routeIs('admin.affiliate') ? 'active' : '' }}"><span><x-icon name="affiliate" class="w-[18px] h-[18px]" /></span> Affiliate</a>

                <div class="nav-section">Content & Social</div>
                <a href="{{ route('admin.complaints') }}" class="nav-link-admin {{ request()->routeIs('admin.complaints') ? 'active' : '' }}"><span><x-icon name="complaints" class="w-[18px] h-[18px]" /></span> Complaints</a>
                <a href="{{ route('admin.messages') }}" class="nav-link-admin {{ request()->routeIs('admin.messages') || request()->routeIs('admin.messages.show') ? 'active' : '' }}"><span><x-icon name="messages" class="w-[18px] h-[18px]" /></span> Messages</a>
                <a href="{{ route('admin.faqs') }}" class="nav-link-admin {{ request()->routeIs('admin.faqs') ? 'active' : '' }}"><span><x-icon name="faq" class="w-[18px] h-[18px]" /></span> FAQs</a>
                <a href="{{ route('admin.ads') }}" class="nav-link-admin {{ request()->routeIs('admin.ads') ? 'active' : '' }}"><span><x-icon name="ads" class="w-[18px] h-[18px]" /></span> Ads</a>
                <a href="{{ route('admin.blog') }}" class="nav-link-admin {{ request()->routeIs('admin.blog') || request()->routeIs('admin.blog.edit') ? 'active' : '' }}"><span><x-icon name="document-text" class="w-[18px] h-[18px]" /></span> Blog</a>
                <a href="{{ route('admin.notifications') }}" class="nav-link-admin {{ request()->routeIs('admin.notifications') ? 'active' : '' }}"><span><x-icon name="notifications" class="w-[18px] h-[18px]" /></span> Notifications</a>

                <div class="nav-section">Airtrendmedia — God Mode</div>
<a href="{{ route('admin.kyc') }}" class="nav-link-admin {{ request()->routeIs('admin.kyc') || request()->routeIs('admin.kyc.show') ? 'active' : '' }}"><span><x-icon name="admin" class="w-[18px] h-[18px]" /></span> KYC Verification</a>
                <a href="{{ route('admin.verification') }}" class="nav-link-admin {{ request()->routeIs('admin.verification') || request()->routeIs('admin.verification.show') ? 'active' : '' }}"><span><x-icon name="star" class="w-[18px] h-[18px]" /></span> Blue Badges</a>
                <a href="{{ route('admin.sponsored-ads') }}" class="nav-link-admin {{ request()->routeIs('admin.sponsored-ads') ? 'active' : '' }}"><span><x-icon name="ads" class="w-[18px] h-[18px]" /></span> Sponsored Ads</a>
                <a href="{{ route('admin.anti-cheat') }}" class="nav-link-admin {{ request()->routeIs('admin.anti-cheat') ? 'active' : '' }}"><span><x-icon name="complaints" class="w-[18px] h-[18px]" /></span> Anti-Cheat</a>
                <a href="{{ route('admin.push-settings') }}" class="nav-link-admin {{ request()->routeIs('admin.push-settings') || request()->routeIs('admin.push-settings.logs') ? 'active' : '' }}"><span><x-icon name="notifications" class="w-[18px] h-[18px]" /></span> Push / Firebase</a>
                <a href="{{ route('admin.pwa-settings') }}" class="nav-link-admin {{ request()->routeIs('admin.pwa-settings') ? 'active' : '' }}"><span><x-icon name="appearance" class="w-[18px] h-[18px]" /></span> PWA Settings</a>
                <a href="{{ route('admin.banner-settings') }}" class="nav-link-admin {{ request()->routeIs('admin.banner-settings') ? 'active' : '' }}"><span><x-icon name="ads" class="w-[18px] h-[18px]" /></span> Banner & Popup</a>

                <div class="nav-section">System</div>
                <a href="{{ route('admin.appearance') }}" class="nav-link-admin {{ request()->routeIs('admin.appearance') ? 'active' : '' }}"><span><x-icon name="appearance" class="w-[18px] h-[18px]" /></span> Appearance</a>
                <a href="{{ route('admin.email-settings') }}" class="nav-link-admin {{ request()->routeIs('admin.email-settings') ? 'active' : '' }}"><span><x-icon name="email" class="w-[18px] h-[18px]" /></span> Email Settings</a>
                <a href="{{ route('admin.settings') }}" class="nav-link-admin {{ request()->routeIs('admin.settings') ? 'active' : '' }}"><span><x-icon name="settings" class="w-[18px] h-[18px]" /></span> Site Settings</a>
                <a href="{{ route('admin.system-update') }}" class="nav-link-admin {{ request()->routeIs('admin.system-update') ? 'active' : '' }}"><span><x-icon name="system-update" class="w-[18px] h-[18px]" /></span> System Update</a>
            </nav>

            {{-- User card at bottom --}}
            <div class="admin-user-card">
                <div class="flex items-center gap-3 px-2 py-2.5 mb-2">
                    <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ strtoupper(substr($admin?->name ?? 'A',0,1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-white truncate leading-tight">{{ $admin?->name }}</p>
                        <p class="text-xs text-blue-200/60 truncate">{{ ucfirst($admin?->role ?? 'Super Admin') }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="nav-link-admin w-full text-red-200 hover:bg-red-900/40"><span><x-icon name="logout" class="w-[18px] h-[18px]" /></span> Logout</button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top bar --}}
            <header class="admin-topbar h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button onclick="AdminMenu.open()" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300" title="Open menu">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                    </button>
                    <h1 class="text-lg font-bold text-slate-800 dark:text-slate-100 hidden sm:block">@yield('heading', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    @if(session('impersonate'))
                        <a href="{{ route('stop-impersonating') }}" class="btn btn-danger text-xs"><x-icon name="x" class="w-3 h-3" /> Stop Impersonating</a>
                    @endif
                    {{-- Theme Toggle --}}
                    <button id="theme-toggle" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300" title="Toggle theme">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" class="hidden dark:block"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" class="block dark:hidden"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                    <a href="{{ route('home') }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1 font-medium">View Site <x-icon name="arrow-right" class="w-3 h-3" /></a>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto dark:text-slate-200">
                @include('partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>

    @if($bodyBottomHtml){!! site_injected_html($bodyBottomHtml) !!}@endif
    @if($footerHtml){!! site_injected_html($footerHtml) !!}@endif

    @push('scripts')
    <script>
    // ---- Admin sidebar mobile menu controller ----
    // Robust: works with or without Tailwind CDN by toggling both a Tailwind
    // utility class (-translate-x-full) AND a semantic .is-open class that is
    // backed by a hard CSS rule in admin.css. This guarantees the menu slides
    // in even if the Tailwind CDN fails to load on a shared host.
    var AdminMenu = {
        sidebar: null,
        backdrop: null,
        ready: function () {
            this.sidebar = this.sidebar || document.getElementById('admin-sidebar');
            this.backdrop = this.backdrop || document.getElementById('admin-sidebar-backdrop');
            return this.sidebar;
        },
        open: function () {
            if (!this.ready()) return;
            this.sidebar.classList.remove('-translate-x-full');
            this.sidebar.classList.add('is-open');
            if (this.backdrop) { this.backdrop.classList.remove('hidden'); requestAnimationFrame(function(){ this.backdrop.classList.remove('opacity-0'); }.bind(this)); }
        },
        close: function () {
            if (!this.ready()) return;
            this.sidebar.classList.add('-translate-x-full');
            this.sidebar.classList.remove('is-open');
            if (this.backdrop) { this.backdrop.classList.add('opacity-0'); var bd = this.backdrop; setTimeout(function(){ bd.classList.add('hidden'); }, 200); }
        },
        toggle: function () {
            if (!this.ready()) return;
            if (this.sidebar.classList.contains('is-open') || !this.sidebar.classList.contains('-translate-x-full')) this.open(); else this.close();
        }
    };

    // Theme toggle
    document.getElementById('theme-toggle')?.addEventListener('click', function () {
        var isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('admin-theme', isDark ? 'dark' : 'light');
    });

    // Backdrop click closes sidebar
    document.addEventListener('DOMContentLoaded', function () {
        var backdrop = document.getElementById('admin-sidebar-backdrop');
        if (backdrop) backdrop.addEventListener('click', function () { AdminMenu.close(); });

        // Auto-close sidebar when a nav link is clicked (mobile only)
        var sidebar = document.getElementById('admin-sidebar');
        if (sidebar) {
            sidebar.querySelectorAll('a.nav-link-admin').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 1024) AdminMenu.close();
                });
            });
        }

        // Close sidebar on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') AdminMenu.close();
        });

        // Close sidebar if resized to desktop while open
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                var bd = document.getElementById('admin-sidebar-backdrop');
                if (bd) { bd.classList.add('hidden', 'opacity-0'); }
            }
        });
    });
    </script>
    @endpush
    @stack('scripts')
</body>
</html>
