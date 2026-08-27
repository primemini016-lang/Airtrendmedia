@extends('layouts.app')
@section('title', $gig->title . ' — Gig')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <a href="{{ route('gigs.browse') }}" class="text-sm text-blue-600 hover:underline mb-4 inline-block"><x-icon name="arrow-left" class="w-4 h-4 inline" /> Back to Gigs</a>

    <div class="grid lg:grid-cols-2 gap-8">
        <!-- Image -->
        <div>
            @if($gig->image)
                <img src="{{ Storage::url($gig->image) }}" class="w-full rounded-xl shadow-md object-cover max-h-96" alt="{{ $gig->title }}">
            @else
                <div class="w-full h-96 rounded-xl bg-gradient-to-br from-blue-100 to-indigo-200 flex items-center justify-center text-6xl">
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
            @if($gig->gallery && is_array($gig->gallery))
                <div class="grid grid-cols-4 gap-2 mt-3">
                    @foreach($gig->gallery as $img)
                        <img src="{{ Storage::url($img) }}" class="w-full h-20 rounded-lg object-cover cursor-pointer" alt="">
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Details -->
        <div>
            <div class="flex items-center gap-2 mb-3 flex-wrap">
                <span class="badge badge-info">{{ $gig->category?->name ?? 'General' }}</span>
                @if($gig->social_platform)<span class="badge badge-muted capitalize">{{ $gig->social_platform }}</span>@endif
                <span class="badge badge-success">Active</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $gig->title }}</h1>
            <p class="text-3xl font-bold text-blue-600 mb-4">${{ number_format((float)$gig->price, 2) }}</p>

            @if($gig->social_url)
            <a href="{{ $gig->social_url }}" target="_blank" class="text-sm text-blue-600 hover:underline mb-3 inline-block"><x-icon name="link" class="w-4 h-4 inline" /> View social profile</a>
            @endif

            <div class="card p-4 mb-4">
                <h3 class="font-semibold text-slate-800 mb-2">Description</h3>
                <p class="text-sm text-slate-600 whitespace-pre-line">{{ $gig->description }}</p>
            </div>

            <!-- Seller info -->
            <div class="card p-4 mb-4">
                <div class="flex items-center gap-3">
                    @if($gig->user?->image)<img src="{{ Storage::url($gig->user->image) }}" class="w-12 h-12 rounded-full object-cover" alt="">@else<div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">{{ strtoupper(substr($gig->user?->username ?? 'U',0,1)) }}</div>@endif
                    <div>
                        <p class="font-semibold text-slate-800">{{ $gig->user?->username ?? 'Anonymous' }}</p>
                        <p class="text-xs text-slate-400 flex items-center gap-3">
                            <span class="flex items-center gap-1"><x-icon name="cart" class="w-3.5 h-3.5" /> {{ $gig->sales }} sales</span>
                            <span class="flex items-center gap-1"><x-icon name="view" class="w-3.5 h-3.5" /> {{ $gig->views }} views</span>
                        </p>
                    </div>
                </div>
            </div>

            @auth
                @if($gig->user_id !== auth('web')->id())
                <a href="{{ route('user.dashboard') }}" class="btn btn-primary w-full text-lg py-3">Order This Gig</a>
                @else
                <p class="text-sm text-slate-500 text-center py-3">This is your gig.</p>
                @endif
            @else
            <a href="{{ route('register') }}" class="btn btn-primary w-full text-lg py-3">Sign Up to Order</a>
            @endauth
        </div>
    </div>

    {{-- Ratings & Reviews --}}
    @include('partials.reviews', ['reviewType' => 'gig', 'reviewTarget' => $gig, 'reviews' => $reviews, 'myReview' => $myReview ?? null])

    <!-- Related -->
    @if($related->isNotEmpty())
    <div class="mt-12">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Related Gigs</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($related as $item)
            <a href="{{ route('gigs.show', $item) }}" class="card overflow-hidden hover:shadow-md transition">
                @if($item->image)<img src="{{ Storage::url($item->image) }}" class="w-full h-32 object-cover" alt="">@else<div class="w-full h-32 bg-blue-100 flex items-center justify-center text-3xl"><x-icon name="briefcase" class="w-4 h-4 inline" /></div>@endif
                <div class="p-3">
                    <h3 class="font-medium text-slate-800 text-sm line-clamp-1">{{ $item->title }}</h3>
                    <p class="text-blue-600 font-bold mt-1">${{ number_format((float)$item->price, 2) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
