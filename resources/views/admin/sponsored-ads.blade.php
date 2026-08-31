@extends('layouts.admin')

@section('title', 'Sponsored Ads')
@section('heading', 'Sponsored Ads Management')

@section('content')
<div class="space-y-6">

    {{-- Settings --}}
    <div class="grid sm:grid-cols-3 gap-4">
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Default CPC</p>
                <p class="stat-value text-blue-600">{{ money($defaultCpc) }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <p class="stat-label">Auto-Approval</p>
                <p class="stat-value @if($autoApprove) text-green-600 @else text-slate-500 @endif">{{ $autoApprove ? 'On' : 'Off' }}</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.sponsored-ads.settings') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="form-label text-xs">Default CPC ($)</label>
                        <input type="number" step="0.001" min="0.001" name="cpc" value="{{ number_format($defaultCpc, 4) }}" class="form-input text-sm">
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="auto_approval" value="1" @if($autoApprove) checked @endif>
                        Auto-approve ads
                    </label>
                    <button class="btn btn-primary text-xs w-full">Save</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.sponsored-ads') }}" class="badge {{ !request('status') ? 'badge-success' : 'badge-default' }}">All</a>
        <a href="{{ route('admin.sponsored-ads', ['status'=>'pending_review']) }}" class="badge {{ request('status')==='pending_review' ? 'badge-warning' : 'badge-default' }}">Pending</a>
        <a href="{{ route('admin.sponsored-ads', ['status'=>'active']) }}" class="badge {{ request('status')==='active' ? 'badge-success' : 'badge-default' }}">Active</a>
        <a href="{{ route('admin.sponsored-ads', ['status'=>'paused']) }}" class="badge {{ request('status')==='paused' ? 'badge-warning' : 'badge-default' }}">Paused</a>
        <a href="{{ route('admin.sponsored-ads', ['status'=>'rejected']) }}" class="badge {{ request('status')==='rejected' ? 'badge-danger' : 'badge-default' }}">Rejected</a>
        <a href="{{ route('admin.sponsored-ads', ['status'=>'budget_exhausted']) }}" class="badge {{ request('status')==='budget_exhausted' ? 'badge-danger' : 'badge-default' }}>Exhausted</a>
    </div>

    {{-- Ads table --}}
    <div class="card">
        <div class="card-body">
            @if($ads->isEmpty())
                <p class="text-center text-slate-400 py-8">No sponsored ads found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-400 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-2 pr-4">Advertiser</th>
                                <th class="pb-2 pr-4">Title</th>
                                <th class="pb-2 pr-4">Type</th>
                                <th class="pb-2 pr-4">Budget</th>
                                <th class="pb-2 pr-4">Spent</th>
                                <th class="pb-2 pr-4">Clicks</th>
                                <th class="pb-2 pr-4">Status</th>
                                <th class="pb-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ads as $ad)
                                <tr class="border-b border-slate-100 dark:border-slate-800">
                                    <td class="py-3 pr-4 text-slate-700 dark:text-slate-200">{{ $ad->advertiser->name ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-slate-600 dark:text-slate-300 max-w-xs truncate">{{ $ad->title }}</td>
                                    <td class="py-3 pr-4 text-slate-500 capitalize">{{ $ad->ad_type }}</td>
                                    <td class="py-3 pr-4 text-slate-600">{{ money((float)$ad->budget) }}</td>
                                    <td class="py-3 pr-4 text-green-600">{{ money((float)$ad->amount_spent) }}</td>
                                    <td class="py-3 pr-4 text-slate-600">{{ number_format($ad->clicks) }}</td>
                                    <td class="py-3 pr-4">
                                        @if($ad->status === 'active')<span class="badge badge-success">Active</span>
                                        @elseif($ad->status === 'pending_review')<span class="badge badge-warning">Pending</span>
                                        @elseif($ad->status === 'paused')<span class="badge badge-default">Paused</span>
                                        @elseif($ad->status === 'budget_exhausted')<span class="badge badge-danger">Exhausted</span>
                                        @else<span class="badge badge-danger">Rejected</span>@endif
                                    </td>
                                    <td class="py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @if($ad->status === 'pending_review')
                                                <form action="{{ route('admin.sponsored-ads.approve', $ad) }}" method="POST">@csrf<button class="text-green-600 text-xs hover:underline">Approve</button></form>
                                                <form action="{{ route('admin.sponsored-ads.reject', $ad) }}" method="POST">@csrf<input type="hidden" name="reason" value="Not approved by admin"><button class="text-red-600 text-xs hover:underline">Reject</button></form>
                                            @elseif($ad->status === 'active')
                                                <form action="{{ route('admin.sponsored-ads.pause', $ad) }}" method="POST">@csrf<button class="text-amber-600 text-xs hover:underline">Pause</button></form>
                                            @elseif($ad->status === 'paused')
                                                <form action="{{ route('admin.sponsored-ads.resume', $ad) }}" method="POST">@csrf<button class="text-green-600 text-xs hover:underline">Resume</button></form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $ads->links() }}
            @endif
        </div>
    </div>
</div>
@endsection
