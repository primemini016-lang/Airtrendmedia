@extends('layouts.app')
@section('title', 'Marketplace — Buy & Sell')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Marketplace</h1>
    <p class="text-slate-500 mb-6">Buy and sell items, services, and digital products from our community.</p>

    <!-- Filters -->
    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Search listings...">
        </div>
        <div class="min-w-[150px]">
            <label class="label">Category</label>
            <select name="category" class="input">
                <option value="">All categories</option>
                @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(request('category')==$cat->id)>{{ $cat->name }}</option>@endforeach
            </select>
        </div>
        <div class="min-w-[150px]">
            <label class="label">Type</label>
            <select name="type" class="input">
                <option value="">All types</option>
                <option value="sell" @selected(request('type')=='sell')>For Sale</option>
                <option value="buy" @selected(request('type')=='buy')>Wanted</option>
            </select>
        </div>
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('marketplace') }}" class="btn btn-outline">Reset</a>
    </form>

    @if($listings->isNotEmpty())
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($listings as $listing)
        <a href="{{ route('marketplace.show', $listing) }}" class="card overflow-hidden hover:shadow-md transition flex flex-col">
            @if($listing->image)
                <img src="{{ Storage::url($listing->image) }}" class="w-full h-40 object-cover" alt="{{ $listing->title }}">
            @else
                <div class="w-full h-40 bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-4xl">🛒</div>
            @endif
            <div class="p-4 flex flex-col flex-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="badge badge-info">{{ $listing->category?->name ?? 'General' }}</span>
                    @if($listing->listing_type === 'buy')
                        <span class="badge badge-warning">Wanted</span>
                    @else
                        <span class="badge badge-success">For Sale</span>
                    @endif
                </div>
                <h3 class="font-semibold text-slate-800 mb-1 line-clamp-2">{{ $listing->title }}</h3>
                <p class="text-sm text-slate-500 line-clamp-2 flex-1 mb-3">{{ Str::limit(strip_tags($listing->description), 80) }}</p>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <span class="text-blue-600 font-bold text-lg">${{ number_format((float)$listing->price, 2) }}</span>
                    <span class="text-xs text-slate-400">{{ $listing->user?->username ?? 'Anonymous' }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $listings->links() }}</div>
    @else
    <div class="card p-12 text-center text-slate-400">
        <p class="text-4xl mb-3">🛒</p>
        <p>No listings found yet. Check back soon!</p>
    </div>
    @endif
</div>
@endsection
