@extends('layouts.user')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@push('styles')
<style>
    /* Professional dashboard - paidwork.com inspired */
    .dash-hero {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #3b82f6 100%);
        border-radius: 1rem;
        padding: 2rem;
        color: #fff;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px -10px rgba(37,99,235,.5);
    }
    .dash-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    .dash-hero::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 20%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,.08) 0%, transparent 70%);
        border-radius: 50%;
    }
    .dash-hero > * { position: relative; z-index: 1; }
    .hero-amount {
        font-size: 2.5rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1;
    }
    .hero-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .7rem;
        border-radius: 999px;
        background: rgba(255,255,255,.15);
        font-size: .75rem;
        font-weight: 600;
        backdrop-filter: blur(10px);
    }
    .stat-pro {
        background: #fff;
        border-radius: .9rem;
        padding: 1.2rem;
        border: 1px solid #eef2f6;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
        transition: transform .2s ease, box-shadow .2s ease;
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }
    .stat-pro:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -8px rgba(0,0,0,.12);
    }
    .stat-icon-wrap {
        width: 42px;
        height: 42px;
        border-radius: .65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .stat-pro .stat-num {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1;
    }
    .stat-pro .stat-cap {
        font-size: .72rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .earn-method {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .9rem 1rem;
        border-radius: .75rem;
        border: 1px solid #eef2f6;
        background: #fff;
        transition: all .2s ease;
        text-decoration: none;
        color: inherit;
    }
    .earn-method:hover {
        border-color: var(--at-primary, #2563eb);
        background: #f8faff;
        transform: translateX(2px);
    }
    .earn-method-icon {
        width: 44px;
        height: 44px;
        border-radius: .65rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .earn-method-title {
        font-weight: 700;
        color: #1e293b;
        font-size: .9rem;
    }
    .earn-method-desc {
        font-size: .76rem;
        color: #94a3b8;
    }
    .progress-bar {
        height: 6px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #2563eb, #3b82f6);
        transition: width .6s ease;
    }
    .quick-tile {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .5rem;
        padding: 1rem .5rem;
        border-radius: .75rem;
        border: 1px solid #eef2f6;
        background: #fff;
        text-decoration: none;
        color: #475569;
        transition: all .2s ease;
        text-align: center;
    }
    .quick-tile:hover {
        border-color: var(--at-primary, #2563eb);
        background: #f8faff;
        color: var(--at-primary, #2563eb);
        transform: translateY(-2px);
    }
    .quick-tile-icon {
        width: 40px;
        height: 40px;
        border-radius: .6rem;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #2563eb;
    }
    .quick-tile:hover .quick-tile-icon {
        background: #2563eb;
        color: #fff;
    }
    .quick-tile-label {
        font-size: .72rem;
        font-weight: 600;
        line-height: 1.2;
    }
    .opp-card {
        border: 1px solid #eef2f6;
        border-radius: .75rem;
        padding: 1rem;
        background: #fff;
        transition: all .2s ease;
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        gap: .5rem;
        height: 100%;
    }
    .opp-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 6px 16px -8px rgba(0,0,0,.12);
    }
    .opp-price {
        font-size: 1.1rem;
        font-weight: 800;
        color: #16a34a;
    }
    .pending-pill {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .35rem .65rem;
        border-radius: .5rem;
        font-size: .72rem;
        font-weight: 700;
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .pending-pill.empty {
        background: #f1f5f9;
        color: #94a3b8;
        border-color: #e2e8f0;
    }
    @media (max-width: 640px) {
        .hero-amount { font-size: 1.8rem; }
        .dash-hero { padding: 1.25rem; }
    }
</style>
@endpush

@section('content')
<div class="card mb-6 border-blue-100"><div class="card-body"><div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between"><div><h3 class="font-bold text-slate-800">AI Workspace</h3><p class="text-sm text-slate-500">Create article drafts, task descriptions, gig copy and images without leaving your dashboard.</p><div class="text-xs font-semibold text-blue-600">AI credits: {{ money((float)$aiCredits) }}</div></div><a href="#ai-workspace" class="btn btn-primary">Open AI Generator</a></div><div class="mt-3 flex flex-wrap items-center gap-2"><form action="{{ route('user.ai.credits.purchase') }}" method="POST" class="flex gap-2">@csrf<input type="number" min="1" max="10000" step="0.01" name="amount" class="input w-28" placeholder="Credits" required><button class="btn btn-outline" type="submit">Buy AI Credits via Paystack</button></form><span class="text-xs text-slate-400">AI image generation costs {{ money((float) App\Models\SiteSetting::get('ai_image_price', 0.20)) }} credits by default. Payments are processed in your local currency via Paystack.</span></div><div id="ai-workspace" class="mt-4 grid md:grid-cols-2 gap-3"><textarea id="aiPrompt" class="input min-h-28" placeholder="Describe what you want AI to create…"></textarea><div class="flex flex-col gap-2"><button id="aiText" class="btn btn-primary">Generate Text</button><button id="aiImage" class="btn btn-outline">Generate Image</button><pre id="aiOut" class="hidden whitespace-pre-wrap text-sm bg-slate-50 rounded-xl p-3 max-h-72 overflow-auto"></pre><img id="aiImg" class="hidden w-full rounded-xl" alt="Generated image"></div></div></div></div>
@push('scripts')<script>
(()=>{const p=document.getElementById('aiPrompt'),o=document.getElementById('aiOut'),i=document.getElementById('aiImg');async function go(url,body){const r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{csrf_token()}}','Accept':'application/json'},body:JSON.stringify(body)});return r.json();}document.getElementById('aiText')?.addEventListener('click',async()=>{const d=await go('{{route('user.ai.text')}}',{prompt:p.value});o.classList.remove('hidden');o.textContent=d.text||d.error||'No result';});document.getElementById('aiImage')?.addEventListener('click',async()=>{const d=await go('{{route('user.ai.image')}}',{prompt:p.value});if(d.url){i.src=d.url;i.classList.remove('hidden');}else{alert(d.error||'Image generation failed');}});})();
</script>@endpush
{{-- Trial countdown banner --}}
@if(auth('web')->user()?->isOnTrial())
    @php $daysLeft = auth('web')->user()->trialDaysLeft(); @endphp
    <div class="card bg-gradient-to-r from-amber-50 to-orange-50 border-amber-300 mb-6">
        <div class="card-body flex items-center gap-4 flex-wrap">
            <div class="inline-flex w-12 h-12 rounded-xl bg-amber-500 items-center justify-center text-white flex-shrink-0">
                <x-icon name="clock" class="w-6 h-6" />
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-bold text-amber-800">Free Trial Active — {{ $daysLeft }} day(s) remaining</h3>
                <p class="text-sm text-amber-700">You're on a 3-day free trial. Pay the {{ money(\App\Services\SettingService::class ? app(\App\Services\SettingService::class)->activationFee() : 5) }} activation fee before your trial ends to keep full access.</p>
            </div>
            <a href="{{ route('user.activate') }}" class="btn btn-primary text-sm flex-shrink-0">Activate Now</a>
        </div>
    </div>
@endif

{{-- ===== Earnings Hero Card (paidwork.com style) ===== --}}
<div class="dash-hero mb-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="hero-chip"><x-icon name="wallet" class="w-3.5 h-3.5" /> Available Balance</span>
                @if($unreadNotifications > 0)
                <a href="{{ route('user.notifications') }}" class="hero-chip hover:bg-white/25"><x-icon name="notifications" class="w-3.5 h-3.5" /> {{ $unreadNotifications }} new</a>
                @endif
            </div>
            <div class="hero-amount">{{ money((float)$user->balance) }}</div>
            <p class="text-blue-100 text-sm mt-2">Total Earned: <span class="font-semibold text-white">{{ money((float)$user->total_earned) }}</span> &middot; This Month: <span class="font-semibold text-white">{{ money((float)$earningsThisMonth) }}</span></p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('user.withdraw') }}" class="btn bg-white text-blue-700 hover:bg-blue-50 font-bold text-sm shadow-lg"><x-icon name="withdraw" class="w-4 h-4" /> Withdraw</a>
            <a href="{{ route('user.wallet') }}" class="btn bg-white/15 text-white border border-white/30 hover:bg-white/25 font-bold text-sm backdrop-blur"><x-icon name="deposit" class="w-4 h-4" /> Deposit</a>
        </div>
    </div>
</div>

{{-- ===== Professional Stats Grid ===== --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-pro">
        <div class="flex items-center gap-3">
            <div class="stat-icon-wrap bg-green-50 text-green-600"><x-icon name="check-circle" class="w-5 h-5" /></div>
            <div>
                <div class="stat-num">{{ $completedTasks }}</div>
                <div class="stat-cap">Tasks Done</div>
            </div>
        </div>
    </div>
    <div class="stat-pro">
        <div class="flex items-center gap-3">
            <div class="stat-icon-wrap bg-blue-50 text-blue-600"><x-icon name="bookings" class="w-5 h-5" /></div>
            <div>
                <div class="stat-num">{{ $totalBookings }}</div>
                <div class="stat-cap">Total Bookings</div>
            </div>
        </div>
    </div>
    <div class="stat-pro">
        <div class="flex items-center gap-3">
            <div class="stat-icon-wrap bg-purple-50 text-purple-600"><x-icon name="gigs" class="w-5 h-5" /></div>
            <div>
                <div class="stat-num">{{ $totalGigs }}</div>
                <div class="stat-cap">My Gigs</div>
            </div>
        </div>
    </div>
    <div class="stat-pro">
        <div class="flex items-center gap-3">
            <div class="stat-icon-wrap bg-orange-50 text-orange-600"><x-icon name="marketplace" class="w-5 h-5" /></div>
            <div>
                <div class="stat-num">{{ $totalListings }}</div>
                <div class="stat-cap">Listings</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Pending Actions Banner ===== --}}
<div class="card mb-6 border-l-4 border-l-amber-400">
    <div class="card-body">
        <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
            <x-icon name="pending" class="w-5 h-5 text-amber-500" />
            Action Required
        </h3>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="flex flex-col gap-2 p-3 rounded-lg border border-slate-100 {{ $pendingTasks->isNotEmpty() ? 'bg-amber-50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-semibold">Tasks Awaiting Approval</span>
                    <span class="pending-pill {{ $pendingTasks->isEmpty() ? 'empty' : '' }}">{{ $pendingTasks->count() }}</span>
                </div>
                @if($pendingTasks->isNotEmpty())
                <a href="{{ route('user.offers', ['status' => 'pending']) }}" class="text-xs text-blue-600 hover:underline">View pending &rarr;</a>
                @endif
            </div>
            <div class="flex flex-col gap-2 p-3 rounded-lg border border-slate-100 {{ $pendingBookings->isNotEmpty() ? 'bg-amber-50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-semibold">Proofs Under Review</span>
                    <span class="pending-pill {{ $pendingBookings->isEmpty() ? 'empty' : '' }}">{{ $pendingBookings->count() }}</span>
                </div>
                @if($pendingBookings->isNotEmpty())
                <a href="{{ route('user.bookings', ['status' => 'submitted']) }}" class="text-xs text-blue-600 hover:underline">View proofs &rarr;</a>
                @endif
            </div>
            <div class="flex flex-col gap-2 p-3 rounded-lg border border-slate-100 {{ $pendingProofs > 0 ? 'bg-amber-50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-semibold">Proofs to Review</span>
                    <span class="pending-pill {{ $pendingProofs === 0 ? 'empty' : '' }}">{{ $pendingProofs }}</span>
                </div>
                @if($pendingProofs > 0)
                <a href="{{ route('user.offers') }}" class="text-xs text-blue-600 hover:underline">Review now &rarr;</a>
                @endif
            </div>
            <div class="flex flex-col gap-2 p-3 rounded-lg border border-slate-100 {{ $pendingWithdrawals > 0 ? 'bg-amber-50' : '' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-semibold">Withdrawals Pending</span>
                    <span class="pending-pill {{ $pendingWithdrawals === 0 ? 'empty' : '' }}">{{ $pendingWithdrawals }}</span>
                </div>
                @if($pendingWithdrawals > 0)
                <a href="{{ route('user.transactions') }}" class="text-xs text-blue-600 hover:underline">View transactions &rarr;</a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===== Main Grid: Earning Methods + Quick Access ===== --}}
<div class="grid lg:grid-cols-3 gap-6 mb-6">
    {{-- Earning Methods (left, 2 cols) --}}
    <div class="card lg:col-span-2">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2"><x-icon name="dollar" class="w-5 h-5 text-green-600" /> Ways to Earn</h3>
                <span class="text-xs text-slate-400">Start earning now</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-3">
                <a href="{{ route('user.tasks') }}" class="earn-method">
                    <div class="earn-method-icon bg-blue-50 text-blue-600"><x-icon name="browse" class="w-5 h-5" /></div>
                    <div class="flex-1 min-w-0">
                        <div class="earn-method-title">Complete Micro Tasks</div>
                        <div class="earn-method-desc">Like, follow, subscribe & earn</div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-slate-300" />
                </a>
                <a href="{{ route('user.task.create') }}" class="earn-method">
                    <div class="earn-method-icon bg-indigo-50 text-indigo-600"><x-icon name="megaphone" class="w-5 h-5" /></div>
                    <div class="flex-1 min-w-0">
                        <div class="earn-method-title">Post a Task</div>
                        <div class="earn-method-desc">Get followers & engagement</div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-slate-300" />
                </a>
                <a href="{{ route('user.gigs.create') }}" class="earn-method">
                    <div class="earn-method-icon bg-purple-50 text-purple-600"><x-icon name="gigs" class="w-5 h-5" /></div>
                    <div class="flex-1 min-w-0">
                        <div class="earn-method-title">Create a Gig</div>
                        <div class="earn-method-desc">Sell your social media services</div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-slate-300" />
                </a>
                <a href="{{ route('user.marketplace.create') }}" class="earn-method">
                    <div class="earn-method-icon bg-orange-50 text-orange-600"><x-icon name="marketplace" class="w-5 h-5" /></div>
                    <div class="flex-1 min-w-0">
                        <div class="earn-method-title">List on Marketplace</div>
                        <div class="earn-method-desc">Sell accounts, services & more</div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-slate-300" />
                </a>
                <a href="{{ route('user.affiliate') }}" class="earn-method">
                    <div class="earn-method-icon bg-pink-50 text-pink-600"><x-icon name="affiliate" class="w-5 h-5" /></div>
                    <div class="flex-1 min-w-0">
                        <div class="earn-method-title">Affiliate Program</div>
                        <div class="earn-method-desc">Refer friends & earn commission</div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-slate-300" />
                </a>
                <a href="{{ route('home') }}" class="earn-method">
                    <div class="earn-method-icon bg-cyan-50 text-cyan-600"><x-icon name="feed" class="w-5 h-5" /></div>
                    <div class="flex-1 min-w-0">
                        <div class="earn-method-title">Community Updates</div>
                        <div class="earn-method-desc">Post, share & grow your network</div>
                    </div>
                    <x-icon name="arrow-right" class="w-4 h-4 text-slate-300" />
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Access (right, 1 col) --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2"><x-icon name="zap" class="w-5 h-5 text-yellow-500" /> Quick Access</h3>
            <div class="grid grid-cols-3 gap-3">
                <a href="{{ route('user.tasks') }}" class="quick-tile">
                    <div class="quick-tile-icon"><x-icon name="tasks" class="w-5 h-5" /></div>
                    <span class="quick-tile-label">Tasks</span>
                </a>
                <a href="{{ route('user.gigs.index') }}" class="quick-tile">
                    <div class="quick-tile-icon"><x-icon name="gigs" class="w-5 h-5" /></div>
                    <span class="quick-tile-label">My Gigs</span>
                </a>
                <a href="{{ route('user.marketplace.index') }}" class="quick-tile">
                    <div class="quick-tile-icon"><x-icon name="marketplace" class="w-5 h-5" /></div>
                    <span class="quick-tile-label">Listings</span>
                </a>
                <a href="{{ route('user.bookings') }}" class="quick-tile">
                    <div class="quick-tile-icon"><x-icon name="bookings" class="w-5 h-5" /></div>
                    <span class="quick-tile-label">Bookings</span>
                </a>
                <a href="{{ route('user.transactions') }}" class="quick-tile">
                    <div class="quick-tile-icon"><x-icon name="transactions" class="w-5 h-5" /></div>
                    <span class="quick-tile-label">History</span>
                </a>
                <a href="{{ route('user.profile') }}" class="quick-tile">
                    <div class="quick-tile-icon"><x-icon name="profile" class="w-5 h-5" /></div>
                    <span class="quick-tile-label">Profile</span>
                </a>
                <a href="{{ route('user.blog.index') }}" class="quick-tile">
                    <div class="quick-tile-icon"><x-icon name="blog" class="w-5 h-5" /></div>
                    <span class="quick-tile-label">Blog</span>
                </a>
                <a href="{{ route('user.sponsored-ads') }}" class="quick-tile">
                    <div class="quick-tile-icon"><x-icon name="ads" class="w-5 h-5" /></div>
                    <span class="quick-tile-label">Ads</span>
                </a>
                <a href="{{ route('user.verification') }}" class="quick-tile">
                    <div class="quick-tile-icon bg-blue-50"><x-verified-badge size="w-5 h-5" /></div>
                    <span class="quick-tile-label">Verify</span>
                </a>
            </div>
            {{-- Earnings progress --}}
            <div class="mt-5 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-semibold text-slate-600">Monthly Earnings Goal</span>
                    <span class="text-xs font-bold text-blue-600">{{ money((float)$earningsThisMonth) }} / {{ money(100) }}</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ min(100, ($earningsThisMonth / 100) * 100) }}%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-2">{{ $completedTasks }} tasks completed this period</p>
            </div>
        </div>
    </div>
</div>

{{-- ===== Available Earning Opportunities ===== --}}
@if($availableTasks->isNotEmpty())
<div class="card mb-6">
    <div class="card-body">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-800 flex items-center gap-2"><x-icon name="trending" class="w-5 h-5 text-green-600" /> Available Earning Opportunities</h3>
            <a href="{{ route('user.tasks') }}" class="text-blue-600 text-sm hover:underline font-semibold">View all <x-icon name="arrow-right" class="w-4 h-4 inline" /></a>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($availableTasks as $task)
            <a href="{{ route('user.task', $task) }}" class="opp-card">
                @if($task->image)
                <img src="{{ storage_asset($task->image) }}" alt="{{ $task->title }}" class="w-full h-24 object-cover rounded-lg" loading="lazy">
                @else
                <div class="w-full h-24 rounded-lg bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                    <x-icon name="tasks" class="w-8 h-8 text-blue-400" />
                </div>
                @endif
                <p class="font-semibold text-slate-800 text-sm line-clamp-2">{{ $task->title }}</p>
                <div class="flex items-center justify-between mt-auto">
                    <span class="opp-price">{{ money((float)$task->price) }}</span>
                    <span class="text-xs text-slate-400">{{ $task->category->name ?? 'General' }}</span>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ===== Recent Transactions + Active Bookings ===== --}}
