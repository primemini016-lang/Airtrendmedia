@extends('layouts.social')

@section('title', 'Explore — MiniWorkers')

@section('content')
<div class="fb-main-container">
    {{-- Search Bar --}}
    <div class="fb-explore-header">
        <div class="fb-search-box fb-search-large">
            <x-icon name="search" class="w-5 h-5 fb-text-muted" />
            <form action="{{ route('social.explore') }}" method="GET" class="flex-1">
                <input type="text" name="q" value="{{ $query }}" placeholder="Search MiniWorkers" class="fb-search-input">
            </form>
        </div>
    </div>

    <div class="fb-content-area">

        {{-- People Section --}}
        <section class="fb-card fb-section-card">
            <div class="fb-card-header">
                <h2 class="fb-card-title">
                    <x-icon name="friend-suggestions" class="w-6 h-6 fb-primary-text" />
                    {{ $query ? 'People matching "' . $query . '"' : 'Discover People' }}
                </h2>
            </div>
            <div class="fb-people-grid">
                @forelse($users as $person)
                    <div class="fb-person-card">
                        <a href="{{ route('social.profile', $person->username) }}">
                            <img src="{{ $person->avatarUrl() }}" class="fb-person-avatar" alt="{{ $person->name }}">
                        </a>
                        <a href="{{ route('social.profile', $person->username) }}" class="fb-person-name">{{ $person->name }}</a>
                        <p class="fb-person-bio">{{ $person->bio ?? 'No bio available' }}</p>
                        <div class="fb-person-stats">
                            <span><strong>{{ $person->followers_count ?? 0 }}</strong> followers</span>
                        </div>
                        <form action="{{ route('social.follow', $person) }}" method="POST">
                            @csrf
                            <button type="submit" class="fb-btn fb-btn-primary w-full mt-2">
                                <x-icon name="user-plus" class="w-4 h-4 inline" /> Follow
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="fb-empty-state col-span-full">
                        <x-icon name="search" class="w-16 h-16 mx-auto mb-3 opacity-30" />
                        <p class="fb-text-muted">{{ $query ? 'No people found for "' . $query . '"' : 'No users to show' }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Posts Section (only when searching) --}}
        @if($query)
            <section class="fb-card fb-section-card mt-4">
                <div class="fb-card-header">
                    <h2 class="fb-card-title">
                        <x-icon name="news-feed" class="w-6 h-6 fb-primary-text" />
                        Posts matching "{{ $query }}"
                    </h2>
                </div>
                <div class="fb-posts-feed">
                    @forelse($posts as $post)
                        @include('social.partials.post-card', ['post' => $post])
                    @empty
                        <div class="fb-empty-state">
                            <x-icon name="news-feed" class="w-16 h-16 mx-auto mb-3 opacity-30" />
                            <p class="fb-text-muted">No posts found for "{{ $query }}"</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif

        {{-- Trending Topics (when not searching) --}}
        @if(!$query)
            <section class="fb-card fb-section-card mt-4">
                <div class="fb-card-header">
                    <h2 class="fb-card-title">
                        <x-icon name="trending-feed" class="w-6 h-6 fb-primary-text" />
                        Trending
                    </h2>
                </div>
                <div class="fb-trending-list">
                    <div class="fb-trending-item">
                        <div class="fb-trending-rank">#1</div>
                        <div>
                            <div class="fb-text font-semibold">MiniWorkers Marketplace</div>
                            <div class="fb-text-muted text-sm">2.4K posts</div>
                        </div>
                    </div>
                    <div class="fb-trending-item">
                        <div class="fb-trending-rank">#2</div>
                        <div>
                            <div class="fb-text font-semibold">Freelance Tips</div>
                            <div class="fb-text-muted text-sm">1.8K posts</div>
                        </div>
                    </div>
                    <div class="fb-trending-item">
                        <div class="fb-trending-rank">#3</div>
                        <div>
                            <div class="fb-text font-semibold">Remote Work</div>
                            <div class="fb-text-muted text-sm">1.2K posts</div>
                        </div>
                    </div>
                </div>
            </section>
        @endif
    </div>
</div>

<style>
.fb-explore-header { padding: 12px 0; max-width: 880px; margin: 0 auto; }
.fb-search-large { padding: 8px 16px; }
.fb-section-card { max-width: 880px; margin: 0 auto; }
.fb-people-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px; padding: 16px;
}
.fb-person-card {
    text-align: center; padding: 16px 12px;
    border: 1px solid var(--fb-border); border-radius: 8px;
    transition: box-shadow 0.15s;
}
.fb-person-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.fb-person-avatar {
    width: 80px; height: 80px; border-radius: 50%;
    object-fit: cover; margin: 0 auto 8px; display: block;
}
.fb-person-name { font-weight: 600; color: var(--fb-text); display: block; }
.fb-person-name:hover { text-decoration: underline; }
.fb-person-bio {
    font-size: 13px; color: var(--fb-text-muted);
    margin: 4px 0 8px; display: -webkit-box;
    -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.fb-person-stats { font-size: 12px; color: var(--fb-text-muted); margin-bottom: 8px; }
.fb-trending-list { padding: 8px 0; }
.fb-trending-item {
    display: flex; align-items: center; gap: 16px;
    padding: 12px 16px; cursor: pointer; transition: background 0.15s;
}
.fb-trending-item:hover { background: var(--fb-hover); }
.fb-trending-rank {
    font-weight: 700; color: var(--fb-primary);
    font-size: 18px; min-width: 32px;
}
</style>
@endsection
