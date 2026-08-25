@extends('layouts.app')
@section('title', 'Blog — ' . ($siteName ?? 'MiniWorkers'))
@section('content')

{{-- Phoenix-style full-screen blog header --}}
<div class="relative bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 text-white overflow-hidden">
    {{-- Decorative pattern --}}
    <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%220.4%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-16 sm:py-24">
        <div class="text-center max-w-3xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur text-sm font-medium mb-6">
                <x-icon name="blog" class="w-4 h-4" />
                <span>The MiniWorkers Blog</span>
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-4">
                Insights, Tips & Stories
            </h1>
            <p class="text-lg sm:text-xl text-blue-100 mb-8">
                Discover the latest trends in microjobs, freelancing, and the gig economy. Learn from experts, grow your income, and build your career.
            </p>

            {{-- Search bar --}}
            <form method="GET" action="{{ route('blog.index') }}" class="max-w-2xl mx-auto">
                <div class="relative flex items-center">
                    <div class="absolute left-4 text-slate-400">
                        <x-icon name="search" class="w-5 h-5" />
                    </div>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Search articles..."
                           class="w-full pl-12 pr-32 py-4 rounded-2xl text-slate-800 bg-white shadow-2xl focus:ring-4 focus:ring-blue-400/50 focus:outline-none text-lg">
                    <button type="submit" class="absolute right-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                        Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Category filter bar --}}
