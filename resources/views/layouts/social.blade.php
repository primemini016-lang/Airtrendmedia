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
    <title>@yield('title', 'Airtrendmedia') — Social</title>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('{{ asset("sw.js") }}')
                    .catch(function (err) { console.warn('SW registration failed:', err); });
            });
        }
    </script>
    <script>
        if (localStorage.getItem('site-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    {{-- Facebook-style design system --}}
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
        .fb-hover-bg:hover { background: var(--fb-hover); }
        .fb-text-secondary { color: var(--fb-text-secondary); }
        .fb-border { border-color: var(--fb-border); }
        .fb-btn-primary { background: var(--fb-blue); color: #fff; }
        .fb-btn-primary:hover { background: var(--fb-blue-hover); }
        .fb-btn-secondary { background: var(--fb-hover); color: var(--fb-text); }
        .fb-btn-secondary:hover { background: var(--fb-border); }
        .fb-nav-icon { width: 112px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 8px; }
        .fb-nav-icon:hover { background: var(--fb-hover); }
        .fb-nav-icon.active { border-bottom: 3px solid var(--fb-blue); border-radius: 0; color: var(--fb-blue); }
        .fb-badge {
            background: #F02849; color: #fff; font-size: 11px; font-weight: 700;
            min-width: 18px; height: 18px; border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            position: absolute; top: 4px; right: 8px; padding: 0 5px;
        }
        .fb-avatar { border-radius: 50%; object-fit: cover; }
        .fb-story-ring {
            border: 3px solid var(--fb-blue); border-radius: 50%; padding: 2px;
        }
        .fb-react-btn {
            display: flex; align-items: center; gap: 6px; padding: 6px 12px;
            border-radius: 6px; cursor: pointer; font-weight: 600; color: var(--fb-text-secondary);
        }
        .fb-react-btn:hover { background: var(--fb-hover); }
        .fb-chat-bubble-received { background: var(--fb-hover); color: var(--fb-text); border-radius: 18px 18px 18px 4px; }
        .fb-chat-bubble-sent { background: var(--fb-blue); color: #fff; border-radius: 18px 18px 4px 18px; }
        .fb-input {
            background: var(--fb-hover); border: none; border-radius: 20px;
            padding: 8px 16px; color: var(--fb-text); outline: none; width: 100%;
        }
        .fb-input::placeholder { color: var(--fb-text-secondary); }
        .fb-search { background: var(--fb-hover); border-radius: 20px; }
        .fb-dropdown {
            position: absolute; top: 56px; right: 0; background: var(--fb-card);
            border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            min-width: 360px; max-height: 500px; overflow-y: auto; z-index: 60;
            padding: 8px 0;
        }
        .fb-right-rail-item {
            display: flex; align-items: center; gap: 12px; padding: 8px 12px;
            border-radius: 8px; cursor: pointer;
        }
        .fb-right-rail-item:hover { background: var(--fb-hover); }
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--fb-border); border-radius: 4px; }
        .fb-tab-link { padding: 16px; border-bottom: 3px solid transparent; font-weight: 600; color: var(--fb-text-secondary); cursor: pointer; }
        .fb-tab-link:hover { background: var(--fb-hover); border-radius: 8px 8px 0 0; }
        .fb-tab-link.active { color: var(--fb-blue); border-bottom-color: var(--fb-blue); }
        .fb-story-create {
            background: linear-gradient(to top, rgba(0,0,0,0.6), transparent 60%);
        }
        @keyframes fb-bounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-3px); } }
        .fb-notification-dot { animation: fb-bounce 2s infinite; }
    </style>
    @stack('styles')
