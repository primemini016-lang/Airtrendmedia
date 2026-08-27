@php
    $authUser = auth('web')->user();
    $commentLiked = $authUser ? \App\Models\SocialLike::where('likeable_type', \App\Models\SocialComment::class)
        ->where('likeable_id', $comment->id)->where('user_id', $authUser->id)->exists() : false;
@endphp
<div class="flex items-start gap-2 mb-2" id="comment-{{ $comment->id }}">
    <a href="{{ route('social.profile', $comment->user?->username ?? '') }}">
        <img src="{{ $comment->user?->avatarUrl() ?? asset('images/default-avatar.png') }}" class="w-8 h-8 fb-avatar" alt="">
    </a>
    <div class="flex-1 min-w-0">
        <div class="inline-block fb-hover-bg rounded-2xl px-3 py-2 max-w-full">
            <a href="{{ route('social.profile', $comment->user?->username ?? '') }}" class="font-semibold text-sm hover:underline block">{{ $comment->user?->name }}</a>
            <div class="text-sm break-words">{!! nl2br(e($comment->body)) !!}</div>
        </div>
        @if($comment->media)
            <img src="{{ Storage::url($comment->media) }}" class="rounded-lg mt-1 max-h-48" alt="">
        @endif

        {{-- Comment action bar: Like, Reply, time --}}
        <div class="flex items-center gap-3 mt-1 px-3 text-xs font-semibold fb-text-secondary">
            <button onclick="likeComment({{ $comment->id }})"
                    id="comment-like-btn-{{ $comment->id }}"
                    class="hover:underline {{ $commentLiked ? 'text-blue-600' : '' }}">
                Like
            </button>
            <button onclick="toggleReplyForm({{ $comment->id }})" class="hover:underline">Reply</button>
            <span class="font-normal">{{ $comment->created_at->diffForHumans() }}</span>
            @if($comment->likes_count > 0)
                <span class="flex items-center gap-1 font-normal" id="comment-like-count-{{ $comment->id }}">
                    <span class="w-4 h-4 rounded-full flex items-center justify-center" style="background: var(--fb-blue);">
                        <x-icon name="like" class="w-3 h-3 text-white" fill="currentColor" />
                    </span>
                    {{ $comment->likes_count }}
                </span>
            @else
                <span class="font-normal hidden" id="comment-like-count-{{ $comment->id }}">0</span>
            @endif
        </div>

        {{-- Reply form (hidden by default, shown when Reply clicked) --}}
        @if($authUser)
        <div id="reply-form-{{ $comment->id }}" class="hidden flex items-start gap-2 mt-2 ml-2">
            <img src="{{ $authUser->avatarUrl() }}" class="w-7 h-7 fb-avatar" alt="">
            <form onsubmit="submitReply(event, {{ $comment->post_id }}, {{ $comment->id }})" class="flex-1">
                @csrf
                <input type="text" name="body" placeholder="Reply to {{ $comment->user?->name ?? 'comment' }}..."
                       class="fb-input text-sm rounded-2xl" required>
            </form>
        </div>
        @endif

        {{-- Replies --}}
        <div id="replies-{{ $comment->id }}" class="mt-1">
            @if($comment->replies->isNotEmpty())
                @foreach($comment->replies as $reply)
                    <div class="flex items-start gap-2 mt-2 ml-2" id="comment-{{ $reply->id }}">
                        <a href="{{ route('social.profile', $reply->user?->username ?? '') }}">
                            <img src="{{ $reply->user?->avatarUrl() ?? asset('images/default-avatar.png') }}" class="w-7 h-7 fb-avatar" alt="">
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="inline-block fb-hover-bg rounded-2xl px-3 py-2 max-w-full">
                                <a href="{{ route('social.profile', $reply->user?->username ?? '') }}" class="font-semibold text-sm hover:underline block">{{ $reply->user?->name }}</a>
                                <div class="text-sm break-words">{!! nl2br(e($reply->body)) !!}</div>
                            </div>
                            <div class="flex items-center gap-3 mt-1 px-3 text-xs font-semibold fb-text-secondary">
                                <span class="font-normal">{{ $reply->created_at->diffForHumans() }}</span>
                                @if($reply->likes_count > 0)
                                    <span class="flex items-center gap-1 font-normal">
                                        <span class="w-4 h-4 rounded-full flex items-center justify-center" style="background: var(--fb-blue);">
                                            <x-icon name="like" class="w-3 h-3 text-white" fill="currentColor" />
                                        </span>
                                        {{ $reply->likes_count }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
