@extends('layouts.admin')

@section('title', 'Marketplace Management')
@section('heading', 'Marketplace Moderation')

@section('content')
<div class="mb-6 flex gap-2 flex-wrap">
    <a href="{{ route('admin.marketplace') }}" class="btn btn-outline text-xs {{ !request('status') ? 'btn-primary' : '' }}">All</a>
    <a href="{{ route('admin.marketplace', ['status' => 'pending']) }}" class="btn btn-outline text-xs {{ request('status') === 'pending' ? 'btn-primary' : '' }}">Pending</a>
    <a href="{{ route('admin.marketplace', ['status' => 'active']) }}" class="btn btn-outline text-xs {{ request('status') === 'active' ? 'btn-primary' : '' }}">Active</a>
    <a href="{{ route('admin.marketplace', ['status' => 'sold']) }}" class="btn btn-outline text-xs {{ request('status') === 'sold' ? 'btn-primary' : '' }}">Sold</a>
    <a href="{{ route('admin.marketplace', ['status' => 'rejected']) }}" class="btn btn-outline text-xs {{ request('status') === 'rejected' ? 'btn-primary' : '' }}">Rejected</a>
</div>

<div class="card">
    <div class="card-body">
        @if($listings->isEmpty())
            <p class="text-slate-400 text-sm py-8 text-center">No marketplace listings found.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500 text-xs uppercase">
                            <th class="py-3 px-2">Listing</th>
                            <th class="py-3 px-2">Seller</th>
                            <th class="py-3 px-2">Type</th>
                            <th class="py-3 px-2">Price</th>
                            <th class="py-3 px-2">Location</th>
                            <th class="py-3 px-2">Status</th>
                            <th class="py-3 px-2">Views</th>
                            <th class="py-3 px-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listings as $listing)
                        <tr class="border-b border-slate-100">
                            <td class="py-3 px-2">
                                <div class="flex items-center gap-2">
                                    @if($listing->image)<img src="{{ Storage::url($listing->image) }}" class="w-10 h-10 rounded-lg object-cover" alt="">@endif
                                    <div>
                                        <p class="font-medium text-slate-800">{{ Str::limit($listing->title, 40) }}</p>
                                        <p class="text-xs text-slate-400">{{ $listing->created_at->format('M j, Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-2 text-slate-600">{{ $listing->user?->username ?? '—' }}</td>
                            <td class="py-3 px-2"><span class="badge badge-info capitalize">{{ $listing->listing_type }}</span></td>
                            <td class="py-3 px-2 font-semibold text-slate-800">${{ number_format($listing->price, 2) }}</td>
                            <td class="py-3 px-2 text-slate-600">{{ $listing->location ?? '—' }}</td>
                            <td class="py-3 px-2">
                                @if($listing->status === 'active')<span class="badge badge-success">Active</span>
                                @elseif($listing->status === 'pending')<span class="badge badge-warning">Pending</span>
                                @elseif($listing->status === 'sold')<span class="badge badge-info">Sold</span>
                                @elseif($listing->status === 'rejected')<span class="badge badge-danger">Rejected</span>
                                @else<span class="badge badge-muted">{{ ucfirst($listing->status) }}</span>@endif
                            </td>
                            <td class="py-3 px-2 text-xs text-slate-500">{{ $listing->views }}</td>
                            <td class="py-3 px-2">
                                <div class="flex gap-1">
                                    @if($listing->status !== 'active')
                                    <form method="POST" action="{{ route('admin.marketplace.approve', $listing) }}">
                                        @csrf <button class="btn btn-success text-xs px-2 py-1">Approve</button>
                                    </form>
                                    @endif
                                    @if($listing->status !== 'rejected')
                                    <form method="POST" action="{{ route('admin.marketplace.reject', $listing) }}" onsubmit="return confirm('Reject this listing?')">
                                        @csrf
                                        <input type="hidden" name="reject_note" value="Does not meet listing guidelines.">
                                        <button class="btn btn-outline text-xs px-2 py-1">Reject</button>
                                    </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.marketplace.delete', $listing) }}" onsubmit="return confirm('Delete this listing permanently?')">
                                        @csrf @method('DELETE') <button class="btn btn-danger text-xs px-2 py-1">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $listings->links() }}</div>
        @endif
    </div>
</div>
@endsection
