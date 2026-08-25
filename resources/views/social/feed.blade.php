@extends('layouts.social')

@section('title', 'Home — Social Feed')

@section('content')
<div class="grid lg:grid-cols-[300px_1fr_300px] gap-4 px-2 sm:px-0">
    {{-- LEFT SIDEBAR: Navigation (Facebook style) --}}
    <aside class="hidden lg:block">
        <div class="sticky top-16 space-y-1">
            <a href="{{ route('social.profile', auth('web')->user()->username) }}" class="fb-right-rail-item">
                <img src="{{ auth('web')->user()->avatarUrl() }}" class="w-9 h-9 fb-avatar" alt="">
                <span class="font-medium">{{ auth('web')->user()->name }}</span>
            </a>
            <a href="{{ route('social.suggestions') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="friends" class="w-5 h-5" /></div>
                <span>Friends</span>
            </a>
            <a href="{{ route('social.explore') }}?tab=videos" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="videos" class="w-5 h-5" /></div>
                <span>Videos</span>
            </a>
            <a href="{{ route('social.explore') }}?tab=reels" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="reels" class="w-5 h-5" /></div>
                <span>Reels</span>
            </a>
            <a href="{{ route('social.pages.mine') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="pages" class="w-5 h-5" /></div>
                <span>Pages</span>
            </a>
            <a href="{{ route('social.groups.mine') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="groups" class="w-5 h-5" /></div>
                <span>Groups</span>
            </a>
            <a href="{{ route('marketplace') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="marketplace" class="w-5 h-5" /></div>
                <span>Marketplace</span>
            </a>
            <a href="{{ route('blog.index') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="blog" class="w-5 h-5" /></div>
                <span>Blog</span>
            </a>
            <a href="{{ route('social.monetization') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="monetization" class="w-5 h-5" /></div>
                <span>Monetization</span>
            </a>
            <a href="{{ route('user.notifications') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="notifications" class="w-5 h-5" /></div>
                <span>Notifications</span>
            </a>
            <a href="{{ route('user.wallet') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="wallet" class="w-5 h-5" /></div>
                <span>Wallet</span>
            </a>
            <a href="{{ route('user.dashboard') }}" class="fb-right-rail-item">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background: #E4E6EB;"><x-icon name="dashboard" class="w-5 h-5" /></div>
                <span>Marketplace Dashboard</span>
            </a>
        </div>
    </aside>

    {{-- CENTER: Feed --}}
    <div class="max-w-[680px] w-full mx-auto">
        @if(session('success'))
            <div class="fb-card p-3 mb-4 text-green-600 text-sm font-medium flex items-center gap-2">
                <x-icon name="check" class="w-5 h-5" />{{ session('success') }}
            </div>
        @endif

        {{-- Stories bar (Facebook style) — powered by Story model --}}
        @php
            $stories = isset($stories) ? $stories : collect();
            if (function_exists('app') && class_exists(\App\Models\Story::class)) {
                try {
                    $stories = \App\Models\Story::with('user')
                        ->where('expires_at', '>', now())
                        ->latest()
                        ->limit(8)
                        ->get();
                } catch (\Throwable $e) { $stories = collect(); }
            }
        @endphp
        @include('social.partials.stories-bar', ['stories' => $stories])

        {{-- Create post box (Facebook style) --}}
        <div class="fb-card mb-4 p-3" id="create-post">
            <div class="flex items-center gap-2 pb-3">
                <img src="{{ auth('web')->user()->avatarUrl() }}" class="w-10 h-10 fb-avatar" alt="">
                <button onclick="document.getElementById('post-modal').classList.remove('hidden')" class="fb-input text-left py-2.5 hover:brightness-95 cursor-pointer">
                    What's on your mind, {{ explode(' ', auth('web')->user()->name)[0] }}?
                </button>
            </div>
            <div class="border-t fb-border pt-2 flex items-center justify-around">
                <button onclick="document.getElementById('post-modal').classList.remove('hidden')" class="fb-react-btn flex-1 justify-center">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background: #F35369;"><x-icon name="video" class="w-4 h-4 text-white" /></span>
                    <span class="text-sm">Live Video</span>
                </button>
                <button onclick="document.getElementById('post-modal').classList.remove('hidden')" class="fb-react-btn flex-1 justify-center">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background: #45BD62;"><x-icon name="photo" class="w-4 h-4 text-white" /></span>
                    <span class="text-sm">Photo/Video</span>
                </button>
                <button onclick="document.getElementById('post-modal').classList.remove('hidden')" class="fb-react-btn flex-1 justify-center">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center" style="background: #F7B928;"><x-icon name="feelings" class="w-4 h-4 text-white" /></span>
                    <span class="text-sm">Feeling</span>
                </button>
            </div>
        </div>

        {{-- Sponsored ad inserted in feed --}}
        @include('social.partials.sponsored-feed')

        {{-- Feed posts --}}
        @forelse($posts as $post)
            @include('social.partials.post-card', ['post' => $post])
            {{-- Insert sponsored ad after the 2nd post --}}
            @if($loop->iteration === 2)
                <div id="airtrend-sponsored-inline"></div>
            @endif
        @empty
            <div class="fb-card p-8 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="news-feed" class="w-8 h-8" /></div>
                <h3 class="font-bold text-lg mb-1">Your feed is empty</h3>
                <p class="fb-text-secondary text-sm mb-4">Follow people and join groups to see their posts here.</p>
                <a href="{{ route('social.suggestions') }}" class="inline-block fb-btn-primary px-6 py-2 rounded-lg font-medium">Find People to Follow</a>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($posts->hasPages())
            <div class="text-center py-4">{{ $posts->links() }}</div>
        @endif
    </div>

    {{-- RIGHT SIDEBAR: Sponsored + Contacts (Facebook style) --}}
    <aside class="hidden lg:block">
        <div class="sticky top-16">
            {{-- Sponsored --}}
            <div class="mb-4">
                <div class="fb-text-secondary font-semibold text-sm mb-2">Sponsored</div>
                @php $ads = \App\Models\Ad::atPosition('sidebar')->take(2)->get(); @endphp
                @if($ads->isNotEmpty())
                    @foreach($ads as $ad)
                        <a href="{{ $ad->link_url ?? '#' }}" class="fb-right-rail-item">
                            <div class="w-24 h-24 rounded-lg overflow-hidden flex-shrink-0">
                                @if($ad->image_path)<img src="{{ Storage::url($ad->image_path) }}" class="w-full h-full object-cover" alt="{{ $ad->title }}">@endif
                            </div>
                            <div>
                                <div class="text-sm font-medium">{{ $ad->title }}</div>
                                <div class="text-xs fb-text-secondary">{{ $ad->subtitle ?? 'sponsored' }}</div>
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="text-xs fb-text-secondary px-3">No sponsored content</div>
                @endif
            </div>

            {{-- Contacts / Friends --}}
            <div class="border-t fb-border pt-3">
                <div class="flex items-center justify-between mb-2 px-3">
                    <span class="fb-text-secondary font-semibold text-sm">Contacts</span>
                    <div class="flex gap-1">
                        <button class="p-1.5 rounded-full fb-hover-bg"><x-icon name="video-call" class="w-4 h-4" /></button>
                        <button class="p-1.5 rounded-full fb-hover-bg"><x-icon name="search" class="w-4 h-4" /></button>
                        <button class="p-1.5 rounded-full fb-hover-bg"><x-icon name="more" class="w-4 h-4" /></button>
                    </div>
                </div>
                @foreach($suggestions as $contact)
                    <a href="{{ route('social.chat') }}?user={{ $contact->id }}" class="fb-right-rail-item">
                        <div class="relative">
                            <img src="{{ $contact->avatarUrl() }}" class="w-9 h-9 fb-avatar" alt="{{ $contact->name }}">
                            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2" style="border-color: var(--fb-card);"></span>
                        </div>
                        <span class="font-medium text-sm">{{ $contact->name }}</span>
                    </a>
                @endforeach
                <a href="{{ route('social.suggestions') }}" class="fb-right-rail-item text-blue-600 font-medium text-sm mt-2">See all</a>
            </div>

            {{-- Trending --}}
            @if($trending->isNotEmpty())
            <div class="border-t fb-border pt-3 mt-3">
                <div class="fb-text-secondary font-semibold text-sm mb-2 px-3">Trending</div>
                @foreach($trending as $trendPost)
                    <a href="{{ route('social.profile', $trendPost->postable->username ?? '') }}" class="fb-right-rail-item">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: #F35369;"><x-icon name="trending-feed" class="w-4 h-4 text-white" /></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ \Illuminate\Support\Str::limit($trendPost->content, 40) }}</div>
                            <div class="text-xs fb-text-secondary">{{ $trendPost->likes_count }} reactions</div>
                        </div>
                    </a>
                @endforeach
            </div>
            @endif
        </div>
    </aside>
