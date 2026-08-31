@php $user = auth('web')->user(); @endphp
<div id="comment-{{ $comment->id }}" class="flex gap-3">
    @if($comment->user && $comment->user->image)
        <img src="{{ storage_asset($comment->user->image) }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0" alt="{{ $comment->user->name }}">
    @else
        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ strtoupper(substr($comment->user?->name ?? 'U', 0, 1)) }}</div>
    @endif
    <div class="flex-1">
        <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-4">
            <div class="flex items-center justify-between mb-1">
                <span class="font-semibold text-sm text-slate-800 dark:text-slate-100">
                    @if($comment->user)
                        <a href="{{ route('user.public-profile', $comment->user) }}" class="hover:underline">{{ $comment->user->name }}</a>
                        @if($comment->user->isBlueVerified())<x-verified-badge size="w-4 h-4 inline" class="align-middle" />@endif
                    @else
                        Unknown
                    @endif
                </span>
                <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $comment->body }}</p>
        </div>

        @if($user)
        <button onclick="toggleReply({{ $comment->id }})" class="text-xs text-blue-600 hover:underline mt-2 flex items-center gap-1">
            <x-icon name="comment" class="w-3 h-3" /> Reply
        </button>

        {{-- Reply form --}}
        <form id="reply-form-{{ $comment->id }}" action="{{ route('blog.comment', $post->slug) }}" method="POST" class="hidden mt-3 mb-3">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $comment->id }}">
            <textarea name="body" rows="2" required placeholder="Reply to {{ $comment->user?->name ?? 'comment' }}..." class="input w-full text-sm resize-none" maxlength="2000"></textarea>
            <button type="submit" class="btn btn-primary mt-2 text-xs px-3 py-1.5">Post Reply</button>
        </form>
        @endif

        {{-- Replies --}}
        @if($comment->replies->isNotEmpty())
        <div class="mt-4 space-y-4 ml-2 border-l-2 border-slate-100 dark:border-slate-700 pl-4">
            @foreach($comment->replies as $reply)
                <div id="comment-{{ $reply->id }}" class="flex gap-3">
                    @if($reply->user && $reply->user->image)
                        <img src="{{ storage_asset($reply->user->image) }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="{{ $reply->user->name }}">
                    @else
                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">{{ strtoupper(substr($reply->user?->name ?? 'U', 0, 1)) }}</div>
                    @endif
                    <div class="flex-1">
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-semibold text-xs text-slate-800 dark:text-slate-100">{{ $reply->user?->name ?? 'Unknown' }}</span>
                                <span class="text-xs text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{{ $reply->body }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<script>
function toggleReply(id) {
    const form = document.getElementById('reply-form-' + id);
    form.classList.toggle('hidden');
    if (!form.classList.contains('hidden')) {
        form.querySelector('textarea').focus();
    }
}
</script>
