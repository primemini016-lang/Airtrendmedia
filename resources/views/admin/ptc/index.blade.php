@extends('layouts.admin')

@section('title', 'PTC Ads Management')
@section('heading', 'PTC Ads — God Mode')

@section('content')
<div class="mb-6 flex gap-2 flex-wrap">
    <a href="{{ route('admin.ptc') }}" class="btn btn-outline text-xs {{ !request('status') ? 'btn-primary' : '' }}">All</a>
    @foreach(['pending'=>'Pending','approved'=>'Approved','paused'=>'Paused','rejected'=>'Rejected','completed'=>'Completed'] as $key=>$label)
    <a href="{{ route('admin.ptc', ['status' => $key]) }}" class="btn btn-outline text-xs {{ request('status') === $key ? 'btn-primary' : '' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
    @endforeach
    <a href="{{ route('admin.ptc.settings') }}" class="btn btn-outline text-xs ml-auto">⚙ PTC Settings</a>
</div>

@if(session('success'))<div class="alert alert-success mb-4">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-body">
        @if($ads->isEmpty())
            <p class="text-slate-400 text-sm py-8 text-center">No PTC ads found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500 text-xs uppercase">
                            <th class="py-3 px-2">Ad</th>
                            <th class="py-3 px-2">Advertiser</th>
                            <th class="py-3 px-2">Duration</th>
                            <th class="py-3 px-2">Cost/View</th>
                            <th class="py-3 px-2">Reward/View</th>
                            <th class="py-3 px-2">Views</th>
                            <th class="py-3 px-2">Mode</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 px-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ads as $ad)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-2">
                                <div class="flex items-center gap-2">
                                    @if($ad->image)<img src="{{ $ad->imageUrl() }}" class="w-9 h-9 rounded object-cover">@endif
                                    <a href="{{ route('admin.ptc.show', $ad) }}" class="font-semibold text-slate-800 hover:underline">{{ $ad->title }}</a>
                                </div>
                            </td>
                            <td class="py-3 px-2">{{ $ad->user?->username ?? '—' }}</td>
                            <td class="py-3 px-2">{{ $ad->duration_seconds }}s</td>
                            <td class="py-3 px-2">${{ number_format((float)$ad->cost_per_view,4) }}</td>
                            <td class="py-3 px-2 text-green-600">${{ number_format((float)$ad->reward_per_view,4) }}</td>
                            <td class="py-3 px-2">{{ number_format($ad->views_count) }}/@if($ad->max_views>0){{ number_format($ad->max_views) }}@else∞@endif</td>
                            <td class="py-3 px-2"><span class="badge {{ $ad->mode==='automatic'?'badge-success':'badge-info' }}">{{ $ad->mode }}</span></td>
                            <td class="py-3 px-2">
                                @if($ad->status==='approved')<span class="badge badge-success">Approved</span>
                                @elseif($ad->status==='pending')<span class="badge badge-warning">Pending</span>
                                @elseif($ad->status==='paused')<span class="badge badge-info">Paused</span>
                                @elseif($ad->status==='rejected')<span class="badge badge-danger">Rejected</span>
                                @elseif($ad->status==='completed')<span class="badge badge-secondary">Completed</span>
                                @endif
                            </td>
                            <td class="py-3 px-2 text-right whitespace-nowrap">
                                @if($ad->status==='pending')
                                <form action="{{ route('admin.ptc.approve', $ad) }}" method="POST" class="inline">@csrf<button class="text-green-600 hover:underline text-xs">Approve</button></form>
                                <form action="{{ route('admin.ptc.reject', $ad) }}" method="POST" class="inline">@csrf<button class="text-red-600 hover:underline text-xs">Reject</button></form>
                                @elseif($ad->status==='approved')
                                <form action="{{ route('admin.ptc.pause', $ad) }}" method="POST" class="inline">@csrf<button class="text-amber-600 hover:underline text-xs">Pause</button></form>
                                @elseif($ad->status==='paused')
                                <form action="{{ route('admin.ptc.resume', $ad) }}" method="POST" class="inline">@csrf<button class="text-green-600 hover:underline text-xs">Resume</button></form>
                                @endif
                                <form action="{{ route('admin.ptc.delete', $ad) }}" method="POST" class="inline" onsubmit="return confirm('Delete this PTC ad permanently?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-xs">Delete</button></form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $ads->links() }}</div>
        @endif
    </div>
</div>
@endsection
