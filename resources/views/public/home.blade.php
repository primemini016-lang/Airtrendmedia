@extends('layouts.app')
@section('title', 'Airtrendmedia — Microjobs, Gigs, Marketplace & Creator Tools')

@section('content')
<!-- Hero -->
<section class="auth-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-24 text-center">
        <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight">Airtrendmedia — Earn, Work, Publish & Grow.</h1>
        <p class="mt-4 text-lg text-blue-100 max-w-2xl mx-auto">{{ $s->footer_text ?? 'A professional marketplace for microjobs, freelance gigs, digital services, publishing, messaging, referrals and advertising — all in one account, one wallet and one admin system.' }}</p>
        <div class="mt-8 flex items-center justify-center gap-3 flex-wrap">
            @guest
            <a href="{{ route('register') }}" class="btn bg-white text-blue-700 hover:bg-blue-50">Get Started — 3-Day Free Trial</a>
            <a href="{{ route('browse') }}" class="btn btn-outline text-white border-white">Browse Tasks</a>
            @else
            <a href="{{ route('user.dashboard') }}" class="btn bg-white text-blue-700 hover:bg-blue-50">Go to Dashboard</a>
            
            @endguest
        </div>
        <div class="mt-10 grid grid-cols-3 gap-4 max-w-lg mx-auto text-center">
            <div><div class="text-2xl font-bold">{{ \App\Models\Task::where('status',1)->count() }}</div><div class="text-xs text-blue-100">Active Tasks</div></div>
            <div><div class="text-2xl font-bold">{{ \App\Models\User::count() }}</div><div class="text-xs text-blue-100">Members</div></div>
            <div><div class="text-2xl font-bold">{{ money((float)\App\Models\Transaction::whereIn('type',['task_credit','affiliate_reward'])->sum('amount')) }}</div><div class="text-xs text-blue-100">Paid Out</div></div>
        </div>
    </div>
</section>

