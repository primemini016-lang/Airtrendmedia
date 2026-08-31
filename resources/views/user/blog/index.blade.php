@extends('layouts.user')
@section('title', 'My Blog')
@section('heading', 'My Blog')
@section('content')
<div class="flex items-center justify-between gap-4 mb-6 flex-wrap">
    <div><h2 class="text-2xl font-black text-slate-900 dark:text-white">Creator Studio</h2><p class="text-sm text-slate-500">Write, publish and manage your articles.</p></div>
    <a href="{{ route('user.blog.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4"/> New article</a>
</div>
<div class="grid gap-4">
@forelse($posts as $post)
<div class="card"><div class="card-body flex items-center gap-4">
@if($post->featured_image)<img src="{{ storage_asset($post->featured_image) }}" class="w-20 h-16 rounded-xl object-cover">@else<div class="w-20 h-16 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600"><x-icon name="blog" class="w-7 h-7"/></div>@endif
<div class="min-w-0 flex-1"><h3 class="font-bold truncate">{{ $post->title }}</h3><p class="text-xs text-slate-500 mt-1">{{ ucfirst($post->status) }} · {{ $post->views_count }} views · {{ $post->likes_count }} likes</p></div>
<a href="{{ route('blog.show',$post->slug) }}" class="btn btn-outline text-xs">View</a><a href="{{ route('user.blog.edit',$post) }}" class="btn btn-outline text-xs"><x-icon name="edit" class="w-4 h-4"/></a>
<form method="POST" action="{{ route('user.blog.destroy',$post) }}" onsubmit="return confirm('Delete this article?')">@csrf @method('DELETE')<button class="btn btn-outline text-xs text-red-600"><x-icon name="trash" class="w-4 h-4"/></button></form>
</div></div>
@empty<div class="card"><div class="card-body text-center py-16"><x-icon name="blog" class="w-12 h-12 mx-auto text-slate-300 mb-3"/><h3 class="font-bold text-lg">Start your first article</h3><p class="text-sm text-slate-500 mb-5">Share ideas, tutorials, stories and useful content with the Airtrendmedia community.</p><a href="{{ route('user.blog.create') }}" class="btn btn-primary">Create article</a></div></div>@endforelse
</div>
<div class="mt-6">{{ $posts->links() }}</div>
@endsection
