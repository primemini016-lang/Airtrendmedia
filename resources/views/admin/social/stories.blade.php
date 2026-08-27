@extends('layouts.admin')
@section('title','Social Stories')
@section('heading','Stories Moderation')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2"><input class="input max-w-sm" name="q" value="{{ request('q') }}" placeholder="Search captions"><button class="btn btn-outline">Search</button></form>
    <a href="{{ route('admin.social.posts') }}" class="btn btn-outline">Social Posts</a>
</div>
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
@forelse($stories as $story)
<div class="card overflow-hidden">
@if($story->media_path)<div class="h-56 bg-slate-900">@if($story->media_type==='video')<video src="{{ $story->mediaUrl() }}" controls class="w-full h-full object-contain"></video>@else<img src="{{ $story->mediaUrl() }}" class="w-full h-full object-cover" alt="Story">@endif</div>@endif
<div class="card-body"><div class="font-semibold text-sm">{{ $story->user?->name ?? 'Unknown user' }}</div><p class="text-sm text-slate-600 dark:text-slate-300 mt-1 line-clamp-3">{{ $story->caption ?: 'No caption' }}</p><div class="text-xs text-slate-400 mt-2">{{ $story->views_count }} views · {{ $story->expires_at?->diffForHumans() }}</div>
<form method="POST" action="{{ route('admin.social.stories.delete',$story) }}" class="mt-3" onsubmit="return confirm('Permanently remove this story?')">@csrf @method('DELETE')<button class="btn btn-danger text-xs w-full"><x-icon name="trash" class="w-4 h-4"/> Remove Story</button></form></div>
</div>
@empty<div class="card sm:col-span-2 lg:col-span-3"><div class="card-body text-center text-slate-400">No stories found.</div></div>@endforelse
</div>
<div class="mt-5">{{ $stories->links() }}</div>
@endsection
