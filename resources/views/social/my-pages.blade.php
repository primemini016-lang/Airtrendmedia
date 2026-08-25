@extends('layouts.social')

@section('title', 'My Pages')

@section('main_class', 'max-w-[900px] mx-auto px-4 py-4')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">My Pages</h1>
    <a href="{{ route('social.pages.create') }}" class="fb-btn-primary px-4 py-2 rounded-lg font-medium flex items-center gap-2"><x-icon name="create" class="w-5 h-5" /> Create Page</a>
</div>

{{-- Owned pages --}}
<h2 class="font-bold text-lg mb-3">Pages You Manage</h2>
@if($ownedPages->isEmpty())
    <div class="fb-card p-6 text-center mb-6">
        <p class="fb-text-secondary text-sm">You don't manage any pages yet.</p>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-6">
        @foreach($ownedPages as $page)
            <div class="fb-card overflow-hidden">
                <a href="{{ route('social.page.show', $page) }}">
                    <img src="{{ $page->profile_image ? Storage::url($page->profile_image) : asset('images/default-page.png') }}" class="w-full h-32 object-cover" alt="{{ $page->name }}">
                </a>
                <div class="p-3">
                    <a href="{{ route('social.page.show', $page) }}" class="font-semibold text-sm truncate block">{{ $page->name }}</a>
                    <div class="text-xs fb-text-secondary mb-2">{{ $page->followers_count }} followers</div>
                    <a href="{{ route('social.page.edit', $page) }}" class="block w-full fb-btn-secondary py-1 rounded-md text-sm font-medium text-center">Manage</a>
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Joined pages --}}
<h2 class="font-bold text-lg mb-3">Pages You Follow</h2>
@if($joinedPages->isEmpty())
    <div class="fb-card p-6 text-center">
        <p class="fb-text-secondary text-sm">You don't follow any pages yet. <a href="{{ route('social.pages') }}" class="text-blue-600">Discover pages</a></p>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($joinedPages as $membership)
            <a href="{{ route('social.page.show', $membership->page) }}" class="fb-card overflow-hidden">
                <img src="{{ $membership->page->profile_image ? Storage::url($membership->page->profile_image) : asset('images/default-page.png') }}" class="w-full h-32 object-cover" alt="{{ $membership->page->name }}">
                <div class="p-3">
                    <div class="font-semibold text-sm truncate">{{ $membership->page->name }}</div>
                    <div class="text-xs fb-text-secondary">{{ $membership->page->followers_count }} followers</div>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
