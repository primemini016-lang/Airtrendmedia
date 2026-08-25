@extends('layouts.user')

@section('title', 'Notifications')
@section('heading', 'Notifications')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100">Your Notifications</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400">Stay updated on all your activities.</p>
    </div>
    <button onclick="markAllRead()" class="btn btn-outline text-xs">Mark All Read</button>
</div>

@if($notifications->isEmpty())
<div class="card p-12 text-center text-slate-400 dark:text-slate-500">
    <p class="text-4xl mb-3">🔔</p>
    <p>No notifications yet. You'll be notified about job posts, approvals, payments, and more.</p>
</div>
@else
<div class="space-y-3" id="notif-list">
    @foreach($notifications as $n)
    <div class="card {{ $n->is_read ? '' : 'border-l-4 border-l-blue-500' }}" data-id="{{ $n->id }}">
        <div class="card-body flex items-start gap-3">
            <div class="text-2xl shrink-0">
                @if($n->type === 'job')📋
                @elseif($n->type === 'payment')💵
                @elseif($n->type === 'message')💬
                @elseif($n->type === 'system')⚙️
                @else🔔@endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $n->title }}</p>
                    <span class="text-xs text-slate-400 shrink-0">{{ $n->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">{{ $n->body }}</p>
                <div class="flex items-center gap-3 mt-2">
                    @if($n->url)<a href="{{ $n->url }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View →</a>@endif
                    @if(!$n->is_read)<button onclick="markRead({{ $n->id }})" class="text-xs text-slate-400 hover:text-blue-600">Mark as read</button>@endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-6">{{ $notifications->links() }}</div>
@endif

@push('scripts')
<script>
function markRead(id) {
    fetch('/notifications/' + id + '/read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const el = document.querySelector('[data-id="' + id + '"]');
            if (el) el.classList.remove('border-l-4', 'border-l-blue-500');
            updateBadge();
        }
    });
}

function markAllRead() {
    fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.querySelectorAll('.border-l-blue-500').forEach(el => el.classList.remove('border-l-4', 'border-l-blue-500'));
            updateBadge();
        }
    });
}

function updateBadge() {
    fetch('/notifications/unread-count', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notif-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        });
}
</script>
@endpush
@endsection
