@extends('layouts.social')

@section('title', 'Friends — People You May Know')

@section('main_class', 'max-w-[900px] mx-auto px-4 py-4')

@section('content')
<div class="mb-4">
    <h1 class="text-2xl font-bold mb-1">People You May Know</h1>
    <p class="fb-text-secondary text-sm">Connect with people to grow your network</p>
</div>

@if($suggestions->isEmpty())
    <div class="fb-card p-8 text-center">
        <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="friend-suggestions" class="w-8 h-8" /></div>
        <h3 class="font-bold text-lg">No suggestions right now</h3>
        <p class="fb-text-secondary text-sm">You're already following everyone! Check back later.</p>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($suggestions as $person)
            <div class="fb-card overflow-hidden">
                <a href="{{ route('social.profile', $person->username) }}">
                    <img src="{{ $person->avatarUrl() }}" class="w-full h-40 object-cover" alt="{{ $person->name }}">
                </a>
                <div class="p-3">
                    <a href="{{ route('social.profile', $person->username) }}" class="font-semibold text-sm hover:underline block truncate">{{ $person->name }}</a>
                    <div class="text-xs fb-text-secondary mb-2">{{ $person->followers_count }} followers</div>
                    <button onclick="toggleFollowSuggestion({{ $person->id }}, this)" class="w-full fb-btn-primary py-1.5 rounded-md text-sm font-medium">
                        + Follow
                    </button>
                    <a href="{{ route('social.chat') }}?user={{ $person->id }}" class="block w-full fb-btn-secondary py-1.5 rounded-md text-sm font-medium mt-1.5 text-center">
                        Message
                    </a>
                </div>
            </div>
        @endforeach
    </div>
    @if($suggestions->hasPages())<div class="text-center py-4">{{ $suggestions->links() }}</div>@endif
@endif
@endsection

@push('scripts')
<script>
function toggleFollowSuggestion(userId, btn) {
    fetch(`/social/follow/${userId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        btn.textContent = data.following ? 'Following' : '+ Follow';
        btn.className = data.following ? 'w-full fb-btn-secondary py-1.5 rounded-md text-sm font-medium' : 'w-full fb-btn-primary py-1.5 rounded-md text-sm font-medium';
    });
}
</script>
@endpush
