@extends('layouts.app')
@section('title', $listing->title . ' — Marketplace')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <a href="{{ route('marketplace') }}" class="text-sm text-blue-600 hover:underline mb-4 inline-block"><x-icon name="arrow-left" class="w-4 h-4 inline" /> Back to Marketplace</a>

    <div class="grid lg:grid-cols-2 gap-8">
        <!-- Image -->
        <div>
            @if($listing->image)
                <img src="{{ storage_asset($listing->image) }}" class="w-full rounded-xl shadow-md object-cover max-h-96" alt="{{ $listing->title }}">
            @else
                <div class="w-full h-96 rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-6xl"><x-icon name="marketplace" class="w-4 h-4 inline" /></div>
            @endif
            @if($listing->gallery && is_array($listing->gallery))
                <div class="grid grid-cols-4 gap-2 mt-3">
                    @foreach($listing->gallery as $img)
                        <img src="{{ storage_asset($img) }}" class="w-full h-20 rounded-lg object-cover cursor-pointer" alt="">
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Details -->
        <div>
            <div class="flex items-center gap-2 mb-3 flex-wrap">
                <span class="badge badge-info">{{ $listing->category?->name ?? 'General' }}</span>
                @if($listing->listing_type === 'buy')
                    <span class="badge badge-warning">Wanted</span>
                @else
                    <span class="badge badge-success">For Sale</span>
                @endif
                @if($listing->status === 'sold')<span class="badge badge-danger">Sold</span>@endif
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-3">{{ $listing->title }}</h1>
            <p class="text-3xl font-bold text-blue-600 mb-4">{{ money((float)$listing->price) }}</p>

            @if($listing->location)
            <p class="text-sm text-slate-500 mb-3"><x-icon name="bookmark" class="w-4 h-4 inline" /> {{ $listing->location }}</p>
            @endif

            <div class="card p-4 mb-4">
                <h3 class="font-semibold text-slate-800 mb-2">Description</h3>
                <p class="text-sm text-slate-600 whitespace-pre-line">{{ $listing->description }}</p>
            </div>

            <!-- Seller info -->
            <div class="card p-4 mb-4">
                <div class="flex items-center gap-3">
                    @if($listing->user?->image)<img src="{{ storage_asset($listing->user->image) }}" class="w-12 h-12 rounded-full object-cover" alt="">@else<div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">{{ strtoupper(substr($listing->user?->username ?? 'U',0,1)) }}</div>@endif
                    <div>
                        <p class="font-semibold text-slate-800">
                            @if($listing->user)
                                <a href="{{ route('user.public-profile', $listing->user) }}" class="hover:underline">{{ $listing->user->name }}</a>
                                @if($listing->user->isBlueVerified())<x-verified-badge size="w-4 h-4 inline" class="align-middle" />@endif
                            @else
                                Anonymous
                            @endif
                        </p>
                        <p class="text-xs text-slate-400 flex items-center gap-3">
                            <span>{{ $listing->created_at->format('M j, Y') }}</span>
                            <span class="flex items-center gap-1"><x-icon name="view" class="w-3.5 h-3.5" /> {{ $listing->views }} views</span>
                        </p>
                    </div>
                </div>
            </div>

            @auth
                @if($listing->status === 'active' && $listing->user_id !== auth('web')->id())
                <button class="btn btn-primary w-full text-lg py-3" onclick="document.getElementById('inquiry-form').classList.toggle('hidden')">Contact Seller</button>
                <form id="inquiry-form" class="card p-4 mt-4 hidden" method="POST" action="{{ route('marketplace.show', $listing) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="label">Your Message</label>
                        <textarea name="message" class="input" rows="3" required placeholder="I'm interested in this listing..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="label">Your Offer ($)</label>
                        <input type="number" name="offer_price" class="input" step="0.01" placeholder="{{ $listing->price }}">
                    </div>
                    <button class="btn btn-primary w-full">Send Inquiry</button>
                </form>
                @elseif($listing->user_id === auth('web')->id())
                    <p class="text-sm text-slate-500 text-center py-3">This is your listing.</p>
                @endif
            @else
                <a href="{{ route('register') }}" class="btn btn-primary w-full text-lg py-3">Sign Up to Contact Seller</a>
            @endauth
        </div>
    </div>

    {{-- Ratings & Reviews --}}
    @include('partials.reviews', ['reviewType' => 'listing', 'reviewTarget' => $listing, 'reviews' => $reviews, 'myReview' => $myReview ?? null])

    {{-- Comments --}}
    <section class="card mt-6 p-5">
        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2"><x-icon name="comment" class="w-5 h-5 text-blue-500" /> Comments <span class="text-sm font-normal text-slate-400">({{ $listing->comment_count }})</span></h3>

        @auth('web')
        <form action="{{ route('comments.store') }}" method="POST" class="mb-5">
            @csrf
            <input type="hidden" name="type" value="listing">
            <input type="hidden" name="id" value="{{ $listing->id }}">
            <textarea name="body" rows="2" class="form-input w-full" placeholder="Write a comment..." required></textarea>
            <button type="submit" class="btn btn-primary mt-2">Post Comment</button>
        </form>
        @endauth

        @if($comments->isNotEmpty())
            <div class="space-y-4">
                @foreach($comments as $comment)
                    <div class="flex gap-3">
                        <img src="{{ $comment->user?->avatarUrl() }}" class="w-9 h-9 rounded-full object-cover" alt="">
                        <div class="flex-1">
                            <div class="bg-slate-50 rounded-xl px-3 py-2">
                                <div class="font-semibold text-slate-700 text-sm">
                                    @if($comment->user)
                                        <a href="{{ route('user.public-profile', $comment->user) }}" class="hover:underline">{{ $comment->user->name }}</a>
                                        @if($comment->user->isBlueVerified())<x-verified-badge size="w-4 h-4 inline" class="align-middle" />@endif
                                    @else
                                        Guest
                                    @endif
                                </div>
                                <p class="text-sm text-slate-600 mt-1">{{ $comment->body }}</p>
                            </div>
                            <div class="text-xs text-slate-400 mt-1">{{ $comment->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-400 text-center py-4">No comments yet.</p>
        @endif
    </section>

    <!-- Related -->
    @if($related->isNotEmpty())
    <div class="mt-12">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Related Listings</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($related as $item)
            <a href="{{ route('marketplace.show', $item) }}" class="card overflow-hidden hover:shadow-md transition">
                @if($item->image)<img src="{{ storage_asset($item->image) }}" class="w-full h-32 object-cover" alt="">@else<div class="w-full h-32 bg-blue-100 flex items-center justify-center text-3xl"><x-icon name="marketplace" class="w-4 h-4 inline" /></div>@endif
                <div class="p-3">
                    <h3 class="font-medium text-slate-800 text-sm line-clamp-1">{{ $item->title }}</h3>
                    <p class="text-blue-600 font-bold mt-1">{{ money((float)$item->price) }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
