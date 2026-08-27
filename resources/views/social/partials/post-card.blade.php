@php
    $postUser = $post->postable instanceof \App\Models\User ? $post->postable : $post->user;
    $authUser = auth('web')->user();
    $userLike = $authUser ? \App\Models\SocialLike::where('likeable_type', \App\Models\SocialPost::class)
        ->where('likeable_id', $post->id)->where('user_id', $authUser->id)->first() : null;
    $postableName = $post->postable instanceof \App\Models\User ? $post->postable->name :
        ($post->postable instanceof \App\Models\SocialPage ? $post->postable->name :
        ($post->postable instanceof \App\Models\SocialGroup ? $post->postable->name : ''));
    $postableUrl = $post->postable instanceof \App\Models\User ? route('social.profile', $post->postable->username) :
        ($post->postable instanceof \App\Models\SocialPage ? route('social.page.show', $post->postable) :
        ($post->postable instanceof \App\Models\SocialGroup ? route('social.group.show', $post->postable) : '#'));
    $avatarUrl = $post->postable instanceof \App\Models\User ? $post->postable->avatarUrl() :
        ($post->postable instanceof \App\Models\SocialPage ? Storage::url($post->postable->image ?? 'pages/default.png') :
        ($post->postable instanceof \App\Models\SocialGroup ? Storage::url($post->postable->image ?? 'groups/default.png') :
        ($postUser?->avatarUrl() ?? asset('images/default-avatar.png'))));
