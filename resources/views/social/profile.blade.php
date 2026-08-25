@extends('layouts.social')

@section('title', $profile->name . ' — Profile')

@section('main_class', 'max-w-[900px] mx-auto px-0 sm:px-4 py-0')

@section('content')
@php $authUser = auth('web')->user(); @endphp

{{-- Profile header with cover photo (Facebook style) --}}
<div class="fb-card mb-4 overflow-hidden">
    {{-- Cover photo --}}
    <div class="relative h-[200px] sm:h-[350px] bg-gradient-to-r from-blue-400 to-purple-500">
        @if($profile->cover_image)
            <img src="{{ Storage::url($profile->cover_image) }}" class="w-full h-full object-cover" alt="Cover">
        @endif
        @if($isOwnProfile)
            <button onclick="document.getElementById('cover-modal').classList.remove('hidden')"
                    class="absolute bottom-4 right-4 fb-btn-secondary px-3 py-1.5 rounded-lg text-sm font-medium flex items-center gap-1.5">
                <x-icon name="cover-photo" class="w-4 h-4" /> Edit Cover
            </button>
        @endif
    </div>

    {{-- Profile info --}}
    <div class="px-4 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3 -mt-8 sm:-mt-12">
            <div class="relative inline-block">
                <img src="{{ $profile->avatarUrl() }}" class="w-32 h-32 sm:w-44 sm:h-44 fb-avatar border-4" style="border-color: var(--fb-card);" alt="{{ $profile->name }}">
                @if($isOwnProfile)
                    <button onclick="document.getElementById('avatar-modal').classList.remove('hidden')"
                            class="absolute bottom-2 right-2 w-8 h-8 rounded-full fb-btn-secondary flex items-center justify-center">
                        <x-icon name="image" class="w-4 h-4" />
                    </button>
                @endif
            </div>
            <div class="flex-1 pb-2">
                <h1 class="text-3xl font-bold flex items-center gap-2">
                    {{ $profile->name }}
                    @if($profile->monetization_enabled)
                        <span title="Monetization Enabled"><x-icon name="monetization" class="w-6 h-6 text-green-600" /></span>
                    @endif
                </h1>
                <div class="fb-text-secondary font-medium mt-1">{{ $profile->followers_count }} followers · {{ $profile->following_count }} following · {{ $friendsCount }} friends</div>
                @if($profile->bio)
                    <div class="mt-2 text-sm">{{ $profile->bio }}</div>
                @endif
            </div>
            <div class="flex gap-2 pb-2">
                @if($isOwnProfile)
                    <a href="{{ route('social.profile.edit') }}" class="fb-btn-secondary px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                        <x-icon name="edit" class="w-5 h-5" /> Edit Profile
                    </a>
                @else
                    <button onclick="toggleFollow({{ $profile->id }})" id="follow-btn"
                            class="px-6 py-2 rounded-lg font-semibold {{ $isFollowing ? 'fb-btn-secondary' : 'fb-btn-primary' }}">
                        {{ $isFollowing ? 'Following' : '+ Follow' }}
                    </button>
                    <a href="{{ route('social.chat') }}?user={{ $profile->id }}" class="fb-btn-secondary px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                        <x-icon name="messenger" class="w-5 h-5" /> Message
                    </a>
                    @if($profile->monetization_enabled)
                    <button onclick="openStarModal({{ $profile->id }})" class="fb-btn-secondary px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                        <x-icon name="star" class="w-5 h-5 text-yellow-500" /> Send Stars
                    </button>
                    @endif
                @endif
            </div>
        </div>

        {{-- Tabs (Facebook style) --}}
        <div class="flex items-center border-t fb-border mt-3 overflow-x-auto">
            <a href="{{ route('social.profile', $profile->username) }}?tab=posts" class="fb-tab-link {{ $tab === 'posts' ? 'active' : '' }}">Posts</a>
            <a href="{{ route('social.profile', $profile->username) }}?tab=about" class="fb-tab-link {{ $tab === 'about' ? 'active' : '' }}">About</a>
            <a href="{{ route('social.profile', $profile->username) }}?tab=friends" class="fb-tab-link {{ $tab === 'friends' ? 'active' : '' }}">Friends</a>
            @if($isOwnProfile)<a href="{{ route('social.profile', $profile->username) }}?tab=followers" class="fb-tab-link {{ $tab === 'followers' ? 'active' : '' }}">Followers</a>@endif
            <a href="{{ route('social.profile', $profile->username) }}?tab=following" class="fb-tab-link {{ $tab === 'following' ? 'active' : '' }}">Following</a>
            <a href="{{ route('social.profile', $profile->username) }}?tab=pages" class="fb-tab-link {{ $tab === 'pages' ? 'active' : '' }}">Pages</a>
            <a href="{{ route('social.profile', $profile->username) }}?tab=groups" class="fb-tab-link {{ $tab === 'groups' ? 'active' : '' }}">Groups</a>
            @if($profile->monetization_enabled)
                <a href="{{ route('social.profile', $profile->username) }}?tab=monetization" class="fb-tab-link {{ $tab === 'monetization' ? 'active' : '' }}">Monetization</a>
            @endif
        </div>
    </div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    {{-- Left column: About / sidebar --}}
    <div class="space-y-4 lg:col-span-1">
        @if($tab === 'about' || $tab === 'posts')
        <div class="fb-card p-4">
            <h3 class="font-bold text-lg mb-3">About</h3>
            <div class="space-y-3 text-sm">
                @if($profile->bio)<div class="flex items-start gap-3"><x-icon name="profile" class="w-5 h-5 flex-shrink-0 fb-text-secondary" /><span>{{ $profile->bio }}</span></div>@endif
                @if($profile->country_code)<div class="flex items-center gap-3"><x-icon name="globe" class="w-5 h-5 flex-shrink-0 fb-text-secondary" /><span>{{ $profile->country?->name ?? $profile->country_code }}</span></div>@endif
                <div class="flex items-center gap-3"><x-icon name="clock" class="w-5 h-5 flex-shrink-0 fb-text-secondary" /><span>Joined {{ $profile->created_at->format('F Y') }}</span></div>
                @if($profile->monetization_enabled)
                    <div class="flex items-center gap-3"><x-icon name="monetization" class="w-5 h-5 flex-shrink-0 text-green-600" /><span class="text-green-600 font-medium">Monetization Enabled</span></div>
                @endif
                <div class="flex items-center gap-3"><x-icon name="star" class="w-5 h-5 flex-shrink-0 text-yellow-500" /><span>{{ $totalStars }} Stars Received</span></div>
            </div>
        </div>
        @endif

        @if($tab === 'pages' || $tab === 'posts')
        @if($pages->isNotEmpty())
        <div class="fb-card p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-lg">Pages</h3>
                @if($isOwnProfile)<a href="{{ route('social.pages.mine') }}" class="text-sm text-blue-600">See all</a>@endif
            </div>
            <div class="space-y-2">
                @foreach($pages as $page)
                    <a href="{{ route('social.page.show', $page) }}" class="fb-right-rail-item">
                        <img src="{{ Storage::url($page->image ?? 'pages/default.png') }}" class="w-12 h-12 rounded-lg object-cover" alt="{{ $page->name }}">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm truncate">{{ $page->name }}</div>
                            <div class="text-xs fb-text-secondary">{{ $page->members()->count() }} members</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
        @endif

        @if($tab === 'groups' || $tab === 'posts')
        @if($groups->isNotEmpty())
        <div class="fb-card p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-bold text-lg">Groups</h3>
                @if($isOwnProfile)<a href="{{ route('social.groups.mine') }}" class="text-sm text-blue-600">See all</a>@endif
            </div>
            <div class="space-y-2">
                @foreach($groups as $group)
                    <a href="{{ route('social.group.show', $group) }}" class="fb-right-rail-item">
                        <img src="{{ Storage::url($group->image ?? 'groups/default.png') }}" class="w-12 h-12 rounded-lg object-cover" alt="{{ $group->name }}">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm truncate">{{ $group->name }}</div>
                            <div class="text-xs fb-text-secondary">{{ $group->members()->where('status','approved')->count() }} members</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
        @endif
    </div>

    {{-- Right column: Tab content --}}
    <div class="lg:col-span-2">
        @if($tab === 'posts')
            @if($isOwnProfile)
                {{-- Mini create post --}}
                <div class="fb-card p-3 mb-4">
                    <div class="flex items-center gap-2">
                        <img src="{{ $authUser->avatarUrl() }}" class="w-10 h-10 fb-avatar" alt="">
                        <a href="{{ route('social.feed') }}#create-post" class="fb-input text-left py-2.5">What's on your mind, {{ explode(' ', $authUser->name)[0] }}?</a>
                    </div>
                </div>
            @endif
            @forelse($posts as $post)
                @include('social.partials.post-card', ['post' => $post])
            @empty
                <div class="fb-card p-8 text-center">
                    <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: var(--fb-hover);"><x-icon name="news-feed" class="w-8 h-8" /></div>
                    <h3 class="font-bold text-lg">No posts yet</h3>
                    <p class="fb-text-secondary text-sm">@if($isOwnProfile)Share your first post!@else{{ $profile->name }} hasn't posted yet.@endif</p>
                </div>
            @endforelse
            @if($posts->hasPages())<div class="text-center py-4">{{ $posts->links() }}</div>@endif

        @elseif($tab === 'friends' || $tab === 'followers' || $tab === 'following')
            <div class="fb-card p-4">
                <h3 class="font-bold text-xl mb-4">{{ ucfirst($tab) }}</h3>
                @php $people = $tab === 'followers' ? $followers : $following; @endphp
                @if($people->isEmpty())
                    <p class="fb-text-secondary text-sm text-center py-6">No {{ $tab }} yet.</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($people as $person)
                            @php $p = $tab === 'followers' ? $person->follower : $person->following; @endphp
                            <div class="fb-card overflow-hidden">
                                <a href="{{ route('social.profile', $p->username) }}">
                                    <img src="{{ $p->avatarUrl() }}" class="w-full h-32 object-cover" alt="{{ $p->name }}">
                                </a>
                                <div class="p-2">
                                    <a href="{{ route('social.profile', $p->username) }}" class="font-medium text-sm hover:underline block truncate">{{ $p->name }}</a>
                                    <div class="text-xs fb-text-secondary">{{ $p->followers_count }} followers</div>
                                    @if(!$isOwnProfile && $authUser->id !== $p->id)
                                    <button onclick="toggleFollow({{ $p->id }})" class="mt-2 w-full fb-btn-secondary py-1 rounded-md text-sm font-medium">Follow</button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if($people->hasPages())<div class="text-center py-4">{{ $people->links() }}</div>@endif
                @endif
            </div>

        @elseif($tab === 'monetization' && $profile->monetization_enabled)
            <div class="fb-card p-4">
                <h3 class="font-bold text-xl mb-4 flex items-center gap-2"><x-icon name="monetization" class="w-6 h-6 text-green-600" /> Monetization</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="fb-card p-4 text-center" style="background: var(--fb-hover);">
                        <div class="text-3xl font-bold text-yellow-500">{{ $totalStars }}</div>
                        <div class="text-sm fb-text-secondary">Stars Received</div>
                    </div>
                    <div class="fb-card p-4 text-center" style="background: var(--fb-hover);">
                        <div class="text-3xl font-bold">${{ number_format($totalStars * 0.01, 2) }}</div>
                        <div class="text-sm fb-text-secondary">Est. Earnings</div>
                    </div>
                </div>
                @if(!$isOwnProfile && $authUser)
                <button onclick="openStarModal({{ $profile->id }})" class="w-full fb-btn-primary py-3 rounded-lg font-semibold mt-4 flex items-center justify-center gap-2">
                    <x-icon name="star" class="w-5 h-5" /> Send Stars to {{ $profile->name }}
                </button>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Avatar upload modal --}}
