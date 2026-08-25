{{--
    Airtrendmedia — Stories Bar (Facebook style)
    Variables: $stories (collection of Story models or users), $currentUser
    Renders an horizontal scroll of story bubbles. The first card is always
    "Create Story". Tapping a story opens the viewer (data-story-id).
--}}
@php
    $storyList = $stories ?? collect();
    $current   = $currentUser ?? auth('web')->user();
@endphp
<div class="fb-stories-bar" id="fb-stories-bar">
    {{-- Create Story --}}
    <div class="fb-story-create" onclick="document.getElementById('story-create-modal')?.classList.remove('hidden')">
        <div class="fb-story-create-img">
            @if($current)<img src="{{ $current->avatarUrl() }}" alt="{{ $current->name }}">@endif
        </div>
        <div class="fb-story-create-cta">
            <div class="fb-story-create-plus">+</div>
            <span>Create Story</span>
        </div>
    </div>

    {{-- Existing stories --}}
    @forelse($storyList as $story)
        @php
            $owner = $story->user ?? $story;
            $storyId = $story->id ?? null;
            $media = method_exists($story, 'mediaUrl') ? $story->mediaUrl() : ($story->media_path ?? $story->image ?? null);
            $mediaUrl = $media ? (str_starts_with($media, 'http') ? $media : asset('storage/' . ltrim($media, '/'))) : ($owner ? $owner->avatarUrl() : '');
        @endphp
        <div class="fb-story-card" data-story-id="{{ $storyId }}"
             onclick="AirtrendStories.open({{ $storyId ?? 'null' }})">
            <img src="{{ $mediaUrl }}" alt="" class="fb-story-img" loading="lazy">
            <div class="fb-story-ring">
                <img src="{{ $owner?->avatarUrl() }}" alt="{{ $owner?->name ?? '' }}">
            </div>
            <div class="fb-story-name">{{ $owner?->name ?? 'Story' }}</div>
        </div>
    @empty
        {{-- Fallback placeholder cards from suggestions --}}
    @endforelse
</div>

{{-- Story Viewer Modal --}}
<div id="airtrend-story-viewer" class="hidden fixed inset-0 z-[200] flex items-center justify-center"
     style="background: rgba(0,0,0,0.92);">
    <button class="absolute top-4 right-4 text-white text-3xl z-10" onclick="AirtrendStories.close()">&times;</button>
    <div class="relative w-full max-w-md h-[80vh] rounded-2xl overflow-hidden bg-black">
        <img id="airtrend-story-img" src="" alt="" class="w-full h-full object-cover">
        <div class="absolute top-3 left-3 flex items-center gap-2 z-10">
            <img id="airtrend-story-avatar" src="" class="w-9 h-9 rounded-full border-2 border-white" alt="">
            <span id="airtrend-story-owner" class="text-white font-semibold text-sm"></span>
        </div>
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/70 to-transparent">
            <form class="flex gap-2" onsubmit="return false">
                <input type="text" placeholder="Reply to story…" class="flex-1 rounded-full px-4 py-2 bg-white/10 text-white border border-white/30 placeholder-white/60 text-sm">
                <button class="w-10 h-10 rounded-full bg-white/10 text-white flex items-center justify-center" type="button">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
window.AirtrendStories = {
    open: function (id) {
        if (!id) return;
        // Fetch story data — endpoint can be extended later; we use the card DOM.
        const card = document.querySelector('[data-story-id="' + id + '"]');
        if (!card) return;
        const img = card.querySelector('.fb-story-img');
        const ring = card.querySelector('.fb-story-ring img');
        const name = card.querySelector('.fb-story-name');
        const viewer = document.getElementById('airtrend-story-viewer');
        if (viewer && img) {
            document.getElementById('airtrend-story-img').src = img.src;
            if (ring) document.getElementById('airtrend-story-avatar').src = ring.src;
            if (name) document.getElementById('airtrend-story-owner').textContent = name.textContent;
            viewer.classList.remove('hidden');
            if (window.AirtrendSounds) AirtrendSounds.notification();
        }
    },
    close: function () {
        const v = document.getElementById('airtrend-story-viewer');
        if (v) v.classList.add('hidden');
    },
};
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') window.AirtrendStories.close();
});
</script>
@endpush