@endphp
<div class="fb-card mb-4 overflow-hidden" id="post-{{ $post->id }}">
    {{-- Post header --}}
    <div class="flex items-start justify-between p-3 pb-2">
        <div class="flex items-center gap-2">
            <a href="{{ $postableUrl }}">
                <img src="{{ $avatarUrl }}" class="w-10 h-10 fb-avatar" alt="{{ $postableName }}">
            </a>
            <div>
                <a href="{{ $postableUrl }}" class="font-semibold text-sm hover:underline">{{ $postableName }}</a>
                @if($post->feeling)
                    <span class="text-sm fb-text-secondary"> is feeling {{ $post->feeling }}</span>
                @endif
                @if($post->location)
                    <span class="text-sm fb-text-secondary"> — at {{ $post->location }}</span>
                @endif
                @if($post->postable instanceof \App\Models\SocialPage)
                    <div class="text-xs fb-text-secondary">Page</div>
                @elseif($post->postable instanceof \App\Models\SocialGroup)
                    <div class="text-xs fb-text-secondary">Group</div>
                @endif
                <div class="text-xs fb-text-secondary flex items-center gap-1">
                    <span>{{ $post->created_at->diffForHumans() }}</span>
                    <span>·</span>
                    <x-icon name="audience" class="w-3 h-3" />
                </div>
            </div>
        </div>
        @if($authUser && $post->postable instanceof \App\Models\User && $post->postable->id === $authUser->id)
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="p-2 rounded-full fb-hover-bg"><x-icon name="more" class="w-5 h-5" /></button>
            <div x-show="open" @click.away="open=false" class="absolute right-0 top-10 z-30 fb-card p-2 min-w-[200px] shadow-lg">
                <button onclick="deletePost({{ $post->id }})" class="fb-right-rail-item w-full text-left text-red-600"><x-icon name="trash" class="w-5 h-5" /><span>Delete Post</span></button>
            </div>
        </div>
        @endif
    </div>

    {{-- Post content --}}
    @if($post->background_color && $post->content)
        <div class="flex items-center justify-center min-h-[200px] p-6 text-center" style="background: {{ $post->background_color }};">
            <p class="text-xl font-bold text-white">{{ $post->content }}</p>
        </div>
    @else
        @if($post->content)
            <div class="px-3 pb-2 text-sm leading-relaxed">{!! nl2br(e($post->content)) !!}</div>
        @endif
    @endif

    {{-- Post media --}}
    @if(!empty($post->media) && is_array($post->media))
        @php $mediaCount = count($post->media); @endphp
        <div class="grid @if($mediaCount === 1) grid-cols-1 @elseif($mediaCount === 2) grid-cols-2 @else grid-cols-2 @endif gap-0.5">
            @foreach(array_slice($post->media, 0, 4) as $media)
                @if(($media['type'] ?? 'image') === 'image')
                    <div class="@if($mediaCount === 1) max-h-[500px] @else max-h-[300px] @endif overflow-hidden bg-black flex items-center justify-center">
                        <img src="{{ Storage::url($media['url'] ?? '') }}" class="w-full h-full object-cover" alt="">
                    </div>
                @elseif(($media['type'] ?? '') === 'video')
                    <div class="max-h-[500px] overflow-hidden bg-black">
                        <video src="{{ Storage::url($media['url'] ?? '') }}" controls class="w-full h-full"></video>
                    </div>
                @endif
            @endforeach
            @if($mediaCount > 4)
                <div class="relative">
                    <img src="{{ Storage::url($post->media[3]['url'] ?? '') }}" class="w-full h-full object-cover opacity-50" alt="">
                    <div class="absolute inset-0 flex items-center justify-center text-white font-bold text-2xl">+{{ $mediaCount - 4 }}</div>
                </div>
            @endif
        </div>
    @endif

    {{-- Shared post --}}
    @if($post->shared_post_id && $post->sharedPost)
        @php $shared = $post->sharedPost; @endphp
        <div class="mx-3 mb-2 border fb-border rounded-lg overflow-hidden">
            <div class="flex items-center gap-2 p-2">
                <img src="{{ $shared->postable instanceof \App\Models\User ? $shared->postable->avatarUrl() : asset('images/default-avatar.png') }}" class="w-8 h-8 fb-avatar" alt="">
                <span class="font-semibold text-sm">{{ $shared->postable->name ?? '' }}</span>
                <span class="text-xs fb-text-secondary">{{ $shared->created_at->diffForHumans() }}</span>
            </div>
            @if($shared->content)<div class="px-2 pb-2 text-sm">{!! nl2br(e($shared->content)) !!}</div>@endif
        </div>
    @endif

    {{-- Stats bar --}}
    @if($post->likes_count > 0 || $post->comments_count > 0 || $post->shares_count > 0)
    <div class="flex items-center justify-between px-3 py-2 text-xs fb-text-secondary">
        <div class="flex items-center gap-1">
            @if($post->likes_count > 0)
                <span class="flex items-center gap-1">
                    <span class="w-4 h-4 rounded-full flex items-center justify-center" style="background: var(--fb-blue);"><x-icon name="like" class="w-3 h-3 text-white" fill="currentColor" /></span>
                    <span class="like-count">{{ $post->likes_count }}</span>
                </span>
            @else
                <span class="like-count" style="display:none;">0</span>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @if($post->comments_count > 0)<span>{{ $post->comments_count }} comments</span>@endif
            @if($post->shares_count > 0)<span>{{ $post->shares_count }} shares</span>@endif
            @if($post->views_count > 0)<span class="flex items-center gap-1"><x-icon name="view" class="w-3 h-3" />{{ $post->views_count }}</span>@endif
        </div>
    </div>
    @else
    <div class="hidden"><span class="like-count">0</span></div>
    @endif

    {{-- Action buttons --}}
    <div class="flex items-center justify-around border-t fb-border px-2 py-1">
        <div class="relative flex-1">
            <div id="reaction-popup-{{ $post->id }}" class="reaction-popup"
                 onmouseenter="showReactionPopup({{ $post->id }})" onmouseleave="hideReactionPopup({{ $post->id }})">
                <span class="reaction-btn" onclick="setReaction({{ $post->id }}, 'like')" title="Like">👍</span>
                <span class="reaction-btn" onclick="setReaction({{ $post->id }}, 'love')" title="Love">❤️</span>
                <span class="reaction-btn" onclick="setReaction({{ $post->id }}, 'haha')" title="Haha">😂</span>
                <span class="reaction-btn" onclick="setReaction({{ $post->id }}, 'wow')" title="Wow">😮</span>
                <span class="reaction-btn" onclick="setReaction({{ $post->id }}, 'sad')" title="Sad">😢</span>
                <span class="reaction-btn" onclick="setReaction({{ $post->id }}, 'angry')" title="Angry">😡</span>
            </div>
            <button onclick="toggleReaction({{ $post->id }}, '{{ $userLike?->reaction ?? '' }}')"
                    onmouseenter="showReactionPopup({{ $post->id }})" onmouseleave="hideReactionPopup({{ $post->id }})"
                    class="fb-react-btn w-full {{ $userLike ? 'text-blue-600' : '' }}"
                    id="like-btn-{{ $post->id }}">
                <x-icon name="{{ $userLike?->reaction === 'love' ? 'reaction-love' : 'like' }}" class="w-5 h-5" fill="{{ $userLike ? 'currentColor' : 'none' }}" />
                <span id="like-text-{{ $post->id }}">{{ ucfirst($userLike?->reaction ?? 'Like') }}</span>
            </button>
        </div>
        <button onclick="showComments({{ $post->id }})" class="fb-react-btn flex-1">
            <x-icon name="comment" class="w-5 h-5" />
            <span>Comment</span>
        </button>
        <button onclick="sharePost({{ $post->id }})" class="fb-react-btn flex-1">
            <x-icon name="share" class="w-5 h-5" />
            <span>Share</span>
        </button>
    </div>

    {{-- Comments section --}}
    <div id="comments-{{ $post->id }}" class="hidden px-3 py-2 border-t fb-border">
        {{-- Comment input --}}
        @if($authUser)
        <div class="flex items-start gap-2 mb-3">
            <img src="{{ $authUser->avatarUrl() }}" class="w-8 h-8 fb-avatar" alt="">
            <form onsubmit="submitComment(event, {{ $post->id }})" class="flex-1">
                @csrf
                <div class="relative">
                    <input type="text" name="body" placeholder="Write a comment..." class="fb-input text-sm" required>
                </div>
            </form>
        </div>
        @endif
        <div id="comment-list-{{ $post->id }}">
            @foreach($post->comments->take(2) as $comment)
                @include('social.partials.comment-item', ['comment' => $comment])
            @endforeach
        </div>
        @if($post->comments->count() > 2)
            <button onclick="loadAllComments({{ $post->id }})" class="text-sm font-medium fb-text-secondary hover:underline mt-2">View more comments</button>
        @endif
    </div>
</div>
