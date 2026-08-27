@extends('layouts.user')

@section('title', 'Ad Statistics')
@section('heading', 'Ad Statistics')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('user.sponsored-ads') }}" class="btn btn-ghost text-sm">← Back</a>
    </div>

    {{-- Ad summary --}}
    <div class="card">
        <div class="card-body">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <span class="badge @if($ad->status==='active') badge-success @elseif($ad->status==='pending_review') badge-warning @elseif($ad->status==='rejected') badge-danger @else badge-default @endif">{{ ucfirst(str_replace('_',' ',$ad->status)) }}</span>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mt-2">{{ $ad->title }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $ad->description }}</p>
                    <a href="{{ $ad->link_url }}" target="_blank" class="text-sm text-blue-600 hover:underline mt-2 inline-block">{{ $ad->link_url }}</a>
                </div>
                @if($ad->media_path)
                    <img src="{{ Storage::url($ad->media_path) }}" class="w-32 h-32 object-cover rounded-lg flex-shrink-0">
                @endif
            </div>
        </div>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
            <div class="card-body text-center">
                <p class="stat-label">Impressions</p>
                <p class="stat-value text-blue-600">{{ number_format($ad->impressions) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <p class="stat-label">Clicks</p>
                <p class="stat-value text-green-600">{{ number_format($ad->clicks) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <p class="stat-label">Spent</p>
                <p class="stat-value text-amber-600">${{ number_format($ad->amount_spent, 2) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body text-center">
                <p class="stat-label">CTR</p>
                <p class="stat-value text-purple-600">{{ $ad->impressions > 0 ? number_format($ad->clicks / $ad->impressions * 100, 2) : '0.00' }}%</p>
            </div>
        </div>
    </div>

    {{-- Budget progress --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Budget Usage</h3>
            @php $pct = $ad->budget > 0 ? min(100, (float)$ad->amount_spent / (float)$ad->budget * 100) : 0; @endphp
            <div class="h-4 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-end pr-2" style="width: {{ max($pct, 5) }}%">
                    <span class="text-xs text-white font-bold">{{ number_format($pct, 0) }}%</span>
                </div>
            </div>
            <div class="flex justify-between text-sm text-slate-500 mt-2">
                <span>Spent: ${{ number_format($ad->amount_spent, 2) }}</span>
                <span>Budget: ${{ number_format($ad->budget, 2) }}</span>
                <span>Remaining: ${{ number_format(max(0, (float)$ad->budget - (float)$ad->amount_spent), 2) }}</span>
            </div>
            <div class="text-xs text-slate-400 mt-1">Cost per click: ${{ number_format($ad->cost_per_click, 4) }}</div>
        </div>
    </div>

    {{-- Recent clicks --}}
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 dark:text-slate-100 mb-3">Recent Clicks</h3>
            @if($recentClicks->isEmpty())
                <p class="text-center text-slate-400 py-6">No clicks recorded yet.</p>
            @else
                <div class="space-y-2">
                    @foreach($recentClicks as $click)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-700/30 text-sm">
                            <span class="text-slate-600 dark:text-slate-300">User #{{ $click->user_id ?? 'Anonymous' }}</span>
                            <span class="text-xs text-slate-400">{{ $click->ip_address }} · {{ $click->created_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
