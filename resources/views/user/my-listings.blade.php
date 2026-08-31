@extends('layouts.user')

@section('title', 'My Listings')
@section('heading', 'My Marketplace Listings')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">My Listings</h2>
            <p class="text-slate-500 text-sm">Manage the items and services you're selling.</p>
        </div>
        <a href="{{ route('user.marketplace.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Create New Listing</a>
    </div>

    @include('partials.alerts')

    @if($listings->isNotEmpty())
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($listings as $listing)
        <div class="card overflow-hidden flex flex-col">
            @if($listing->image)
                <img src="{{ storage_asset($listing->image) }}" class="w-full h-36 object-cover" alt="{{ $listing->title }}">
            @else
                <div class="w-full h-36 bg-gradient-to-br from-emerald-100 to-teal-200 flex items-center justify-center">
                    <x-icon name="marketplace" class="w-10 h-10 text-emerald-400" />
                </div>
            @endif
            <div class="p-4 flex flex-col flex-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="badge badge-info">{{ ucfirst($listing->listing_type) }}</span>
                    @if($listing->status === 'active')<span class="badge badge-success">Active</span>
                    @elseif($listing->status === 'pending')<span class="badge badge-warning">Pending</span>
                    @elseif($listing->status === 'sold')<span class="badge badge-muted">Sold</span>
                    @elseif($listing->status === 'rejected')<span class="badge badge-danger">Rejected</span>
                    @endif
                </div>
                <h3 class="font-semibold text-slate-800 dark:text-slate-100 line-clamp-2 mb-1">{{ $listing->title }}</h3>
                <p class="text-xs text-slate-400 line-clamp-2 mb-3">{{ $listing->description }}</p>
                @if($listing->location)<p class="text-xs text-slate-400 mb-2 flex items-center gap-1"><x-icon name="location" class="w-3.5 h-3.5" /> {{ $listing->location }}</p>@endif
                <div class="flex items-center justify-between mt-auto pt-3 border-t border-slate-100 dark:border-slate-700">
                    <span class="text-emerald-600 font-bold">{{ money((float)$listing->price) }}</span>
                    <div class="flex gap-2">
                        <a href="{{ route('user.marketplace.edit', $listing) }}" class="btn btn-outline text-xs px-3 py-1.5"><x-icon name="edit" class="w-3.5 h-3.5" /> Edit</a>
                        @if(in_array($listing->status, ['active','sold']))
                            <a href="{{ route('marketplace.show', $listing) }}" target="_blank" class="btn btn-outline text-xs px-3 py-1.5"><x-icon name="eye" class="w-3.5 h-3.5" /> View</a>
                        @endif
                    </div>
                </div>
                <form action="{{ route('user.marketplace.destroy', $listing) }}" method="POST" class="mt-2" onsubmit="return confirm('Delete this listing permanently?')">
                    @csrf @method('DELETE')
                    <button class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1"><x-icon name="trash" class="w-3.5 h-3.5" /> Delete</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $listings->links() }}</div>
    @else
    <div class="card">
        <div class="card-body text-center py-12">
            <x-icon name="marketplace" class="w-12 h-12 mx-auto text-slate-300 mb-3" />
            <p class="text-slate-500 mb-4">You haven't listed anything yet.</p>
            <a href="{{ route('user.marketplace.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Create Your First Listing</a>
        </div>
    </div>
    @endif
</div>
@endsection
