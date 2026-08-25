@extends('layouts.app')
@section('title', $post->title)
@section('content')

@php
    $user = auth('web')->user();
    $shareUrl = urlencode(url()->current());
    $shareTitle = urlencode($post->title);
@endphp

{{-- Hero header with featured image --}}
<div class="relative bg-slate-900 text-white">
    @if($post->featured_image)
        <div class="absolute inset-0">
            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/80 to-slate-900/40"></div>
        </div>
    @endif

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 pt-20 pb-12">
        @if($post->category)
            <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
               class="inline-block px-3 py-1 bg-blue-600 rounded-full text-xs font-semibold mb-4 hover:bg-blue-700 transition-colors">
                {{ $post->category->name }}
            </a>
        @endif
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight mb-4">{{ $post->title }}</h1>
        <p class="text-lg text-slate-300 mb-6 max-w-2xl">{{ $post->excerpt }}</p>

        {{-- Author and meta --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-3">
                @if($post->author && $post->author->image)
                    <img src="{{ asset('storage/'.$post->author->image) }}" class="w-10 h-10 rounded-full object-cover" alt="{{ $post->author->name }}">
                @else
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center font-bold">{{ strtoupper(substr($post->author?->name ?? 'A', 0, 1)) }}</div>
                @endif
                <div>
                    <p class="font-semibold text-sm">{{ $post->author?->name ?? 'Admin' }}</p>
                    <p class="text-xs text-slate-400">
                        {{ $post->published_at?->format('M j, Y') }} &middot; {{ $post->reading_time }} min read
                    </p>
                </div>
            </div>

            {{-- Stats --}}
            <div class="flex items-center gap-4 text-sm">
                <span class="flex items-center gap-1.5 text-slate-300"><x-icon name="view" class="w-4 h-4" /> {{ number_format($post->views_count) }}</span>
                <span class="flex items-center gap-1.5 text-slate-300"><x-icon name="like" class="w-4 h-4" /> {{ $post->likes_count }}</span>
                <span class="flex items-center gap-1.5 text-slate-300"><x-icon name="comment" class="w-4 h-4" /> {{ $post->comments_count }}</span>
                <span class="flex items-center gap-1.5 text-slate-300"><x-icon name="share" class="w-4 h-4" /> {{ $post->shares_count }}</span>
            </div>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 transition-colors">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">

        {{-- Article content --}}
        <article class="prose prose-lg dark:prose-invert max-w-none mb-10 article-content">
            {!! $post->content !!}
        </article>

        {{-- Tags --}}
        @if($post->tags)
        <div class="flex flex-wrap items-center gap-2 mb-8">
            <x-icon name="bookmark" class="w-4 h-4 text-slate-400" />
            @foreach(explode(',', $post->tags) as $tag)
                <a href="{{ route('blog.index', ['q' => trim($tag)]) }}" class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm rounded-full hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    {{ trim($tag) }}
                </a>
            @endforeach
        </div>
        @endif

        {{-- Action bar: like, rate, share --}}
        <div class="border-y border-slate-200 dark:border-slate-700 py-4 mb-10 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                {{-- Like button --}}
                <button id="like-btn"
                        data-slug="{{ $post->slug }}"
                        class="flex items-center gap-2 px-4 py-2 rounded-full font-medium text-sm transition-colors {{ $hasLiked ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700' }}">
                    <x-icon name="like" class="w-5 h-5 {{ $hasLiked ? 'fill-white' : '' }}" />
                    <span id="like-count">{{ $post->likes_count }}</span>
                    <span>{{ $hasLiked ? 'Liked' : 'Like' }}</span>
                </button>

                {{-- Rating stars --}}
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-0.5" id="rating-stars">
                        @for($i = 1; $i <= 5; $i++)
                            <button class="star-btn p-0.5 {{ ($userRating && $userRating->rating >= $i) ? 'text-yellow-400' : 'text-slate-300 dark:text-slate-600' }} hover:text-yellow-400 transition-colors"
                                    data-rating="{{ $i }}" data-slug="{{ $post->slug }}">
                                <x-icon name="star" class="w-5 h-5 {{ ($userRating && $userRating->rating >= $i) ? 'fill-current' : '' }}" />
                            </button>
                        @endfor
                    </div>
                    <span class="text-sm text-slate-500">
                        @if($avgRating > 0) {{ number_format($avgRating, 1) }} ({{ $post->ratings_count }}) @else Rate this @endif
                    </span>
                </div>
            </div>

            {{-- Share buttons --}}
            <div class="flex items-center gap-2">
                <span class="text-sm text-slate-500 mr-1">Share:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" onclick="trackShare('{{ $post->slug }}', 'facebook')" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors">
                    <x-icon name="facebook" class="w-4 h-4" />
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" onclick="trackShare('{{ $post->slug }}', 'twitter')" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors">
                    <x-icon name="twitter" class="w-4 h-4" />
                </a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" onclick="trackShare('{{ $post->slug }}', 'linkedin')" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors">
                    <x-icon name="linkedin" class="w-4 h-4" />
                </a>
                <a href="https://wa.me/?text={{ $shareTitle }}%20{{ $shareUrl }}" target="_blank" onclick="trackShare('{{ $post->slug }}', 'whatsapp')" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors">
                    <x-icon name="whatsapp" class="w-4 h-4" />
                </a>
                <button onclick="copyLink('{{ url()->current() }}')" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-blue-600 hover:text-white transition-colors" title="Copy link">
                    <x-icon name="link" class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Comments section --}}
        <section id="comments" class="mb-12">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <x-icon name="comment" class="w-6 h-6" />
                Comments ({{ $post->comments_count }})
            </h2>

            @if($user)
            {{-- Comment form --}}
            <form action="{{ route('blog.comment', $post->slug) }}" method="POST" class="mb-8">
                @csrf
                <div class="flex gap-3">
                    @if($user->image)
                        <img src="{{ asset('storage/'.$user->image) }}" class="w-10 h-10 rounded-full object-cover flex-shrink-0" alt="{{ $user->name }}">
                    @else
                        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    <div class="flex-1">
                        <textarea name="body" rows="3" required placeholder="Share your thoughts..." class="input w-full resize-none" maxlength="2000"></textarea>
                        <button type="submit" class="btn btn-primary mt-2 text-sm flex items-center gap-2">
                            <x-icon name="send" class="w-4 h-4" /> Post Comment
                        </button>
                    </div>
                </div>
            </form>
            @else
            <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-6 text-center mb-8">
                <p class="text-slate-600 dark:text-slate-300 mb-3">Login to join the conversation.</p>
                <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
            </div>
            @endif

            {{-- Comments list --}}
            <div class="space-y-6">
                @foreach($post->comments as $comment)
                    @include('blog.partials.comment', ['comment' => $comment])
                @endforeach

                @if($post->comments->isEmpty())
                    <p class="text-center text-slate-500 py-8">No comments yet. Be the first to comment!</p>
                @endif
            </div>
        </section>

        {{-- Related posts --}}
        @if($related->isNotEmpty())
        <section class="border-t border-slate-200 dark:border-slate-700 pt-10">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6">Related Articles</h2>
            <div class="grid sm:grid-cols-2 gap-6">
                @foreach($related as $rel)
                    <a href="{{ route('blog.show', $rel->slug) }}" class="group flex gap-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:shadow-lg transition-all">
                        @if($rel->featured_image)
                            <img src="{{ Storage::url($rel->featured_image) }}" class="w-24 h-24 rounded-lg object-cover flex-shrink-0" alt="{{ $rel->title }}">
                        @else
                            <div class="w-24 h-24 rounded-lg bg-gradient-to-br from-blue-500 to-slate-700 flex-shrink-0"></div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-slate-800 dark:text-slate-100 group-hover:text-blue-600 transition-colors line-clamp-2 mb-1">{{ $rel->title }}</h3>
                            <p class="text-sm text-slate-500 line-clamp-2">{{ $rel->excerpt }}</p>
                            <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                                <span class="flex items-center gap-1"><x-icon name="view" class="w-3 h-3" /> {{ number_format($rel->views_count) }}</span>
                                <span class="flex items-center gap-1"><x-icon name="like" class="w-3 h-3" /> {{ $rel->likes_count }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>
</div>

@push('scripts')
<script>
// Like button
const likeBtn = document.getElementById('like-btn');
if (likeBtn) {
    likeBtn.addEventListener('click', function() {
        const slug = this.dataset.slug;
        fetch(`/blog/${slug}/like`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(r => r.json()).then(data => {
            if (data.error) { alert(data.error); return; }
            const countEl = document.getElementById('like-count');
            countEl.textContent = data.count;
            if (data.liked) {
                likeBtn.classList.remove('bg-slate-100', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'hover:bg-blue-50', 'dark:hover:bg-slate-700');
                likeBtn.classList.add('bg-blue-600', 'text-white');
                likeBtn.querySelector('span:last-child').textContent = 'Liked';
            } else {
                likeBtn.classList.add('bg-slate-100', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'hover:bg-blue-50', 'dark:hover:bg-slate-700');
                likeBtn.classList.remove('bg-blue-600', 'text-white');
                likeBtn.querySelector('span:last-child').textContent = 'Like';
            }
        }).catch(() => alert('An error occurred. Please try again.'));
    });
}

// Rating stars
document.querySelectorAll('.star-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const rating = this.dataset.rating;
        const slug = this.dataset.slug;
        fetch(`/blog/${slug}/rate`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify({ rating: parseInt(rating) }),
        }).then(r => r.json()).then(data => {
            if (data.error) { alert(data.error); return; }
            // Update star display
            document.querySelectorAll('.star-btn').forEach((b, i) => {
                const icon = b.querySelector('svg');
                if (i < rating) {
                    b.classList.add('text-yellow-400');
                    b.classList.remove('text-slate-300', 'dark:text-slate-600');
                    icon.classList.add('fill-current');
                } else {
                    b.classList.remove('text-yellow-400');
                    b.classList.add('text-slate-300', 'dark:text-slate-600');
                    icon.classList.remove('fill-current');
                }
            });
            // Update text
            const textEl = document.querySelector('#rating-stars').nextElementSibling;
            if (textEl) textEl.textContent = `${data.avg.toFixed(1)} (${data.count})`;
        }).catch(() => alert('An error occurred. Please try again.'));
    });
});