</div>

{{-- Create Post Modal --}}
<div id="post-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.6);">
    <div class="fb-card w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.outside="document.getElementById('post-modal').classList.add('hidden')">
        <div class="flex items-center justify-between p-3 border-b fb-border">
            <button onclick="document.getElementById('post-modal').classList.add('hidden')"><x-icon name="x" class="w-5 h-5" /></button>
            <h3 class="font-bold text-lg">Create Post</h3>
            <div class="w-5"></div>
        </div>
        <form action="{{ route('social.feed.post') }}" method="POST" enctype="multipart/form-data" class="p-3">
            @csrf
            <div class="flex items-center gap-2 mb-3">
                <img src="{{ auth('web')->user()->avatarUrl() }}" class="w-10 h-10 fb-avatar" alt="">
                <div>
                    <div class="font-semibold">{{ auth('web')->user()->name }}</div>
                    <select name="visibility" class="text-xs fb-input py-1 px-2">
                        <option value="public">🌍 Public</option>
                        <option value="friends">👥 Friends</option>
                        <option value="private">🔒 Only Me</option>
                    </select>
                </div>
            </div>
            <textarea name="content" rows="5" placeholder="What's on your mind, {{ explode(' ', auth('web')->user()->name)[0] }}?" class="w-full text-lg outline-none bg-transparent resize-none mb-3" required></textarea>
            <div class="flex gap-2 mb-3 flex-wrap">
                <input type="text" name="feeling" placeholder="Feeling (e.g. happy, excited)" class="fb-input text-sm flex-1 min-w-[120px]">
                <input type="text" name="location" placeholder="📍 Location" class="fb-input text-sm flex-1 min-w-[120px]">
            </div>
            <div class="border fb-border rounded-lg p-3 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-medium text-sm">Add to your post</span>
                    <div class="flex gap-1">
                        <label class="cursor-pointer p-2 rounded-full fb-hover-bg" title="Photo/Video">
                            <x-icon name="photo" class="w-5 h-5" style="color: #45BD62;" />
                            <input type="file" name="media[]" multiple accept="image/*,video/*" class="hidden">
                        </label>
                        <button type="button" class="p-2 rounded-full fb-hover-bg" title="Tag Friends"><x-icon name="tag" class="w-5 h-5" style="color: #1877F2;" /></button>
                        <button type="button" class="p-2 rounded-full fb-hover-bg" title="Feeling"><x-icon name="feelings" class="w-5 h-5" style="color: #F7B928;" /></button>
                        <button type="button" class="p-2 rounded-full fb-hover-bg" title="Check-in"><x-icon name="check-in" class="w-5 h-5" style="color: #F35369;" /></button>
                        <button type="button" class="p-2 rounded-full fb-hover-bg" title="GIF"><x-icon name="gif" class="w-5 h-5" style="color: #00A800;" /></button>
                    </div>
                </div>
            </div>
            <button type="submit" class="w-full fb-btn-primary py-2.5 rounded-lg font-semibold">Post</button>
        </form>
    </div>
