@extends('layouts.social')

@section('title', 'Groups')

@section('main_class', 'max-w-[900px] mx-auto px-4 py-4')

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold">Groups</h1>
        <p class="fb-text-secondary text-sm">Join communities that share your interests</p>
    </div>
    <a href="{{ route('social.groups.create') }}" class="fb-btn-primary px-4 py-2 rounded-lg font-medium flex items-center gap-2"><x-icon name="create" class="w-5 h-5" /> Create Group</a>
</div>

<form method="GET" class="mb-4">
    <div class="relative">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search groups..." class="fb-input py-2.5 pl-10">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 fb-text-secondary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    </div>
</form>

@if($myGroups->isNotEmpty())
<div class="mb-6">
    <h2 class="font-bold text-lg mb-3">Your Groups</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($myGroups as $group)
            <a href="{{ route('social.group.show', $group) }}" class="fb-card overflow-hidden">
                <img src="{{ $group->profile_image ? Storage::url($group->profile_image) : asset('images/default-group.png') }}" class="w-full h-32 object-cover" alt="{{ $group->name }}">
                <div class="p-3">
                    <div class="font-semibold text-sm truncate">{{ $group->name }}</div>
                    <div class="text-xs fb-text-secondary">{{ $group->members()->where('status','approved')->count() }} members</div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endif

<h2 class="font-bold text-lg mb-3">Discover Groups</h2>
@if($groups->isEmpty())
    <div class="fb-card p-8 text-center">
        <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="groups" class="w-8 h-8" /></div>
        <h3 class="font-bold text-lg">No groups found</h3>
        <a href="{{ route('social.groups.create') }}" class="inline-block fb-btn-primary px-6 py-2 rounded-lg font-medium mt-3">Create Group</a>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($groups as $group)
            <div class="fb-card overflow-hidden">
                <a href="{{ route('social.group.show', $group) }}">
                    <img src="{{ $group->profile_image ? Storage::url($group->profile_image) : asset('images/default-group.png') }}" class="w-full h-40 object-cover" alt="{{ $group->name }}">
                </a>
                <div class="p-3">
                    <a href="{{ route('social.group.show', $group) }}" class="font-semibold text-sm hover:underline block truncate">{{ $group->name }}</a>
                    @if($group->category)<div class="text-xs fb-text-secondary">{{ $group->category }}</div>@endif
                    <div class="text-xs fb-text-secondary flex items-center gap-1">
                        <x-icon name="{{ $group->privacy === 'private' ? 'lock' : 'globe' }}" class="w-3 h-3" />
                        {{ ucfirst($group->privacy) }} · {{ $group->members()->where('status','approved')->count() }} members
                    </div>
                    @if(!$group->isMemberOf($user))
                    <button onclick="joinGroup({{ $group->id }}, this)" class="w-full fb-btn-primary py-1.5 rounded-md text-sm font-medium mt-2">
                        {{ $group->requires_approval ? 'Request to Join' : '+ Join' }}
                    </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @if($groups->hasPages())<div class="text-center py-4">{{ $groups->links() }}</div>@endif
@endif
@endsection

@push('scripts')
<script>
function joinGroup(groupId, btn) {
    fetch(`/social/groups/${groupId}/join`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).then(r => r.json()).then(data => {
        if (data.error) { alert(data.error); return; }
        btn.textContent = data.pending ? 'Pending' : 'Joined';
        btn.className = 'w-full fb-btn-secondary py-1.5 rounded-md text-sm font-medium mt-2';
        btn.disabled = true;
    });
}
</script>
@endpush
