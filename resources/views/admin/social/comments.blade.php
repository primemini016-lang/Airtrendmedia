@extends('layouts.admin')
@section('title','Social Comments')
@section('heading','Social Comments')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <form method="GET" class="flex gap-2"><input class="input max-w-sm" name="q" value="{{ request('q') }}" placeholder="Search comments or users"><button class="btn btn-outline">Search</button></form>
    <a href="{{ route('admin.social.posts') }}" class="btn btn-outline"><x-icon name="feed" class="w-4 h-4"/> Posts</a>
</div>
<div class="space-y-3">
@forelse($comments as $comment)
<div class="card"><div class="card-body flex gap-3 items-start">
<div class="w-9 h-9 rounded-full auth-gradient flex items-center justify-center text-white text-xs font-bold flex-shrink-0">{{ strtoupper(substr($comment->user?->name ?? 'U',0,1)) }}</div>
<div class="flex-1 min-w-0"><div class="text-sm font-semibold">{{ $comment->user?->name ?? 'Unknown user' }} <span class="text-xs text-slate-400 font-normal">· {{ $comment->created_at?->diffForHumans() }}</span></div>
<p class="text-sm text-slate-700 dark:text-slate-200 mt-1 break-words">{{ $comment->body ?: 'Media comment' }}</p>
<p class="text-xs text-slate-400 mt-2">Post #{{ $comment->post_id }} · {{ $comment->likes_count }} likes · {{ $comment->replies_count }} replies</p></div>
<form method="POST" action="{{ route('admin.social.comments.delete',$comment) }}" onsubmit="return confirm('Remove this comment and its replies?')">@csrf @method('DELETE')<button class="btn btn-danger text-xs"><x-icon name="trash" class="w-4 h-4"/> Remove</button></form>
</div></div>
@empty<div class="card"><div class="card-body text-center text-slate-400">No comments found.</div></div>@endforelse
</div>
<div class="mt-5">{{ $comments->links() }}</div>
@endsection