</div>

{{-- Reaction popup CSS + JS --}}
<style>
.reaction-popup {
    position: absolute; bottom: 48px; left: 0; display: none;
    gap: 4px; padding: 6px; border-radius: 30px; z-index: 50;
    background: var(--fb-card); box-shadow: 0 2px 12px rgba(0,0,0,0.2);
}
.reaction-popup.show { display: flex; animation: popIn 0.2s; }
@keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.reaction-btn { width: 36px; height: 36px; cursor: pointer; transition: transform 0.15s; }
.reaction-btn:hover { transform: scale(1.3) translateY(-5px); }
</style>
@endsection

@push('scripts')
<script>
// Toggle reaction (like by default, with reaction picker on hover)
function toggleReaction(postId, currentReaction) {
    // For simplicity, toggle like
    fetch(`{{ route('social.like', ':post') }}`.replace(':post', postId), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
        body: JSON.stringify({ reaction: 'like' })
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        const btn = document.getElementById('like-btn-' + postId);
        const text = document.getElementById('like-text-' + postId);
        if (data.reacted) {
            btn.classList.add('text-blue-600');
            text.textContent = 'Like';
        } else {
            btn.classList.remove('text-blue-600');
            text.textContent = 'Like';
        }
    });
}

function showComments(postId) {
    const el = document.getElementById('comments-' + postId);
    el.classList.toggle('hidden');
}

