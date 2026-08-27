@extends('layouts.user')

@section('title', 'PTC Earnings History')
@section('heading', 'My PTC Earnings')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="grid sm:grid-cols-2 gap-4 mb-6">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-green-600">${{ number_format((float)$todayEarnings, 4) }}</div>
            <div class="text-xs text-slate-400">Earned Today</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">${{ number_format((float)$totalEarnings, 4) }}</div>
            <div class="text-xs text-slate-400">Total PTC Earnings</div>
        </div>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-semibold text-slate-800">History</h2>
        <a href="{{ route('ptc.index') }}" class="btn btn-primary">Watch More Ads</a>
    </div>

    @if($views->isNotEmpty())
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="text-left p-3">Ad</th>
                        <th class="text-left p-3">Duration</th>
                        <th class="text-left p-3">Reward</th>
                        <th class="text-left p-3">Confirmed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($views as $view)
                    <tr>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                @if($view->ad?->image)<img src="{{ $view->ad->imageUrl() }}" class="w-8 h-8 rounded object-cover">@endif
                                <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $view->ad?->title ?? 'Removed ad' }}</span>
                            </div>
                        </td>
                        <td class="p-3">{{ $view->watched_seconds }}s</td>
                        <td class="p-3 text-green-600 font-semibold">${{ number_format((float)$view->reward, 4) }}</td>
                        <td class="p-3 text-slate-400">{{ optional($view->confirmed_at)->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $views->links() }}</div>
    @else
    <div class="card p-12 text-center">
        <div class="text-5xl mb-3">💰</div>
        <p class="text-slate-500 mb-4">You haven't earned from PTC ads yet.</p>
        <a href="{{ route('ptc.index') }}" class="btn btn-primary">Start Watching Ads</a>
    </div>
    @endif
</div>
@endsection
