@extends('layouts.admin')

@section('title', 'PTC Ad Details')
@section('heading', 'PTC Ad — ' . $ad->title)

@section('content')
<div class="max-w-4xl">
    <a href="{{ route('admin.ptc') }}" class="text-blue-600 hover:underline text-sm mb-4 inline-block">← Back to PTC Management</a>

    @if(session('success'))<div class="alert alert-success mb-4">{{ session('success') }}</div>@endif

    <div class="card mb-5">
        <div class="card-body">
            <div class="flex items-start gap-4">
                @if($ad->image)<img src="{{ $ad->imageUrl() }}" class="w-28 h-28 rounded-xl object-cover">@endif
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-slate-800">{{ $ad->title }}</h2>
                    <p class="text-sm text-slate-400 mb-2">by {{ $ad->user?->name }} ({{ $ad->user?->username }}) · {{ $ad->user?->email }}</p>
                    @if($ad->description)<p class="text-sm text-slate-600 mb-2">{!! nl2br(e($ad->description)) !!}</p>@endif
                    @if($ad->url)<p class="text-sm"><a href="{{ $ad->url }}" target="_blank" class="text-blue-600 hover:underline">{{ $ad->url }}</a></p>@endif
                    @if($ad->admin_note)<p class="text-sm text-red-600 mt-2">Admin note: {{ $ad->admin_note }}</p>@endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid sm:grid-cols-3 gap-4 mb-5">
        <div class="card p-4 text-center"><div class="text-xl font-bold text-blue-600">{{ $ad->duration_seconds }}s</div><div class="text-xs text-slate-400">Duration</div></div>
        <div class="card p-4 text-center"><div class="text-xl font-bold text-green-600">${{ number_format((float)$ad->reward_per_view,4) }}</div><div class="text-xs text-slate-400">Reward/View</div></div>
        <div class="card p-4 text-center"><div class="text-xl font-bold text-amber-600">${{ number_format((float)$ad->cost_per_view,4) }}</div><div class="text-xs text-slate-400">Cost/View</div></div>
    </div>

    <div class="flex gap-2 mb-6 flex-wrap">
        @if($ad->status==='pending')
        <form action="{{ route('admin.ptc.approve', $ad) }}" method="POST">@csrf<button class="btn btn-primary">Approve</button></form>
        <form action="{{ route('admin.ptc.reject', $ad) }}" method="POST">@csrf<input type="text" name="reason" placeholder="Rejection reason" class="input" required><button class="btn btn-danger">Reject</button></form>
        @elseif($ad->status==='approved')
        <form action="{{ route('admin.ptc.pause', $ad) }}" method="POST">@csrf<button class="btn btn-outline">Pause</button></form>
        @elseif($ad->status==='paused')
        <form action="{{ route('admin.ptc.resume', $ad) }}" method="POST">@csrf<button class="btn btn-primary">Resume</button></form>
        @endif
        <form action="{{ route('admin.ptc.delete', $ad) }}" method="POST" onsubmit="return confirm('Delete permanently?')">@csrf @method('DELETE')<button class="btn btn-danger">Delete</button></form>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 class="font-semibold text-slate-800 mb-3">Recent Views ({{ number_format($ad->views_count) }} total)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b text-left text-xs uppercase text-slate-500"><th class="py-2 px-2">User</th><th class="py-2 px-2">Reward</th><th class="py-2 px-2">Watched</th><th class="py-2 px-2">Status</th><th class="py-2 px-2">Confirmed</th></tr></thead>
                    <tbody>
                    @foreach($recentViews as $view)
                    <tr class="border-b border-slate-100">
                        <td class="py-2 px-2">{{ $view->user?->username ?? '—' }}</td>
                        <td class="py-2 px-2 text-green-600">${{ number_format((float)$view->reward,4) }}</td>
                        <td class="py-2 px-2">{{ $view->watched_seconds }}s</td>
                        <td class="py-2 px-2"><span class="badge {{ $view->status==='confirmed'?'badge-success':'badge-warning' }}">{{ $view->status }}</span></td>
                        <td class="py-2 px-2 text-slate-400">{{ optional($view->confirmed_at)->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
