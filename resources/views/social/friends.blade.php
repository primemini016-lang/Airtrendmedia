@extends('layouts.social')

@section('title', 'My Friends')

@section('main_class', 'max-w-[900px] mx-auto px-4 py-4')

@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-bold mb-1">Friends</h1>
    <p class="fb-text-secondary text-sm">{{ $friends->total() }} friends (mutual follows)</p>
</div>

@if($friends->isEmpty())
    <div class="fb-card p-8 text-center">
        <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="friends" class="w-8 h-8" /></div>
        <h3 class="font-bold text-lg">No friends yet</h3>
        <p class="fb-text-secondary text-sm mb-4">Follow people who follow you back to become friends.</p>
        <a href="{{ route('social.suggestions') }}" class="inline-block fb-btn-primary px-6 py-2 rounded-lg font-medium">Find People</a>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($friends as $friend)
            <div class="fb-card overflow-hidden">
                <a href="{{ route('social.profile', $friend->username) }}">
                    <img src="{{ $friend->avatarUrl() }}" class="w-full h-40 object-cover" alt="{{ $friend->name }}">
                </a>
                <div class="p-3">
                    <a href="{{ route('social.profile', $friend->username) }}" class="font-semibold text-sm hover:underline block truncate">{{ $friend->name }}</a>
                    <div class="text-xs fb-text-secondary mb-2">{{ $friend->followers_count }} followers</div>
                    <a href="{{ route('social.chat') }}?user={{ $friend->id }}" class="block w-full fb-btn-secondary py-1.5 rounded-md text-sm font-medium text-center">Message</a>
                </div>
            </div>
        @endforeach
    </div>
    @if($friends->hasPages())<div class="text-center py-4">{{ $friends->links() }}</div>@endif
@endif
@endsection
