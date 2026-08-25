@extends('layouts.social')

@section('title', 'Pages')

@section('main_class', 'max-w-[900px] mx-auto px-4 py-4')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold">Pages</h1>
        <p class="fb-text-secondary text-sm">Discover and connect with pages</p>
    </div>
    <a href="{{ route('social.pages.create') }}" class="fb-btn-primary px-4 py-2 rounded-lg font-medium flex items-center gap-2">
        <x-icon name="create" class="w-5 h-5" /> Create Page
    </a>
</div>

{{-- Search --}}
<form method="GET" class="mb-4">
    <div class="relative">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search pages..." class="fb-input py-2.5 pl-10">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 fb-text-secondary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </div>
</form>

{{-- My pages --}}
@if($myPages->isNotEmpty())
<div class="mb-6">
    <h2 class="font-bold text-lg mb-3">Your Pages</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($myPages as $page)
            <a href="{{ route('social.page.show', $page) }}" class="fb-card overflow-hidden hover:shadow-md transition">
                <img src="{{ $page->profile_image ? Storage::url($page->profile_image) : asset('images/default-page.png') }}" class="w-full h-32 object-cover" alt="{{ $page->name }}">
                <div class="p-3">
                    <div class="font-semibold text-sm truncate">{{ $page->name }}</div>
                    <div class="text-xs fb-text-secondary">{{ $page->followers_count }} followers</div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- Discover pages --}}
<h2 class="font-bold text-lg mb-3">Discover Pages</h2>
@if($pages->isEmpty())
    <div class="fb-card p-8 text-center">
        <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="pages" class="w-8 h-8" /></div>
        <h3 class="font-bold text-lg">No pages found</h3>
        <p class="fb-text-secondary text-sm mb-4">Be the first to create a page!</p>
        <a href="{{ route('social.pages.create') }}" class="inline-block fb-btn-primary px-6 py-2 rounded-lg font-medium">Create Page</a>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($pages as $page)
            <div class="fb-card overflow-hidden">
                <a href="{{ route('social.page.show', $page) }}">
                    <img src="{{ $page->profile_image ? Storage::url($page->profile_image) : asset('images/default-page.png') }}" class="w-full h-40 object-cover" alt="{{ $page->name }}">
                </a>
                <div class="p-3">
                    <a href="{{ route('social.page.show', $page) }}" class="font-semibold text-sm hover:underline block truncate">{{ $page->name }}</a>
                    @if($page->category)<div class="text-xs fb-text-secondary">{{ $page->category }}</div>@endif
                    <div class="text-xs fb-text-secondary">{{ $page->followers_count }} followers</div>
                    <button onclick="joinPage({{ $page->id }}, this)" class="w-full fb-btn-primary py-1.5 rounded-md text-sm font-medium mt-2">+ Follow</button>
                </div>
            </div>
        @endforeach
    </div>
    @if($pages->hasPages())<div class="text-center py-4">{{ $pages->links() }}</div>@endif
@endif
@endsection

@push('scripts')
<script>
function joinPage(pageId, btn) {
    fetch(`/social/pages/${pageId}/join`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).then(r => r.json()).then(data => {
        if (data.error) { alert(data.error); return; }
        btn.textContent = data.joined ? 'Following' : '+ Follow';
        btn.className = data.joined ? 'w-full fb-btn-secondary py-1.5 rounded-md text-sm font-medium mt-2' : 'w-full fb-btn-primary py-1.5 rounded-md text-sm font-medium mt-2';
    });
}
</script>
@endpush