</head>
<body class="fb-body min-h-screen">
    @php
        $authUser = auth('web')->user();
        $settings = app(\App\Services\SettingService::class)->all();
        $siteName = $settings->name ?? 'MiniWorkers';
        $logoUrl = null;
        if (!empty($settings->logo)) { $logoUrl = Storage::url($settings->logo); }
        $unreadNotifs = $authUser ? $authUser->notifications()->where('is_read', false)->count() : 0;
        $unreadMsgs = 0;
        if ($authUser) {
            $unreadMsgs = \App\Models\ConversationParticipant::where('user_id', $authUser->id)
                ->where('last_read_at', '<', \Illuminate\Support\Carbon::now())
                ->whereHas('conversation', function($q) use ($authUser) {
                    $q->whereHas('messages', function($mq) use ($authUser) {
                        $mq->where('sender_id', '!=', $authUser->id)
                          ->where('created_at', '>', \App\Models\ConversationParticipant::where('user_id', $authUser->id)->where('conversation_id', $mq->getQualified('conversation_id'))->value('last_read_at') ?? '1970-01-01');
                    });
                })->count();
        }
        $friendRequests = $authUser ? $authUser->followers()->whereDoesntHave('followers', function($q) use ($authUser) { $q->where('user_id', $authUser->id); })->count() : 0;
        $currentRoute = request()->route() ? request()->route()->getName() : '';
    @endphp

    {{-- ============ FACEBOOK TOP NAVIGATION BAR ============ --}}
    <header class="sticky top-0 z-50 bg-[var(--fb-card)] shadow-sm" style="border-bottom: 1px solid var(--fb-border);">
        <div class="flex items-center justify-between h-14 px-2 sm:px-4">
            {{-- LEFT: Logo + Search --}}
            <div class="flex items-center gap-2 flex-1 max-w-xs">
                <a href="{{ route('social.feed') }}" class="flex-shrink-0">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" class="h-10 w-10 rounded-full" alt="{{ $siteName }}">
                    @else
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-xl" style="background: var(--fb-blue);">{{ strtoupper(substr($siteName,0,1)) }}</div>
                    @endif
                </a>
                {{-- Search bar (Facebook style) --}}
                <div class="relative flex-1 hidden sm:block">
                    <form action="{{ route('social.explore') }}" method="GET" class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search social"
                               class="fb-search w-full pl-10 pr-4 py-2.5 text-sm rounded-full outline-none"
                               style="background: var(--fb-hover); color: var(--fb-text);">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color: var(--fb-text-secondary);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </form>
                </div>
            </div>

            {{-- CENTER: Navigation Icons (Facebook style) --}}
            <nav class="hidden lg:flex items-center gap-1">
                <a href="{{ route('social.feed') }}" class="fb-nav-icon {{ $currentRoute === 'social.feed' ? 'active' : '' }}" title="Home">
                    <x-icon name="home" class="w-7 h-7" />
                </a>
                <a href="{{ route('social.suggestions') }}" class="fb-nav-icon {{ $currentRoute === 'social.suggestions' || $currentRoute === 'social.friends' ? 'active' : '' }}" title="Friends">
                    <span class="relative">
                        <x-icon name="friends" class="w-7 h-7" />
                        @if($friendRequests > 0)<span class="fb-badge">{{ $friendRequests }}</span>@endif
                    </span>
                </a>
                <a href="{{ route('social.explore') }}?tab=videos" class="fb-nav-icon {{ $currentRoute === 'social.explore' && request('tab') === 'videos' ? 'active' : '' }}" title="Videos">
                    <x-icon name="videos" class="w-7 h-7" />
                </a>
                <a href="{{ route('social.explore') }}?tab=reels" class="fb-nav-icon {{ $currentRoute === 'social.explore' && request('tab') === 'reels' ? 'active' : '' }}" title="Reels">
                    <x-icon name="reels" class="w-7 h-7" />
                </a>
                <a href="{{ route('marketplace') }}" class="fb-nav-icon {{ $currentRoute === 'marketplace' ? 'active' : '' }}" title="Marketplace">
                    <x-icon name="marketplace" class="w-7 h-7" />
                </a>
                <a href="{{ route('social.groups') }}" class="fb-nav-icon {{ str_starts_with($currentRoute, 'social.group') ? 'active' : '' }}" title="Groups">
                    <x-icon name="groups" class="w-7 h-7" />
                </a>
                <a href="{{ route('social.pages') }}" class="fb-nav-icon {{ str_starts_with($currentRoute, 'social.page') ? 'active' : '' }}" title="Pages">
                    <x-icon name="pages" class="w-7 h-7" />
                </a>
            </nav>

            {{-- RIGHT: Notifications + Messages + Profile --}}
            <div class="flex items-center gap-2 flex-1 justify-end max-w-xs">
                {{-- Create menu (Facebook "+" button) --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="w-10 h-10 rounded-full flex items-center justify-center fb-hover-bg" style="background: var(--fb-hover);" title="Create">
                        <x-icon name="create" class="w-5 h-5" />
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                         class="fb-dropdown" style="min-width: 280px;">
                        <div class="px-4 py-3 font-bold text-lg border-b fb-border">Create</div>
                        <a href="{{ route('social.feed') }}#create-post" @click="open=false" class="fb-right-rail-item">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="create-post" class="w-5 h-5" /></div>
                            <span class="font-medium">Post</span>
                        </a>
                        <a href="{{ route('social.pages.create') }}" @click="open=false" class="fb-right-rail-item">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="pages" class="w-5 h-5" /></div>
                            <span class="font-medium">Page</span>
                        </a>
                        <a href="{{ route('social.groups.create') }}" @click="open=false" class="fb-right-rail-item">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="groups" class="w-5 h-5" /></div>
                            <span class="font-medium">Group</span>
                        </a>
                        <a href="{{ route('social.explore') }}?tab=reels" @click="open=false" class="fb-right-rail-item">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="reels" class="w-5 h-5" /></div>
                            <span class="font-medium">Reel</span>
                        </a>
                        <a href="{{ route('social.feed') }}#create-story" @click="open=false" class="fb-right-rail-item">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="create-story" class="w-5 h-5" /></div>
                            <span class="font-medium">Story</span>
                        </a>
                    </div>
                </div>

                {{-- Messenger / Messages --}}
                <div x-data="{ open: false }" class="relative">
                    <a href="{{ route('social.chat') }}" @click.prevent="open = !open" class="w-10 h-10 rounded-full flex items-center justify-center fb-hover-bg relative" style="background: var(--fb-hover);" title="Messenger">
                        <x-icon name="messenger" class="w-5 h-5" />
                        @if($unreadMsgs > 0)<span class="fb-badge">{{ $unreadMsgs }}</span>@endif
                    </a>
                    <div x-show="open" @click.away="open = false" x-transition class="fb-dropdown" style="right: -80px;">
                        <div class="px-4 py-3 font-bold text-lg">Chats</div>
                        @php
                            $recentConversations = $authUser ? \App\Models\Conversation::whereHas('participants', function($q) use ($authUser) {
                                $q->where('user_id', $authUser->id);
                            })->with(['messages' => function($q){ $q->latest()->limit(1); }, 'participants.user'])->latest()->limit(8)->get() : collect();
                        @endphp
                        @if($recentConversations->isEmpty())
                            <div class="px-4 py-6 text-center fb-text-secondary text-sm">No conversations yet.<br><a href="{{ route('social.suggestions') }}" class="text-blue-600 font-medium">Find people to chat with</a></div>
                        @else
                            @foreach($recentConversations as $conv)
                                @php
                                    $otherUser = $conv->is_group ? null : $conv->participants->where('user_id', '!=', $authUser->id)->first()?->user;
                                    $lastMsg = $conv->messages->first();
                                @endphp
                                <a href="{{ route('social.chat', ['conversation' => $conv->id]) }}" class="fb-right-rail-item" @click="open=false">
                                    <img src="{{ $otherUser?->avatarUrl() ?? asset('images/default-avatar.png') }}" class="w-10 h-10 fb-avatar" alt="">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-sm truncate">{{ $conv->is_group ? $conv->name : $otherUser?->name }}</div>
                                        <div class="text-xs fb-text-secondary truncate">{{ $lastMsg?->message ?? '' }}</div>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                        <div class="border-t fb-border mt-2 pt-2">
                            <a href="{{ route('social.chat') }}" class="fb-right-rail-item text-blue-600 font-medium text-sm">See all chats</a>
                        </div>
                    </div>
                </div>

                {{-- Notifications --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="w-10 h-10 rounded-full flex items-center justify-center fb-hover-bg relative" style="background: var(--fb-hover);" title="Notifications">
                        <x-icon name="notifications" class="w-5 h-5" />
                        @if($unreadNotifs > 0)<span class="fb-badge fb-notification-dot">{{ $unreadNotifs }}</span>@endif
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="fb-dropdown" style="right: -160px;">
                        <div class="px-4 py-3 font-bold text-lg">Notifications</div>
                        @php $recentNotifs = $authUser ? $authUser->notifications()->latest()->limit(8)->get() : collect(); @endphp
                        @if($recentNotifs->isEmpty())
                            <div class="px-4 py-6 text-center fb-text-secondary text-sm">No notifications</div>
                        @else
                            @foreach($recentNotifs as $notif)
                                <a href="{{ $notif->url ?? '#' }}" class="fb-right-rail-item {{ $notif->is_read ? '' : 'bg-blue-50 dark:bg-blue-900/20' }}">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background: var(--fb-blue); color: #fff;">
                                        <x-icon name="{{ $notif->type === 'follow' ? 'friends' : ($notif->type === 'like' ? 'like' : ($notif->type === 'comment' ? 'comment' : 'notifications')) }}" class="w-5 h-5" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm">{{ $notif->title }}</div>
                                        <div class="text-xs fb-text-secondary truncate">{{ $notif->body }}</div>
                                    </div>
                                </a>
                            @endforeach
                        @endif
                        <div class="border-t fb-border mt-2 pt-2">
                            <a href="{{ route('user.notifications') }}" class="fb-right-rail-item text-blue-600 font-medium text-sm">See all notifications</a>
                        </div>
                    </div>
                </div>

                {{-- Profile dropdown --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex-shrink-0">
                        <img src="{{ $authUser?->avatarUrl() ?? asset('images/default-avatar.png') }}" class="w-10 h-10 fb-avatar ring-2 ring-transparent hover:ring-blue-400" alt="Profile">
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="fb-dropdown" style="right: 0; min-width: 320px;">
                        <a href="{{ route('social.profile', $authUser?->username ?? $authUser?->id) }}" class="fb-right-rail-item border-b fb-border pb-3 mb-2" @click="open=false">
                            <img src="{{ $authUser?->avatarUrl() ?? asset('images/default-avatar.png') }}" class="w-10 h-10 fb-avatar" alt="">
                            <div>
                                <div class="font-semibold">{{ $authUser?->name }}</div>
                                <div class="text-xs fb-text-secondary">See your profile</div>
                            </div>
                        </a>
                        <a href="{{ route('social.profile', $authUser?->username ?? $authUser?->id) }}" class="fb-right-rail-item" @click="open=false"><x-icon name="profile" class="w-5 h-5" /><span>Profile</span></a>
                        <a href="{{ route('social.monetization') }}" class="fb-right-rail-item" @click="open=false"><x-icon name="monetization" class="w-5 h-5" /><span>Monetization</span></a>
                        <a href="{{ route('social.suggestions') }}" class="fb-right-rail-item" @click="open=false"><x-icon name="friend-suggestions" class="w-5 h-5" /><span>People You May Know</span></a>
                        <a href="{{ route('user.dashboard') }}" class="fb-right-rail-item" @click="open=false"><x-icon name="dashboard" class="w-5 h-5" /><span>Marketplace Dashboard</span></a>
                        <a href="{{ route('blog.index') }}" class="fb-right-rail-item" @click="open=false"><x-icon name="blog" class="w-5 h-5" /><span>Blog</span></a>
                        <a href="{{ route('user.wallet') }}" class="fb-right-rail-item" @click="open=false"><x-icon name="wallet" class="w-5 h-5" /><span>Wallet</span></a>
                        <div class="border-t fb-border my-2"></div>
                        <a href="{{ route('user.profile') }}" class="fb-right-rail-item" @click="open=false"><x-icon name="settings" class="w-5 h-5" /><span>Settings & Privacy</span></a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="fb-right-rail-item w-full text-left" onclick="open=false"><x-icon name="logout" class="w-5 h-5" /><span>Log Out</span></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Mobile bottom nav (Facebook style) --}}
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 flex items-center justify-around py-1" style="background: var(--fb-card); border-top: 1px solid var(--fb-border);">
        <a href="{{ route('social.feed') }}" class="fb-nav-icon !w-16 !h-12" title="Home"><x-icon name="home" class="w-6 h-6" /></a>
        <a href="{{ route('social.suggestions') }}" class="fb-nav-icon !w-16 !h-12 relative" title="Friends"><x-icon name="friends" class="w-6 h-6" />@if($friendRequests > 0)<span class="fb-badge">{{ $friendRequests }}</span>@endif</a>
        <a href="{{ route('social.chat') }}" class="fb-nav-icon !w-16 !h-12 relative" title="Messages"><x-icon name="messenger" class="w-6 h-6" />@if($unreadMsgs > 0)<span class="fb-badge">{{ $unreadMsgs }}</span>@endif</a>
        <a href="{{ route('social.explore') }}?tab=videos" class="fb-nav-icon !w-16 !h-12" title="Videos"><x-icon name="videos" class="w-6 h-6" /></a>
        <a href="{{ route('social.explore') }}?tab=reels" class="fb-nav-icon !w-16 !h-12" title="Reels"><x-icon name="reels" class="w-6 h-6" /></a>
        <a href="{{ route('marketplace') }}" class="fb-nav-icon !w-16 !h-12" title="Marketplace"><x-icon name="marketplace" class="w-6 h-6" /></a>
        <a href="{{ route('social.pages') }}" class="fb-nav-icon !w-16 !h-12" title="Pages"><x-icon name="pages" class="w-6 h-6" /></a>
        <a href="{{ route('social.groups') }}" class="fb-nav-icon !w-16 !h-12" title="Groups"><x-icon name="groups" class="w-6 h-6" /></a>
    </nav>

    {{-- Main content --}}
    <main class="@yield('main_class', 'max-w-[1100px] mx-auto px-0 sm:px-4 py-4')">
        @yield('content')
    </main>

    {{-- Mobile spacer for bottom nav --}}
    <div class="lg:hidden h-14"></div>

    {{-- Theme toggle script --}}
    <script>
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('[x-show]').forEach(el => el.__x?.$data && (el.__x.$data.open = false));
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