<!-- Platform Features Tabs -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-14" x-data="{ tab: 'work' }">
    <h2 class="text-2xl font-bold text-center text-slate-800 mb-2">Everything in One Place</h2>
    <p class="text-center text-slate-500 mb-8">Use one account to earn, offer services, publish content and run campaigns.</p>
    <div class="flex justify-center gap-2 mb-8 flex-wrap">
        <button @click="tab='work'" :class="tab==='work' ? 'btn-primary' : 'btn-ghost border'" class="btn"><x-icon name="briefcase" class="w-4 h-4 inline" /> Freelancers</button>
        <button @click="tab='marketplace'" :class="tab==='marketplace' ? 'btn-primary' : 'btn-ghost border'" class="btn"><x-icon name="marketplace" class="w-4 h-4 inline" /> Marketplace</button>
        <button @click="tab='advertisers'" :class="tab==='advertisers' ? 'btn-primary' : 'btn-ghost border'" class="btn"><x-icon name="megaphone" class="w-4 h-4 inline" /> Advertisers</button>
    </div>
    <div x-show="tab==='work'" class="grid md:grid-cols-3 gap-6">
        <div class="card p-6"><div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4"><x-icon name="briefcase" class="w-6 h-6" /></div><h3 class="font-bold text-slate-800 mb-2">Microjobs & Proofs</h3><p class="text-sm text-slate-500 mb-3">Complete short paid jobs, book available slots and submit text or image proof for employer review.</p><p class="text-sm text-slate-500">Clear earnings tracking, task history and wallet ledger.</p></div>
        <div class="card p-6"><div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4"><x-icon name="gigs" class="w-6 h-6" /></div><h3 class="font-bold text-slate-800 mb-2">Gigs & Freelance Services</h3><p class="text-sm text-slate-500 mb-3">Create service offers, showcase your work, receive reviews and communicate with clients.</p><p class="text-sm text-slate-500">Your profile, portfolio, followers and reputation live in one place.</p></div>
        <div class="card p-6"><div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center mb-4"><x-icon name="chat" class="w-6 h-6" /></div><h3 class="font-bold text-slate-800 mb-2">Messaging & Connections</h3><p class="text-sm text-slate-500 mb-3">Find people, follow freelancers, message clients and get visible online-status indicators.</p><p class="text-sm text-slate-500">Notifications and unread counts keep conversations visible.</p></div>
    </div>

    {{-- Marketplace tab --}}
    <div x-show="tab==='marketplace'" x-cloak class="grid md:grid-cols-3 gap-6">
        <div class="card p-6">
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4"><x-icon name="marketplace" class="w-6 h-6" /></div>
            <h3 class="font-bold text-slate-800 mb-2">Microjobs from {{ money(0.01) }}</h3>
            <p class="text-sm text-slate-500 mb-3"><b>Feature:</b> Browse real microjobs, digital services, creative work, reviews and other marketplace opportunities.</p>
            <p class="text-sm text-slate-500 mb-3"><b>Benefit:</b> Earn from anywhere by completing tiny tasks in minutes.</p>
            <p class="text-sm text-slate-500"><b>How to use:</b> Activate account → Browse Tasks → book → submit proof → get paid.</p>
        </div>
        <div class="card p-6">
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-4"><x-icon name="megaphone" class="w-6 h-6" /></div>
            <h3 class="font-bold text-slate-800 mb-2">Post Jobs</h3>
            <p class="text-sm text-slate-500 mb-3"><b>Feature:</b> Employers post jobs with exact category icons and set per-interaction prices from {{ money(0.01) }}.</p>
            <p class="text-sm text-slate-500 mb-3"><b>Benefit:</b> Get real social proof and engagement at scale, cheaply.</p>
            <p class="text-sm text-slate-500"><b>How to use:</b> Dashboard → Create Task → pick category → fund → approve proofs.</p>
        </div>
        <div class="card p-6">
            <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center mb-4"><x-icon name="lock" class="w-6 h-6" /></div>
            <h3 class="font-bold text-slate-800 mb-2">KYC + Anti-Cheat</h3>
            <p class="text-sm text-slate-500 mb-3"><b>Feature:</b> KYC verification for withdrawals, one-account-per-IP enforcement, and view-manipulation prevention.</p>
            <p class="text-sm text-slate-500 mb-3"><b>Benefit:</b> A fair, trusted marketplace for workers and employers.</p>
            <p class="text-sm text-slate-500"><b>How to use:</b> Complete KYC in your wallet → withdraw safely anytime.</p>
        </div>
    </div>

    {{-- Advertisers tab --}}
    <div x-show="tab==='advertisers'" x-cloak class="grid md:grid-cols-3 gap-6">
        <div class="card p-6">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4"><x-icon name="target" class="w-6 h-6" /></div>
            <h3 class="font-bold text-slate-800 mb-2">Sponsored Ads</h3>
            <p class="text-sm text-slate-500 mb-3"><b>Feature:</b> Run text, image or video ads in the marketplace placements from just {{ money(0.02) }} per click.</p>
            <p class="text-sm text-slate-500 mb-3"><b>Benefit:</b> Reach a captive social audience with a clear "Sponsored" label and live stats.</p>
            <p class="text-sm text-slate-500"><b>How to use:</b> Switch to Advertiser → New Ad → set budget & CPC → track clicks.</p>
        </div>
        <div class="card p-6">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4"><x-icon name="refresh" class="w-6 h-6" /></div>
            <h3 class="font-bold text-slate-800 mb-2">Account-Type Switch</h3>
            <p class="text-sm text-slate-500 mb-3"><b>Feature:</b> Be a Freelancer, an Advertiser, or both — switch anytime from one account.</p>
            <p class="text-sm text-slate-500 mb-3"><b>Benefit:</b> Earn and advertise without managing separate logins.</p>
            <p class="text-sm text-slate-500"><b>How to use:</b> Settings → Account Type → choose role.</p>
        </div>
        <div class="card p-6">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-4"><x-verified-badge size="w-8 h-8" /></div>
            <h3 class="font-bold text-slate-800 mb-2">Blue Verification</h3>
            <p class="text-sm text-slate-500 mb-3"><b>Feature:</b> Get a blue badge with government ID + {{ money(\App\Services\SettingService::class ? app(\App\Services\SettingService::class)->activationFee() : 5) }}/month (30-day validity, auto-renew).</p>
            <p class="text-sm text-slate-500 mb-3"><b>Benefit:</b> Build trust and stand out as a verified creator or business.</p>
            <p class="text-sm text-slate-500"><b>How to use:</b> Identity → Blue Badge → upload ID → pay → get verified.</p>
        </div>
    </div>
