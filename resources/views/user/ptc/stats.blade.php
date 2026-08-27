@extends('layouts.user')

@section('title', 'PTC Ad Stats')
@section('heading', 'Stats — ' . $ad->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('user.ptc.index') }}" class="text-blue-600 hover:underline text-sm mb-4 inline-block">← Back to My PTC Ads</a>

    <div class="card mb-5">
        <div class="card-body flex items-center gap-4">
            @if($ad->image)<img src="{{ $ad->imageUrl() }}" class="w-20 h-20 rounded-xl object-cover">@endif
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $ad->title }}</h2>
                <p class="text-sm text-slate-400">{{ $ad->duration_seconds }}s · ${{ number_format((float)$ad->reward_per_view,4) }} reward · ${{ number_format((float)$ad->cost_per_view,4) }} cost/view</p>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_views']) }}</div>
            <div class="text-xs text-slate-400">Total Views</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ number_format($stats['confirmed_views']) }}</div>
            <div class="text-xs text-slate-400">Confirmed Views</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">{{ number_format($stats['today_views']) }}</div>
            <div class="text-xs text-slate-400">Views Today</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-amber-600">{{ is_string($stats['remaining']) ? $stats['remaining'] : number_format($stats['remaining']) }}</div>
            <div class="text-xs text-slate-400">Remaining</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex justify-between items-center">
                <span class="text-slate-500">Total spent on rewards:</span>
                <span class="text-lg font-bold text-slate-800">${{ number_format((float)$stats['total_spent'], 4) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
