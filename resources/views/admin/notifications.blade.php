@extends('layouts.admin')

@section('title', 'Notifications')
@section('heading', 'Notifications & Broadcasts')

@section('content')
<div class="mb-6">
    <p class="text-slate-500 text-sm">Send broadcast notifications to all users, or view recent notifications sent by the system.</p>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="card">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Broadcast Notification</h3>
            <form action="{{ route('admin.notifications.broadcast') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="input" placeholder="e.g. New Feature Live!" required>
                </div>
                <div class="mb-3">
                    <label class="label">Message <span class="text-red-500">*</span></label>
                    <textarea name="body" class="input" rows="4" placeholder="Write your notification message..." required></textarea>
                </div>
                <div class="mb-3">
                    <label class="label">Link URL (optional)</label>
                    <input type="text" name="url" class="input" placeholder="/dashboard">
                </div>
                <button class="btn btn-primary w-full">Send to All Users</button>
            </form>
        </div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-body">
            <h3 class="font-bold text-slate-800 mb-4">Recent Notifications</h3>
            @if($recentNotifications->isEmpty())
                <p class="text-slate-400 text-sm py-8 text-center">No notifications sent yet.</p>
            @else
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($recentNotifications as $n)
                    <div class="border border-slate-100 rounded-lg p-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-medium text-slate-800 text-sm">{{ $n->title }}</span>
                            <span class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ Str::limit($n->body, 100) }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge badge-muted text-[10px]">{{ ucfirst($n->type) }}</span>
                            @if($n->user)<span class="text-xs text-slate-400"><x-icon name="arrow-right" class="w-4 h-4 inline" /> {{ $n->user->username }}</span>@endif
                            @if($n->is_read)<span class="text-xs text-green-600">Read</span>@else<span class="text-xs text-blue-600">Unread</span>@endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