// Share tracking
function trackShare(slug, platform) {
    fetch(`/blog/${slug}/share`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
        body: JSON.stringify({ platform: platform }),
    }).catch(() => {});
}

// Copy link
function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    });
}
</script>
@endpush

<style>
.article-content { color: #374151; }
.dark .article-content { color: #cbd5e1; }
.article-content h1, .article-content h2, .article-content h3 { color: #1e293b; font-weight: 700; margin-top: 1.5em; margin-bottom: 0.5em; }
.dark .article-content h1, .dark .article-content h2, .dark .article-content h3 { color: #f1f5f9; }
.article-content p { margin-bottom: 1em; line-height: 1.75; }
.article-content img { border-radius: 0.75rem; margin: 1.5em 0; max-width: 100%; height: auto; }
.article-content ul, .article-content ol { margin-left: 1.5em; margin-bottom: 1em; }
.article-content li { margin-bottom: 0.25em; }
.article-content a { color: #2563eb; text-decoration: underline; }
.article-content blockquote { border-left: 4px solid #2563eb; padding-left: 1rem; font-style: italic; color: #64748b; margin: 1em 0; }
.article-content pre { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1em 0; }
.article-content code { background: #f1f5f9; padding: 0.125rem 0.25rem; border-radius: 0.25rem; font-size: 0.875em; }
.dark .article-content code { background: #334155; }
.article-content pre code { background: transparent; padding: 0; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

@endsection
