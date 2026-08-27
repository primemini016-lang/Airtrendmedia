@extends('layouts.app')
@section('title', 'PTC Ads — Get Paid to View')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">PTC Ads — Paid To Click</h1>
            <p class="text-slate-500">Watch ads and earn instantly. Unlimited ads per day. Each 10 seconds = $0.005.</p>
        </div>
        <div class="flex gap-3 flex-wrap">
            <div class="card px-4 py-2 text-center">
                <div class="text-xs text-slate-400">Active Ads</div>
                <div class="text-lg font-bold text-blue-600">{{ number_format($totalAds) }}</div>
            </div>
            <div class="card px-4 py-2 text-center">
                <div class="text-xs text-slate-400">Your Views Today</div>
                <div class="text-lg font-bold text-green-600">{{ number_format($todayCount) }}</div>
            </div>
            <div class="card px-4 py-2 text-center">
                <div class="text-xs text-slate-400">Total Paid Out</div>
                <div class="text-lg font-bold text-emerald-600">${{ number_format((float)$totalPaid, 2) }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if(auth('web')->check())
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('user.ptc.create') }}" class="btn btn-primary">+ Create PTC Ad</a>
        <a href="{{ route('user.ptc.index') }}" class="btn btn-outline">My PTC Ads</a>
        <a href="{{ route('user.ptc.history') }}" class="btn btn-outline">My Earnings History</a>
    </div>
    @endif

    @if($ads->isNotEmpty())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($ads as $ad)
        <a href="{{ route('ptc.show', $ad) }}" class="card overflow-hidden hover:shadow-lg transition flex flex-col group">
            <div class="relative h-44 bg-gradient-to-br from-blue-500 to-indigo-600 overflow-hidden">
                @if($ad->image)
                    <img src="{{ $ad->imageUrl() }}" class="w-full h-full object-cover group-hover:scale-105 transition" alt="{{ $ad->title }}">
                @else
                    <div class="w-full h-full flex items-center justify-center text-white text-5xl font-bold opacity-90">{{ strtoupper(substr($ad->title,0,1)) }}</div>
                @endif
                <span class="absolute top-2 left-2 badge bg-green-500 text-white">{{ $ad->duration_seconds }}s</span>
                <span class="absolute top-2 right-2 badge bg-blue-600 text-white">+${{ number_format((float)$ad->reward_per_view, 4) }}</span>
            </div>
            <div class="p-4 flex flex-col flex-1">
                <h3 class="font-semibold text-slate-800 mb-1 line-clamp-2">{{ $ad->title }}</h3>
                <p class="text-sm text-slate-500 line-clamp-2 flex-1 mb-3">{{ Str::limit(strip_tags($ad->description ?? 'Click to view this ad and earn instantly.'), 90) }}</p>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs text-slate-400">
                    <span class="flex items-center gap-1"><x-icon name="view" class="w-3.5 h-3.5" /> {{ number_format($ad->views_count) }} views</span>
                    <span class="text-blue-600 font-semibold">Watch & Earn →</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $ads->links() }}</div>
    @else
    <div class="card p-12 text-center">
        <div class="text-5xl mb-4">📺</div>
        <h3 class="text-xl font-semibold text-slate-700 mb-2">No PTC ads available right now</h3>
        <p class="text-slate-400 mb-4">New ads are added frequently. Check back soon or create your own ad.</p>
        @if(auth('web')->check())
        <a href="{{ route('user.ptc.create') }}" class="btn btn-primary">Create a PTC Ad</a>
        @else
        <a href="{{ route('register') }}" class="btn btn-primary">Sign Up to Earn</a>
        @endif
    </div>
    @endif
</div>
@endsection
