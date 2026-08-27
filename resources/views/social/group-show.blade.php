@extends('layouts.social')

@section('title', $group->name)

@section('main_class', 'max-w-[900px] mx-auto px-0 sm:px-4 py-0')

@section('content')
<div class="fb-card mb-4 overflow-hidden">
    <div class="relative h-[200px] sm:h-[300px] bg-gradient-to-r from-green-500 to-teal-600">
        @if($group->cover_image)
            <img src="{{ Storage::url($group->cover_image) }}" class="w-full h-full object-cover" alt="Cover">
        @endif
    </div>
    <div class="px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3 -mt-12">
            <img src="{{ $group->profile_image ? Storage::url($group->profile_image) : asset('images/default-group.png') }}" class="w-28 h-28 sm:w-36 sm:h-36 rounded-lg border-4 object-cover" style="border-color: var(--fb-card);" alt="{{ $group->name }}">
            <div class="flex-1 pb-2">
                <h1 class="text-2xl sm:text-3xl font-bold">{{ $group->name }}</h1>
                <div class="fb-text-secondary font-medium mt-1 flex items-center gap-2">
                    <x-icon name="{{ $group->privacy === 'private' ? 'lock' : 'globe' }}" class="w-4 h-4" />
                    {{ ucfirst($group->privacy) }} group · {{ $group->members()->where('status','approved')->count() }} members
                </div>
                @if($group->category)<div class="text-sm fb-text-secondary mt-1">{{ $group->category }}</div>@endif
            </div>
            <div class="flex gap-2 pb-2">
                @if($isMember)
                    @if($group->owner_id !== $user->id)
                    <button onclick="leaveGroup('{{ $group->slug }}', this)" class="fb-btn-secondary px-6 py-2 rounded-lg font-semibold">Leave Group</button>
                    @endif
                    @if($memberRole === 'admin' || $group->owner_id === $user->id)
                    <a href="{{ route('social.group.edit', $group) }}" class="fb-btn-secondary px-4 py-2 rounded-lg font-medium"><x-icon name="settings" class="w-5 h-5" /></a>
                    @endif
                @elseif($isPending)
                    <button disabled class="fb-btn-secondary px-6 py-2 rounded-lg font-semibold">Pending Approval</button>
                @else
                    <button onclick="joinGroup('{{ $group->slug }}', this)" class="fb-btn-primary px-6 py-2 rounded-lg font-semibold">
                        {{ $group->requires_approval ? 'Request to Join' : '+ Join Group' }}
                    </button>
                @endif
            </div>
        </div>

        <div class="flex border-t fb-border mt-3 overflow-x-auto">
            <a href="{{ route('social.group.show', $group) }}" class="fb-tab-link active">Home</a>
            <a href="#about" class="fb-tab-link">About</a>
            <a href="#members" class="fb-tab-link">Members</a>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="space-y-4">
        <div class="fb-card p-4" id="about">
            <h3 class="font-bold text-lg mb-3">About</h3>
            @if($group->description)<p class="text-sm mb-3">{{ $group->description }}</p>@endif
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2"><x-icon name="groups" class="w-4 h-4 fb-text-secondary" />{{ $group->members()->where('status','approved')->count() }} members</div>
                @if($group->category)<div class="flex items-center gap-2"><x-icon name="categories" class="w-4 h-4 fb-text-secondary" />{{ $group->category }}</div>@endif
                <div class="flex items-center gap-2"><x-icon name="{{ $group->privacy === 'private' ? 'lock' : 'globe' }}" class="w-4 h-4 fb-text-secondary" />{{ ucfirst($group->privacy) }}</div>
                <div class="flex items-center gap-2"><x-icon name="profile" class="w-4 h-4 fb-text-secondary" />Admin: <a href="{{ route('social.profile', $group->owner->username) }}" class="text-blue-600">{{ $group->owner->name }}</a></div>
            </div>
        </div>

        <div class="fb-card p-4" id="members">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-lg">Members</h3>
                <span class="text-sm fb-text-secondary">{{ $members->count() }}</span>
            </div>
            <div class="space-y-2">
                @foreach($members->take(12) as $member)
                    <a href="{{ route('social.profile', $member->user->username) }}" class="fb-right-rail-item">
                        <img src="{{ $member->user->avatarUrl() }}" class="w-9 h-9 fb-avatar" alt="{{ $member->user->name }}">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm truncate">{{ $member->user->name }}</div>
                            <div class="text-xs fb-text-secondary">{{ ucfirst($member->role) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Pending approval requests --}}
        @if(($memberRole === 'admin' || $group->owner_id === $user->id) && $pendingMembers->isNotEmpty())
        <div class="fb-card p-4">
            <h3 class="font-bold text-lg mb-3">Pending Requests ({{ $pendingMembers->count() }})</h3>
            <div class="space-y-2">
                @foreach($pendingMembers as $pending)
                    <div class="flex items-center gap-2">
                        <img src="{{ $pending->user->avatarUrl() }}" class="w-9 h-9 fb-avatar" alt="">
                        <div class="flex-1 min-w-0"><div class="font-medium text-sm truncate">{{ $pending->user->name }}</div></div>
                        <button onclick="approveMember('{{ $group->slug }}', {{ $pending->user_id }}, this)" class="fb-btn-primary px-3 py-1 rounded-md text-xs font-medium">Approve</button>
                        <button onclick="removeMember('{{ $group->slug }}', {{ $pending->user_id }}, this)" class="fb-btn-secondary px-3 py-1 rounded-md text-xs">Decline</button>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="lg:col-span-2">
        @if($isMember || $group->owner_id === $user->id)
            <div class="fb-card p-3 mb-4">
                <div class="flex items-center gap-2">
                    <img src="{{ $user->avatarUrl() }}" class="w-10 h-10 fb-avatar" alt="">
                    <a href="{{ route('social.feed') }}#create-post" class="fb-input text-left py-2.5">Write something in {{ $group->name }}...</a>
                </div>
            </div>
        @endif

        @if($posts->isEmpty())
            <div class="fb-card p-8 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="news-feed" class="w-8 h-8" /></div>
                <h3 class="font-bold text-lg">No posts yet</h3>
                <p class="fb-text-secondary text-sm">@if($isMember)Be the first to post!@elseJoin to see posts.@endif</p>
            </div>
        @else
            @foreach($posts as $post)
                @include('social.partials.post-card', ['post' => $post])
            @endforeach
            @if($posts->hasPages())<div class="text-center py-4">{{ $posts->links() }}</div>@endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function joinGroup(groupId, btn) {
    fetch(`/groups/${groupId}/join`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => {
        if (data.error) { alert(data.error); return; }
        btn.textContent = data.pending ? 'Pending' : 'Joined';
        btn.className = 'fb-btn-secondary px-6 py-2 rounded-lg font-semibold';
        btn.disabled = true;
    });
}
function leaveGroup(groupId, btn) {
    if (!confirm('Leave this group?')) return;
    fetch(`/groups/${groupId}/leave`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert(data.error || 'Failed.'); });
}
function approveMember(groupId, userId, btn) {
    fetch(`/groups/${groupId}/members/${userId}/approve`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => { if (data.success) btn.parentElement.remove(); else alert(data.error); });
}
function removeMember(groupId, userId, btn) {
    fetch(`/groups/${groupId}/members/${userId}/remove`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => { if (data.success) btn.parentElement.remove(); else alert(data.error); });
}
</script>
@endpush
