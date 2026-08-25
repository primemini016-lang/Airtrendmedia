@extends('layouts.social')

@section('title', $page->name)

@section('main_class', 'max-w-[900px] mx-auto px-0 sm:px-4 py-0')

@section('content')
{{-- Page header (Facebook style) --}}
<div class="fb-card mb-4 overflow-hidden">
    <div class="relative h-[200px] sm:h-[350px] bg-gradient-to-r from-indigo-500 to-purple-600">
        @if($page->cover_image)
            <img src="{{ Storage::url($page->cover_image) }}" class="w-full h-full object-cover" alt="Cover">
        @endif
    </div>
    <div class="px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3 -mt-12">
            <img src="{{ $page->profile_image ? Storage::url($page->profile_image) : asset('images/default-page.png') }}" class="w-32 h-32 sm:w-40 sm:h-40 rounded-lg border-4 object-cover" style="border-color: var(--fb-card);" alt="{{ $page->name }}">
            <div class="flex-1 pb-2">
                <h1 class="text-3xl font-bold">{{ $page->name }}</h1>
                <div class="fb-text-secondary font-medium mt-1">{{ $page->followers_count }} followers · {{ $page->likes_count }} likes</div>
                @if($page->category)<div class="text-sm fb-text-secondary mt-1">{{ $page->category }}</div>@endif
            </div>
            <div class="flex gap-2 pb-2">
                @if($isMember)
                    <button onclick="leavePage({{ $page->id }}, this)" class="fb-btn-secondary px-6 py-2 rounded-lg font-semibold">
                        <x-icon name="check" class="w-5 h-5 inline" /> Following
                    </button>
                @else
                    <button onclick="joinPage({{ $page->id }}, this)" class="fb-btn-primary px-6 py-2 rounded-lg font-semibold">+ Follow</button>
                @endif
                @if($page->website)
                    <a href="{{ $page->website }}" target="_blank" class="fb-btn-secondary px-4 py-2 rounded-lg font-medium"><x-icon name="link" class="w-5 h-5" /></a>
                @endif
                @if($memberRole === 'admin' || $page->owner_id === $user->id)
                    <a href="{{ route('social.page.edit', $page) }}" class="fb-btn-secondary px-4 py-2 rounded-lg font-medium"><x-icon name="edit" class="w-5 h-5" /> Manage</a>
                @endif
            </div>
        </div>

        <div class="flex border-t fb-border mt-3 overflow-x-auto">
            <a href="{{ route('social.page.show', $page) }}" class="fb-tab-link active">Home</a>
            <a href="#about" class="fb-tab-link">About</a>
            <a href="#members" class="fb-tab-link">Members</a>
            <a href="#photos" class="fb-tab-link">Photos</a>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    {{-- Left: About --}}
    <div class="space-y-4">
        <div class="fb-card p-4" id="about">
            <h3 class="font-bold text-lg mb-3">About</h3>
            @if($page->description)<p class="text-sm mb-3">{{ $page->description }}</p>@endif
            <div class="space-y-2 text-sm">
                @if($page->category)<div class="flex items-center gap-2"><x-icon name="categories" class="w-4 h-4 fb-text-secondary" />{{ $page->category }}</div>@endif
                @if($page->location)<div class="flex items-center gap-2"><x-icon name="check-in" class="w-4 h-4 fb-text-secondary" />{{ $page->location }}</div>@endif
                @if($page->phone)<div class="flex items-center gap-2"><x-icon name="phone-call" class="w-4 h-4 fb-text-secondary" />{{ $page->phone }}</div>@endif
                @if($page->email)<div class="flex items-center gap-2"><x-icon name="email" class="w-4 h-4 fb-text-secondary" />{{ $page->email }}</div>@endif
                @if($page->website)<div class="flex items-center gap-2"><x-icon name="link" class="w-4 h-4 fb-text-secondary" /><a href="{{ $page->website }}" class="text-blue-600">{{ $page->website }}</a></div>@endif
            </div>
        </div>

        <div class="fb-card p-4" id="members">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-lg">Members</h3>
                <span class="text-sm fb-text-secondary">{{ $page->members()->count() }}</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                @foreach($members as $member)
                    <a href="{{ route('social.profile', $member->user->username) }}">
                        <img src="{{ $member->user->avatarUrl() }}" class="w-full aspect-square object-cover rounded-lg" alt="{{ $member->user->name }}">
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Posts --}}
    <div class="lg:col-span-2">
        @if($isMember || $page->owner_id === $user->id)
            {{-- Create post as page --}}
            <div class="fb-card p-3 mb-4">
                <div class="flex items-center gap-2">
                    <img src="{{ $page->profile_image ? Storage::url($page->profile_image) : asset('images/default-page.png') }}" class="w-10 h-10 rounded-lg object-cover" alt="">
                    <a href="{{ route('social.feed') }}#create-post" class="fb-input text-left py-2.5">Write something to {{ $page->name }}...</a>
                </div>
            </div>
        @endif

        @forelse($posts as $post)
            @include('social.partials.post-card', ['post' => $post])
        @empty
            <div class="fb-card p-8 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="pages" class="w-8 h-8" /></div>
                <h3 class="font-bold text-lg">No posts yet</h3>
                <p class="fb-text-secondary text-sm">{{ $page->name }} hasn't posted anything yet.</p>
            </div>
        @endforelse
        @if($posts->hasPages())<div class="text-center py-4">{{ $posts->links() }}</div>@endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function joinPage(pageId, btn) {
    fetch(`/social/pages/${pageId}/join`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).then(r => r.json()).then(data => {
        if (data.error) { alert(data.error); return; }
        btn.textContent = 'Following';
        btn.className = 'fb-btn-secondary px-6 py-2 rounded-lg font-semibold';
        btn.onclick = function() { leavePage(pageId, this); };
    });
}
function leavePage(pageId, btn) {
    fetch(`/social/pages/${pageId}/leave`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).then(r => r.json()).then(data => {
        if (data.error) { alert(data.error); return; }
        btn.textContent = '+ Follow';
        btn.className = 'fb-btn-primary px-6 py-2 rounded-lg font-semibold';
        btn.onclick = function() { joinPage(pageId, this); };
    });
}
</script>
@endpush
