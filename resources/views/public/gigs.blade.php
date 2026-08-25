@extends('layouts.app')
@section('title', 'Gigs — Freelance Services')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Gigs</h1>
    <p class="text-slate-500 mb-6">Browse freelance services offered by our skilled workers.</p>

    <!-- Filters -->
    <form method="GET" class="card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="label">Search</label>
            <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Search gigs...">
        </div>
        <div class="min-w-[150px]">
            <label class="label">Category</label>
            <select name="category" class="input">
                <option value="">All categories</option>
                @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(request('category')==$cat->id)>{{ $cat->name }}</option>@endforeach
            </select>
        </div>
        <div class="min-w-[150px]">
            <label class="label">Platform</label>
            <select name="platform" class="input">
                <option value="">All platforms</option>
                @foreach($platforms as $p)<option value="{{ $p }}" @selected(request('platform')==$p)>{{ ucfirst($p) }}</option>@endforeach
            </select>
        </div>
        <div class="min-w-[120px]">
            <label class="label">Max Price</label>
            <input type="number" name="max_price" value="{{ request('max_price') }}" class="input" placeholder="$">
        </div>
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('gigs.browse') }}" class="btn btn-outline">Reset</a>
    </form>

    @if($gigs->isNotEmpty())
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($gigs as $gig)
        <a href="{{ route('gigs.show', $gig) }}" class="card overflow-hidden hover:shadow-md transition flex flex-col">
            @if($gig->image)
                <img src="{{ Storage::url($gig->image) }}" class="w-full h-40 object-cover" alt="{{ $gig->title }}">
            @else
                <div class="w-full h-40 bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center text-4xl">
                    @if($gig->social_platform === 'facebook')<x-icon name="facebook" class="w-4 h-4 inline" />
                    @elseif($gig->social_platform === 'twitter')<x-icon name="twitter" class="w-4 h-4 inline" />
                    @elseif($gig->social_platform === 'instagram')<x-icon name="image" class="w-4 h-4 inline" />
                    @elseif($gig->social_platform === 'youtube')<x-icon name="youtube" class="w-4 h-4 inline" />
                    @elseif($gig->social_platform === 'tiktok')<x-icon name="music" class="w-4 h-4 inline" />
                    @elseif($gig->social_platform === 'linkedin')<x-icon name="briefcase" class="w-4 h-4 inline" />
                    @elseif($gig->social_platform === 'telegram')<x-icon name="telegram" class="w-4 h-4 inline" />
                    @elseif($gig->social_platform === 'whatsapp')<x-icon name="messages" class="w-4 h-4 inline" />
                    @else<x-icon name="briefcase" class="w-4 h-4 inline" />@endif
                </div>
            @endif
            <div class="p-4 flex flex-col flex-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="badge badge-info">{{ $gig->category?->name ?? 'General' }}</span>
                    @if($gig->social_platform)<span class="text-lg">{{ ucfirst($gig->social_platform) }}</span>@endif
                </div>
                <h3 class="font-semibold text-slate-800 mb-1 line-clamp-2">{{ $gig->title }}</h3>
                <p class="text-sm text-slate-500 line-clamp-2 flex-1 mb-3">{{ Str::limit(strip_tags($gig->description), 80) }}</p>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <span class="text-blue-600 font-bold text-lg">${{ number_format((float)$gig->price, 2) }}</span>
                    <span class="text-xs text-slate-400">{{ $gig->user?->username ?? 'Anonymous' }}</span>
                </div>
                <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                    <span class="flex items-center gap-1"><x-icon name="view" class="w-3.5 h-3.5" /> {{ number_format($gig->views ?? 0) }}</span>
                    <span class="flex items-center gap-1"><x-icon name="cart" class="w-3.5 h-3.5" /> {{ number_format($gig->sales ?? 0) }} sales</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $gigs->links() }}</div>
    @else
    <div class="card p-12 text-center text-slate-400">
        <p class="text-4xl mb-3"><x-icon name="briefcase" class="w-4 h-4 inline" /></p>
        <p>No gigs found yet. Check back soon!</p>
    </div>
    @endif
</div>
@endsection