<div class="grid lg:grid-cols-3 gap-6">
    {{-- Recent transactions (2 cols) --}}
    <div class="card lg:col-span-2">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2"><x-icon name="transactions" class="w-5 h-5 text-blue-600" /> Recent Activity</h3>
                <a href="{{ route('user.transactions') }}" class="text-blue-600 text-sm hover:underline font-semibold">View all</a>
            </div>
            @if($user->transactions->isNotEmpty())
            <div class="space-y-2">
                @foreach($user->transactions as $t)
                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 {{ $t->amount >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                        <x-icon name="{{ $t->amount >= 0 ? 'deposit' : 'withdraw' }}" class="w-4 h-4" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate">{{ $t->description ?? ucfirst(str_replace('_',' ',$t->type)) }}</p>
                        <p class="text-xs text-slate-400">{{ $t->created_at->format('M d, Y - H:i') }}</p>
                    </div>
                    <span class="font-bold text-sm {{ $t->amount >= 0 ? 'text-green-600' : 'text-red-600' }} whitespace-nowrap">
                        {{ $t->amount >= 0 ? '+' : '' }}{{ money(abs((float)$t->amount)) }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-10">
                <div class="w-14 h-14 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                    <x-icon name="transactions" class="w-7 h-7 text-slate-300" />
                </div>
                <p class="text-slate-400 text-sm mb-3">No transactions yet.</p>
                <a href="{{ route('user.tasks') }}" class="btn btn-primary text-sm">Start Earning <x-icon name="arrow-right" class="w-4 h-4" /></a>
            </div>
            @endif
        </div>
    </div>

    {{-- Active bookings (1 col) --}}
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2"><x-icon name="bookings" class="w-5 h-5 text-indigo-600" /> Active Bookings</h3>
                <a href="{{ route('user.bookings') }}" class="text-blue-600 text-sm hover:underline font-semibold">All</a>
            </div>
            @if($openBookings->isNotEmpty())
            <div class="space-y-3">
                @foreach($openBookings as $booking)
                <a href="{{ route('user.task', $booking->task) }}" class="block p-3 rounded-lg border border-slate-100 hover:border-blue-200 hover:bg-blue-50/30 transition-all">
                    <p class="font-semibold text-slate-800 text-sm line-clamp-1">{{ $booking->task->title }}</p>
                    <div class="flex items-center justify-between mt-1.5">
                        <span class="text-blue-600 font-bold text-sm">{{ money((float)$booking->task->price) }}</span>
                        <span class="text-xs text-slate-400">Expires {{ $booking->expire_in?->format('M d') ?? '—' }}</span>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2">
                    <x-icon name="bookings" class="w-6 h-6 text-slate-300" />
                </div>
                <p class="text-slate-400 text-xs mb-3">No active bookings.</p>
                <a href="{{ route('user.tasks') }}" class="btn btn-outline text-xs">Browse Tasks</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