</section>

<style>[x-cloak]{display:none!important}</style>

<!-- How it works -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
    <h2 class="text-2xl font-bold text-center text-slate-800 mb-2">How It Works</h2>
    <p class="text-center text-slate-500 mb-10">Three simple steps to start earning</p>
    <div class="grid md:grid-cols-3 gap-6">
        <div class="card text-center p-6">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">1</div>
            <h3 class="font-bold text-slate-800 mb-2">Register & Activate</h3>
            <p class="text-sm text-slate-500">Create your free account and pay the one-time {{ money(\App\Services\SettingService::class ? app(\App\Services\SettingService::class)->activationFee() : 5) }} activation fee to unlock the marketplace.</p>
        </div>
        <div class="card text-center p-6">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">2</div>
            <h3 class="font-bold text-slate-800 mb-2">Complete Tasks</h3>
            <p class="text-sm text-slate-500">Browse available microjobs, book a slot, complete the work, and submit your proof.</p>
        </div>
        <div class="card text-center p-6">
            <div class="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">3</div>
            <h3 class="font-bold text-slate-800 mb-2">Get Paid</h3>
            <p class="text-sm text-slate-500">Once the employer approves your proof, the funds are credited to your wallet instantly. Withdraw anytime.</p>
        </div>
    </div>
</section>

<!-- Categories -->
@if($categories->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <h2 class="text-2xl font-bold text-slate-800 mb-6">Browse by Category</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($categories as $cat)
        <a href="{{ route('browse', ['category'=>$cat->id]) }}" class="card p-5 hover:shadow-lg hover:-translate-y-1 transition text-center group">
            <div class="mx-auto mb-3 group-hover:scale-110 group-active:scale-95 transition-transform">@categoryIcon3D($cat, 52)</div>
            <p class="font-semibold text-slate-800 text-sm">{{ $cat->name }}</p>
        </a>
        @endforeach
    </div>
</section>
@endif

<!-- Live tasks -->
@if($liveTasks->isNotEmpty())
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Latest Tasks</h2>
        <a href="{{ route('browse') }}" class="text-blue-600 text-sm font-semibold hover:underline">View all <x-icon name="arrow-right" class="w-4 h-4 inline" /></a>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($liveTasks as $task)
        <div class="card p-5 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-2">
                <span class="badge badge-info inline-flex items-center gap-1.5">@if($task->category)<span class="inline-flex">@categoryIcon3D($task->category, 20)</span>@endif {{ $task->category?->name ?? 'General' }}</span>
                <span class="text-xs text-slate-400">{{ $task->booked }}/{{ $task->amount }} slots</span>
            </div>
            <h3 class="font-semibold text-slate-800 mb-1 line-clamp-2">{{ $task->title }}</h3>
            <p class="text-sm text-slate-500 line-clamp-2 mb-3">{{ $task->details }}</p>
            <div class="flex items-center justify-between">
                <span class="text-blue-600 font-bold">{{ money((float)$task->price) }}</span>
                @guest<a href="{{ route('register') }}" class="btn btn-primary text-xs">Sign up to view</a>@else<a href="{{ route('user.task',$task) }}" class="btn btn-primary text-xs">View Task</a>@endguest
            </div>
        </div>
        @endforeach
    </div>
</section>
@endif

<!-- CTA -->
<section class="bg-slate-900 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-14 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold mb-3">Ready to Start Earning?</h2>
        <p class="text-slate-400 mb-6">Join the marketplace today and turn your spare time into income.</p>
        @guest<a href="{{ route('register') }}" class="btn btn-primary">Create Free Account</a>@else<a href="{{ route('user.dashboard') }}" class="btn btn-primary">Go to Dashboard</a>@endguest
    </div>
</section>
@endsection
