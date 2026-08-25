@php $authUser = auth('web')->user(); @endphp
<div class="flex items-start gap-2 mb-2" id="comment-{{ $comment->id }}">
    <a href="{{ route('social.profile', $comment->user?->username ?? '') }}">
        <img src="{{ $comment->user?->avatarUrl() ?? asset('images/default-avatar.png') }}" class="w-8 h-8 fb-avatar" alt="">
    </a>
    <div class="flex-1">
        <div class="inline-block fb-hover-bg rounded-2xl px-3 py-2">
            <a href="{{ route('social.profile', $comment->user?->username ?? '') }}" class="font-semibold text-sm hover:underline block">{{ $comment->user?->name }}</a>
            <div class="text-sm">{!! nl2br(e($comment->body)) !!}</div>
        </div>
        @if($comment->media)
            <img src="{{ Storage::url($comment->media) }}" class="rounded-lg mt-1 max-h-48" alt="">
        @endif
        <div class="flex items-center gap-3 mt-1 px-3 text-xs font-semibold fb-text-secondary">
            <button onclick="likeComment({{ $comment->id }})" class="hover:underline">Like</button>
            <button class="hover:underline">Reply</button>
            <span class="font-normal">{{ $comment->created_at->diffForHumans() }}</span>
        </div>
        {{-- Replies --}}
        @if($comment->replies->isNotEmpty())
            @foreach($comment->replies as $reply)
                <div class="flex items-start gap-2 mt-2 ml-2">
                    <img src="{{ $reply->user?->avatarUrl() ?? asset('images/default-avatar.png') }}" class="w-7 h-7 fb-avatar" alt="">
                    <div>
                        <div class="inline-block fb-hover-bg rounded-2xl px-3 py-2">
                            <span class="font-semibold text-sm">{{ $reply->user?->name }}</span>
                            <div class="text-sm">{!! nl2br(e($reply->body)) !!}</div>
                        </div>
                        <div class="text-xs fb-text-secondary mt-1 px-3">{{ $reply->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
