@extends('layouts.user')

@section('title', 'My PTC Ads')
@section('heading', 'My PTC Ad Campaigns')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-5">
        <h2 class="text-lg font-semibold text-slate-800">Your Campaigns</h2>
        <a href="{{ route('user.ptc.create') }}" class="btn btn-primary">+ Create PTC Ad</a>
    </div>

    @if(session('success'))<div class="alert alert-success mb-4">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger mb-4">{{ implode('<br>', $errors->all()) }}</div>@endif

    @if($ads->isNotEmpty())
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="text-left p-3">Ad</th>
                        <th class="text-left p-3">Duration</th>
                        <th class="text-left p-3">Reward/View</th>
                        <th class="text-left p-3">Views</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-right p-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($ads as $ad)
                    <tr>
                        <td class="p-3">
                            <div class="flex items-center gap-3">
                                @if($ad->image)<img src="{{ $ad->imageUrl() }}" class="w-10 h-10 rounded object-cover">@endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 dark:text-slate-100 truncate">{{ $ad->title }}</p>
                                    <p class="text-xs text-slate-400">{{ $ad->mode }} · ${{ number_format((float)$ad->cost_per_view,4) }}/view</p>
                                </div>
                            </div>
                        </td>
                        <td class="p-3">{{ $ad->duration_seconds }}s</td>
                        <td class="p-3 text-green-600 font-semibold">${{ number_format((float)$ad->reward_per_view,4) }}</td>
                        <td class="p-3">{{ number_format($ad->views_count) }} / @if($ad->max_views>0){{ number_format($ad->max_views) }}@else∞@endif</td>
                        <td class="p-3">
                            @if($ad->status==='approved')<span class="badge badge-success">Approved</span>
                            @elseif($ad->status==='pending')<span class="badge badge-warning">Pending</span>
                            @elseif($ad->status==='paused')<span class="badge badge-info">Paused</span>
                            @elseif($ad->status==='rejected')<span class="badge badge-danger" title="{{ $ad->admin_note }}">Rejected</span>
                            @elseif($ad->status==='completed')<span class="badge badge-secondary">Completed</span>
                            @endif
                        </td>
                        <td class="p-3 text-right whitespace-nowrap">
                            <a href="{{ route('user.ptc.stats', $ad) }}" class="text-blue-600 hover:underline text-xs">Stats</a>
                            @if(in_array($ad->status,['pending','rejected']))
                            <a href="{{ route('user.ptc.edit', $ad) }}" class="text-blue-600 hover:underline text-xs ml-2">Edit</a>
                            @endif
                            <form action="{{ route('user.ptc.destroy', $ad) }}" method="POST" class="inline" onsubmit="return confirm('Delete this PTC ad?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $ads->links() }}</div>
    @else
    <div class="card p-12 text-center">
        <div class="text-5xl mb-3">📺</div>
        <p class="text-slate-500 mb-4">You haven't created any PTC ads yet.</p>
        <a href="{{ route('user.ptc.create') }}" class="btn btn-primary">Create Your First PTC Ad</a>
    </div>
    @endif
</div>
@endsection