function submitComment(event, postId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    fetch(`{{ route('social.comment', ':post') }}`.replace(':post', postId), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const list = document.getElementById('comment-list-' + postId);
            const div = document.createElement('div');
            div.className = 'flex items-start gap-2 mb-2';
            div.innerHTML = `
                <img src="${data.comment.user_avatar}" class="w-8 h-8 fb-avatar" alt="">
                <div class="flex-1">
                    <div class="inline-block fb-hover-bg rounded-2xl px-3 py-2">
                        <span class="font-semibold text-sm">${data.comment.user_name}</span>
                        <div class="text-sm">${data.comment.body}</div>
                    </div>
                    <div class="text-xs fb-text-secondary mt-1 px-3">Just now</div>
                </div>`;
            list.prepend(div);
            form.querySelector('input[name=body]').value = '';
        } else if (data.error) {
            alert(data.error);
        }
    });
}

function sharePost(postId) {
    if (!confirm('Share this post to your feed?')) return;
    fetch(`{{ route('social.share', ':post') }}`.replace(':post', postId), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => { if (data.success) alert('Post shared!'); else alert(data.error || 'Failed to share.'); });
}

function deletePost(postId) {
    if (!confirm('Delete this post?')) return;
    fetch(`{{ route('social.delete', ':post') }}`.replace(':post', postId), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    .then(r => r.json())
    .then(data => { if (data.success) document.getElementById('post-' + postId).remove(); else alert(data.error || 'Failed.'); });
}

function likeComment(commentId) {
    fetch(`/social/comment/${commentId}/like`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    }).then(r => r.json()).then(d => { if (!d.liked) {} });
}

function loadAllComments(postId) {
    fetch(`{{ route('social.comments', ':post') }}`.replace(':post', postId))
    .then(r => r.json())
    .then(data => {
        const list = document.getElementById('comment-list-' + postId);
        list.innerHTML = '';
        data.comments.forEach(c => {
            const div = document.createElement('div');
            div.className = 'flex items-start gap-2 mb-2';
            div.innerHTML = `<img src="${c.user_avatar}" class="w-8 h-8 fb-avatar" alt=""><div class="flex-1"><div class="inline-block fb-hover-bg rounded-2xl px-3 py-2"><span class="font-semibold text-sm">${c.user_name}</span><div class="text-sm">${c.body}</div></div><div class="text-xs fb-text-secondary mt-1 px-3">${c.time}</div></div>`;
            list.appendChild(div);
        });
    });
}

// Close modal on Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.getElementById('post-modal')?.classList.add('hidden');
    }
});

// Action sounds — wire reaction / comment actions to the sound player.
if (window.AirtrendSounds) {
    document.querySelectorAll('[data-action="like"], [data-like]').forEach(function (btn) {
        btn.addEventListener('click', function () { AirtrendSounds.like(); });
    });
    document.querySelectorAll('[data-action="comment"]').forEach(function (btn) {
        btn.addEventListener('click', function () { AirtrendSounds.comment(); });
    });
}
</script>
@endpush

{{-- Action sounds player (Web Audio synthesis + optional MP3 files) --}}
<script src="{{ asset('js/action-sounds.js') }}" defer></script>