<div class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-16 z-30 transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center gap-2 overflow-x-auto py-3 scrollbar-hide">
            <a href="{{ route('blog.index') }}"
               class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors {{ !request('category') ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-600' }}">
                All Posts
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('blog.index', ['category' => $cat->slug]) }}"
                   class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors {{ request('category') === $cat->slug ? 'bg-blue-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-600' }}">
                    {{ $cat->name }}
                    <span class="ml-1 text-xs opacity-60">({{ $cat->published_posts_count }})</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="bg-slate-50 dark:bg-slate-900 min-h-screen transition-colors">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">

        {{-- Featured posts (only on first page, no search) --}}
        @if($featured->isNotEmpty() && !request('q') && !request('category') && request('page', 1) == 1)
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <x-icon name="star" class="w-6 h-6 text-yellow-500" />
                Featured Articles
            </h2>
            <div class="grid lg:grid-cols-3 gap-6">
                {{-- Large featured post --}}
                @php $mainFeature = $featured->first(); @endphp
                <a href="{{ route('blog.show', $mainFeature->slug) }}" class="lg:col-span-2 group block relative rounded-2xl overflow-hidden shadow-lg h-96">
                    @if($mainFeature->featured_image)
                        <img src="{{ Storage::url($mainFeature->featured_image) }}" alt="{{ $mainFeature->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-blue-600 to-slate-800 flex items-center justify-center">
                            <x-icon name="blog" class="w-20 h-20 text-white/30" />
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                        @if($mainFeature->category)
                            <span class="inline-block px-3 py-1 bg-blue-600 rounded-full text-xs font-semibold mb-3">{{ $mainFeature->category->name }}</span>
                        @endif
                        <h3 class="text-2xl font-bold mb-2 group-hover:text-blue-300 transition-colors">{{ $mainFeature->title }}</h3>
                        <p class="text-sm text-slate-200 line-clamp-2">{{ $mainFeature->excerpt }}</p>
                        <div class="flex items-center gap-4 mt-3 text-xs text-slate-300">
                            <span class="flex items-center gap-1"><x-icon name="view" class="w-4 h-4" /> {{ number_format($mainFeature->views_count) }}</span>
                            <span class="flex items-center gap-1"><x-icon name="like" class="w-4 h-4" /> {{ $mainFeature->likes_count }}</span>
                            <span class="flex items-center gap-1"><x-icon name="comment" class="w-4 h-4" /> {{ $mainFeature->comments_count }}</span>
                            <span class="flex items-center gap-1"><x-icon name="clock" class="w-4 h-4" /> {{ $mainFeature->reading_time }} min</span>
                        </div>
                    </div>
                </a>

                {{-- Side featured posts --}}
                <div class="space-y-6">
                    @foreach($featured->slice(1) as $f)
                        <a href="{{ route('blog.show', $f->slug) }}" class="group block relative rounded-2xl overflow-hidden shadow-lg h-44">
                            @if($f->featured_image)
                                <img src="{{ Storage::url($f->featured_image) }}" alt="{{ $f->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-slate-600 to-slate-800"></div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                                <h3 class="font-bold text-lg group-hover:text-blue-300 transition-colors line-clamp-2">{{ $f->title }}</h3>
                                <div class="flex items-center gap-3 mt-2 text-xs text-slate-300">
                                    <span class="flex items-center gap-1"><x-icon name="view" class="w-3 h-3" /> {{ number_format($f->views_count) }}</span>
                                    <span class="flex items-center gap-1"><x-icon name="like" class="w-3 h-3" /> {{ $f->likes_count }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- Sort bar --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
                @if(request('q'))
                    Search results for "{{ request('q') }}"
                @elseif(request('category'))
                    {{ $categories->where('slug', request('category'))->first()?->name ?? 'Category' }}
                @else
                    Latest Articles
                @endif
                <span class="text-base font-normal text-slate-500">({{ $posts->total() }} posts)</span>
            </h2>
            <div class="flex items-center gap-2">
                <select onchange="window.location.href=this.value" class="input text-sm py-2">
                    <option value="{{ route('blog.index', array_filter(['q' => request('q'), 'category' => request('category'), 'sort' => 'latest'])) }}" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="{{ route('blog.index', array_filter(['q' => request('q'), 'category' => request('category'), 'sort' => 'popular'])) }}" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Viewed</option>
                    <option value="{{ route('blog.index', array_filter(['q' => request('q'), 'category' => request('category'), 'sort' => 'liked'])) }}" {{ request('sort') === 'liked' ? 'selected' : '' }}>Most Liked</option>
                    <option value="{{ route('blog.index', array_filter(['q' => request('q'), 'category' => request('category'), 'sort' => 'rated'])) }}" {{ request('sort') === 'rated' ? 'selected' : '' }}>Top Rated</option>
                </select>
            </div>
        </div>

        {{-- Blog posts grid --}}
        @if($posts->isNotEmpty())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            @foreach($posts as $post)
                <article class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden hover:shadow-xl transition-all group">
                    <a href="{{ route('blog.show', $post->slug) }}" class="block relative h-48 overflow-hidden">
                        @if($post->featured_image)
                            <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-blue-500 to-slate-700 flex items-center justify-center">
                                <x-icon name="blog" class="w-12 h-12 text-white/30" />
                            </div>
                        @endif
                        @if($post->is_featured)
                            <span class="absolute top-3 right-3 px-2 py-1 bg-yellow-500 text-white text-xs font-bold rounded-full flex items-center gap-1">
                                <x-icon name="star" class="w-3 h-3" /> Featured
                            </span>
                        @endif
                        @if($post->category)
                            <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/90 dark:bg-slate-900/90 text-blue-600 text-xs font-semibold rounded-full">{{ $post->category->name }}</span>
                        @endif
                    </a>
                    <div class="p-5">
                        <a href="{{ route('blog.show', $post->slug) }}">
                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 group-hover:text-blue-600 transition-colors line-clamp-2 mb-2">{{ $post->title }}</h3>
                        </a>
                        <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-2 mb-4">{{ $post->excerpt }}</p>

                        {{-- Stats bar --}}
                        <div class="flex items-center justify-between pt-3 border-t border-slate-100 dark:border-slate-700">
                            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1" title="Views">
                                    <x-icon name="view" class="w-3.5 h-3.5" /> {{ number_format($post->views_count) }}
                                </span>
                                <span class="flex items-center gap-1" title="Likes">
                                    <x-icon name="like" class="w-3.5 h-3.5" /> {{ $post->likes_count }}
                                </span>
                                <span class="flex items-center gap-1" title="Comments">
                                    <x-icon name="comment" class="w-3.5 h-3.5" /> {{ $post->comments_count }}
                                </span>
                                @if($post->ratings_count > 0)
                                <span class="flex items-center gap-1" title="Rating">
                                    <x-icon name="rate" class="w-3.5 h-3.5 text-yellow-500" /> {{ number_format($post->averageRating(), 1) }}
                                </span>
                                @endif
                            </div>
                            <span class="text-xs text-slate-400 flex items-center gap-1">
                                <x-icon name="clock" class="w-3.5 h-3.5" /> {{ $post->reading_time }}m
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="flex justify-center">
            {{ $posts->appends(request()->query())->links() }}
        </div>

        @else
        <div class="text-center py-20">
            <x-icon name="search" class="w-16 h-16 text-slate-300 dark:text-slate-600 mx-auto mb-4" />
            <h3 class="text-xl font-bold text-slate-700 dark:text-slate-300 mb-2">No articles found</h3>
            <p class="text-slate-500 mb-6">Try adjusting your search or browse all posts.</p>
            <a href="{{ route('blog.index') }}" class="btn btn-primary">View All Posts</a>
        </div>
        @endif

        {{-- Trending sidebar section --}}
        @if($trending->isNotEmpty() && !request('q'))
        <section class="mt-16 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 sm:p-8">
            <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
                <x-icon name="trending" class="w-5 h-5 text-blue-600" />
                Trending Now
            </h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach($trending as $i => $t)
                    <a href="{{ route('blog.show', $t->slug) }}" class="group flex gap-3 items-start">
                        <span class="text-3xl font-extrabold text-slate-200 dark:text-slate-700 group-hover:text-blue-400 transition-colors">{{ $i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-300 group-hover:text-blue-600 transition-colors line-clamp-2">{{ $t->title }}</h4>
                            <span class="text-xs text-slate-400 flex items-center gap-1 mt-1">
                                <x-icon name="view" class="w-3 h-3" /> {{ number_format($t->views_count) }} views
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

    </div>
</div>

<style>
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

@endsection