@if($isOwnProfile)
<div id="avatar-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.6);">
    <div class="fb-card w-full max-w-md p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-lg">Update Profile Picture</h3>
            <button onclick="document.getElementById('avatar-modal').classList.add('hidden')"><x-icon name="x" class="w-5 h-5" /></button>
        </div>
        <form action="{{ route('social.profile.avatar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="image" accept="image/*" class="w-full mb-3" required>
            <button type="submit" class="w-full fb-btn-primary py-2 rounded-lg font-semibold">Upload</button>
        </form>
    </div>
</div>

{{-- Cover upload modal --}}
<div id="cover-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.6);">
    <div class="fb-card w-full max-w-md p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-lg">Update Cover Photo</h3>
            <button onclick="document.getElementById('cover-modal').classList.add('hidden')"><x-icon name="x" class="w-5 h-5" /></button>
        </div>
        <form action="{{ route('social.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ $profile->name }}">
            <input type="file" name="cover_image" accept="image/*" class="w-full mb-3" required>
            <button type="submit" class="w-full fb-btn-primary py-2 rounded-lg font-semibold">Upload Cover</button>
        </form>
    </div>
</div>
@endif

{{-- Star modal --}}
<div id="star-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.6);">
    <div class="fb-card w-full max-w-md p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-lg">Send Stars</h3>
            <button onclick="document.getElementById('star-modal').classList.add('hidden')"><x-icon name="x" class="w-5 h-5" /></button>
        </div>
        <form id="star-form" onsubmit="sendStars(event)">
            @csrf
            <input type="hidden" name="creator_id" id="star-creator-id">
            <div class="text-center mb-4">
                <div class="text-5xl mb-2">⭐</div>
                <p class="text-sm fb-text-secondary">Each star = $0.01. Your balance: ${{ number_format($authUser->balance ?? 0, 2) }}</p>
            </div>
            <div class="flex gap-2 justify-center mb-3">
                <button type="button" onclick="document.getElementById('stars-count').value=1; updateStarCost()" class="fb-btn-secondary px-4 py-2 rounded-lg font-bold">1</button>
                <button type="button" onclick="document.getElementById('stars-count').value=10; updateStarCost()" class="fb-btn-secondary px-4 py-2 rounded-lg font-bold">10</button>
                <button type="button" onclick="document.getElementById('stars-count').value=50; updateStarCost()" class="fb-btn-secondary px-4 py-2 rounded-lg font-bold">50</button>
                <button type="button" onclick="document.getElementById('stars-count').value=100; updateStarCost()" class="fb-btn-secondary px-4 py-2 rounded-lg font-bold">100</button>
            </div>
            <input type="hidden" name="stars_count" id="stars-count" value="1">
            <div id="star-cost" class="text-center font-bold text-lg mb-3">$0.01</div>
            <input type="text" name="message" placeholder="Add a message (optional)" class="fb-input mb-3 text-sm">
            <button type="submit" class="w-full fb-btn-primary py-2.5 rounded-lg font-semibold flex items-center justify-center gap-2">
                <x-icon name="star" class="w-5 h-5" /> Send Stars
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleFollow(userId) {
    fetch(`/social/follow/${userId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        const btn = document.getElementById('follow-btn');
        if (btn) {
            btn.textContent = data.following ? 'Following' : '+ Follow';
            btn.className = data.following ? 'px-6 py-2 rounded-lg font-semibold fb-btn-secondary' : 'px-6 py-2 rounded-lg font-semibold fb-btn-primary';
        }
    });
}

function openStarModal(creatorId) {
    document.getElementById('star-creator-id').value = creatorId;
    document.getElementById('star-modal').classList.remove('hidden');
}

function updateStarCost() {
    const count = parseInt(document.getElementById('stars-count').value);
    document.getElementById('star-cost').textContent = '$' + (count * 0.01).toFixed(2);
}

function sendStars(event) {
    event.preventDefault();
    const form = event.target;
    const creatorId = document.getElementById('star-creator-id').value;
    const formData = new FormData(form);
    fetch(`/social/stars/${creatorId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        alert(data.message || 'Stars sent!');
        document.getElementById('star-modal').classList.add('hidden');
    });
}
</script>
@endpush
