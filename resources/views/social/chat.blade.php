@extends('layouts.social')

@section('title', 'Messenger — MiniWorkers')

@section('content')
<div class="fb-chat-container" x-data="{ showInfo: false, messageText: '' }">
    <div class="fb-chat-layout">

        {{-- LEFT: Conversation List --}}
        <aside class="fb-chat-sidebar">
            <div class="fb-chat-sidebar-header">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-bold fb-text">Chats</h2>
                    <button class="fb-nav-icon-btn" title="New message">
                        <x-icon name="create" class="w-5 h-5" />
                    </button>
                </div>
                <div class="fb-search-box mb-3">
                    <x-icon name="search" class="w-4 h-4 fb-text-muted" />
                    <input type="text" placeholder="Search Messenger" class="fb-search-input">
                </div>
            </div>

            <div class="fb-conversation-list">
                @forelse($conversations as $conv)
                    @php
                        $otherUser = null;
                        if (!$conv->is_group && $conv->participants) {
                            foreach ($conv->participants as $p) {
                                if ($p->user_id !== $user->id) { $otherUser = $p->user; break; }
                            }
                        }
                        $isActive = $activeConversation && $activeConversation->id === $conv->id;
                        $lastMsg = $conv->lastMessage;
                        $unread = $conv->participants->where('user_id', $user->id)->first();
                        $unreadCount = 0; // simplified
                    @endphp
                    <a href="{{ route('social.chat', ['c' => $conv->id]) }}"
                       class="fb-conversation-item {{ $isActive ? 'active' : '' }}">
                        <div class="fb-avatar-wrapper">
                            @if($conv->is_group)
                                <div class="fb-avatar fb-avatar-group">
                                    <x-icon name="community" class="w-5 h-5" />
                                </div>
                            @elseif($otherUser)
                                <img src="{{ $otherUser->avatarUrl() }}" alt="{{ $otherUser->name }}" class="fb-avatar">
                            @else
                                <div class="fb-avatar"><x-icon name="user" class="w-5 h-5" /></div>
                            @endif
                        </div>
                        <div class="fb-conv-info">
                            <div class="fb-conv-name fb-text font-semibold">
                                {{ $conv->is_group ? $conv->title : ($otherUser?->name ?? 'Unknown') }}
                            </div>
                            <div class="fb-conv-preview fb-text-muted text-sm truncate">
                                @if($lastMsg)
                                    @if($lastMsg->sender_id === $user->id)<span>You: </span>@endif
                                    {{ $lastMsg->type === 'text' ? Str::limit($lastMsg->body, 40) : ucfirst($lastMsg->type) }}
                                    · {{ $lastMsg->created_at->diffForHumans() }}
                                @else
                                    No messages yet
                                @endif
                            </div>
                        </div>
                        @if($unreadCount > 0)
                            <span class="fb-badge fb-badge-unread">{{ $unreadCount }}</span>
                        @endif
                    </a>
                @empty
                    <div class="text-center py-12 fb-text-muted">
                        <x-icon name="messenger" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                        <p>No conversations yet.</p>
                        <p class="text-sm">Start chatting with people below!</p>
                    </div>
                @endforelse
            </div>

            {{-- Suggested Users --}}
            <div class="fb-chat-suggestions">
                <div class="fb-text-muted text-xs font-semibold uppercase px-3 py-2">People You Can Message</div>
                @foreach($suggestedUsers as $sugUser)
                    <a href="{{ route('social.chat.start', $sugUser) }}" class="fb-suggestion-item">
                        <img src="{{ $sugUser->avatarUrl() }}" class="fb-avatar fb-avatar-sm" alt="{{ $sugUser->name }}">
                        <span class="fb-text text-sm font-medium truncate">{{ $sugUser->name }}</span>
                        <x-icon name="messenger" class="w-4 h-4 fb-text-muted ml-auto" />
                    </a>
                @endforeach
            </div>
        </aside>

        {{-- CENTER: Chat Thread --}}
        <main class="fb-chat-main">
            @if($activeConversation)
                @php
                    $otherUser = null;
                    if (!$activeConversation->is_group) {
                        foreach ($activeConversation->participants as $p) {
                            if ($p->user_id !== $user->id) { $otherUser = $p->user; break; }
                        }
                    }
                @endphp

                {{-- Chat Header --}}
                <div class="fb-chat-header">
                    <a href="{{ route('social.chat') }}" class="fb-nav-icon-btn md:hidden">
                        <x-icon name="arrow-left" class="w-5 h-5" />
                    </a>
                    @if($activeConversation->is_group)
                        <div class="fb-avatar fb-avatar-group"><x-icon name="community" class="w-6 h-6" /></div>
                    @else
                        <img src="{{ $otherUser?->avatarUrl() }}" class="fb-avatar" alt="">
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="fb-text font-semibold truncate">
                            {{ $activeConversation->is_group ? $activeConversation->title : ($otherUser?->name ?? 'Unknown') }}
                        </h3>
                        <p class="fb-text-muted text-xs">
                            @if($activeConversation->is_group)
                                {{ $activeConversation->participants->count() }} members
                            @else
                                Active now
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="fb-nav-icon-btn" title="Voice call" onclick="alert('Voice call feature')">
                            <x-icon name="phone-call" class="w-5 h-5" />
                        </button>
                        <button class="fb-nav-icon-btn" title="Video call" onclick="alert('Video call feature')">
                            <x-icon name="video-call" class="w-5 h-5" />
                        </button>
                        <button class="fb-nav-icon-btn" @click="showInfo = !showInfo" title="Conversation info">
                            <x-icon name="info" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {{-- Messages Area --}}
                <div class="fb-messages-area" id="messagesArea">
                    @if($messages->isEmpty())
                        <div class="fb-empty-chat">
                            <img src="{{ $otherUser?->avatarUrl() }}" class="fb-avatar fb-avatar-lg mb-4" alt="">
                            <h3 class="fb-text font-bold text-lg">{{ $otherUser?->name }}</h3>
                            <p class="fb-text-muted text-sm mb-4">{{ $otherUser?->username }}</p>
                            <p class="fb-text-muted text-sm">You're friends on MiniWorkers</p>
                            <a href="{{ route('social.profile', $otherUser?->username) }}" class="fb-btn fb-btn-primary mt-4">View Profile</a>
                        </div>
                    @else
                        @foreach($messages as $msg)
                            @php $isMe = $msg->sender_id === $user->id; @endphp
                            <div class="fb-message-row {{ $isMe ? 'me' : 'them' }}">
                                @if(!$isMe)
                                    <img src="{{ $msg->sender?->avatarUrl() }}" class="fb-avatar fb-avatar-xs" alt="">
                                @endif
                                <div class="fb-message-bubble {{ $isMe ? 'fb-msg-me' : 'fb-msg-them' }}">
                                    @if($msg->type === 'image' && $msg->body)
                                        <img src="{{ asset('storage/' . $msg->body) }}" class="fb-msg-image" alt="">
                                    @else
                                        {{ $msg->body }}
                                    @endif
                                </div>
                            </div>
                            <div class="fb-message-time {{ $isMe ? 'me' : 'them' }}">{{ $msg->created_at->format('M j, g:i A') }}</div>
                        @endforeach
                    @endif
                </div>

                {{-- Message Composer --}}
                <div class="fb-chat-composer">
                    <button class="fb-nav-icon-btn" title="Add photo">
                        <x-icon name="photo" class="w-6 h-6 fb-text-muted" />
                    </button>
                    <button class="fb-nav-icon-btn" title="Add GIF">
                        <x-icon name="gif" class="w-6 h-6 fb-text-muted" />
                    </button>
                    <button class="fb-nav-icon-btn" title="Add sticker">
                        <x-icon name="sticker" class="w-6 h-6 fb-text-muted" />
                    </button>
                    <form action="{{ route('social.chat.send', $activeConversation) }}" method="POST" class="fb-chat-form" id="chatForm">
                        @csrf
                        <input type="text" name="body" placeholder="Aa"
                               class="fb-chat-input"
                               x-model="messageText"
                               @keydown.enter="if(messageText.trim()){ $el.closest('form').submit(); }">
                    </form>
                    <button class="fb-nav-icon-btn" title="Send" onclick="document.getElementById('chatForm').submit();">
                        <x-icon name="send" class="w-6 h-6 fb-text-muted" />
                    </button>
                </div>
            @else
                {{-- No active conversation --}}
                <div class="fb-chat-empty">
                    <div class="text-center">
                        <x-icon name="messenger" class="w-24 h-24 mx-auto mb-4 opacity-30" />
                        <h2 class="text-2xl font-bold fb-text mb-2">Your Messages</h2>
                        <p class="fb-text-muted mb-6">Send a message to start a chat.</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 max-w-lg mx-auto">
                            @foreach($suggestedUsers->take(4) as $sugUser)
                                <a href="{{ route('social.chat.start', $sugUser) }}" class="fb-suggest-card">
                                    <img src="{{ $sugUser->avatarUrl() }}" class="fb-avatar mb-2" alt="{{ $sugUser->name }}">
                                    <div class="fb-text text-sm font-semibold truncate">{{ $sugUser->name }}</div>
                                    <div class="fb-text-muted text-xs">Message</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </main>

        {{-- RIGHT: Info Panel --}}
        @if($activeConversation && $showInfo)
            <aside class="fb-chat-info-panel">
                <div class="text-center py-4">
                    @if($activeConversation->is_group)
                        <div class="fb-avatar fb-avatar-group fb-avatar-lg mx-auto mb-3"><x-icon name="community" class="w-8 h-8" /></div>
                        <h3 class="fb-text font-bold">{{ $activeConversation->title }}</h3>
                    @else
                        <img src="{{ $otherUser?->avatarUrl() }}" class="fb-avatar fb-avatar-lg mx-auto mb-3" alt="">
                        <h3 class="fb-text font-bold">{{ $otherUser?->name }}</h3>
                        <p class="fb-text-muted text-sm">{{ $otherUser?->username }}</p>
                    @endif
                </div>

                <div class="fb-info-section">
                    <h4 class="fb-text font-semibold text-sm mb-2">Members</h4>
                    @foreach($activeConversation->participants as $p)
                        <div class="fb-info-member">
                            <img src="{{ $p->user?->avatarUrl() }}" class="fb-avatar fb-avatar-sm" alt="">
                            <span class="fb-text text-sm">{{ $p->user?->name }}</span>
                        </div>
                    @endforeach
                </div>

                @if(!$activeConversation->is_group && $otherUser)
                    <a href="{{ route('social.profile', $otherUser->username) }}" class="fb-btn fb-btn-secondary w-full mb-2">
                        <x-icon name="user" class="w-4 h-4 inline" /> View Profile
                    </a>
                @endif

                <form action="{{ route('social.chat.delete', $activeConversation) }}" method="POST" onsubmit="return confirm('Delete this conversation?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="fb-btn fb-btn-danger w-full">
                        <x-icon name="trash" class="w-4 h-4 inline" /> Delete Conversation
                    </button>
                </form>
            </aside>
        @endif
    </div>
</div>

<style>
.fb-chat-container { height: calc(100vh - 56px); overflow: hidden; }
.fb-chat-layout { display: flex; height: 100%; }
.fb-chat-sidebar {
    width: 340px; min-width: 340px;
    border-right: 1px solid var(--fb-border);
    overflow-y: auto; display: flex; flex-direction: column;
}
.fb-chat-sidebar-header { padding: 12px; position: sticky; top: 0; background: var(--fb-card); z-index: 10; }
.fb-conversation-list { flex: 1; overflow-y: auto; }
.fb-conversation-item {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px; border-radius: 8px; margin: 2px 8px;
    cursor: pointer; transition: background 0.15s;
}
.fb-conversation-item:hover { background: var(--fb-hover); }
.fb-conversation-item.active { background: var(--fb-primary); }
.fb-conversation-item.active .fb-conv-name,
.fb-conversation-item.active .fb-conv-preview { color: #fff; }
.fb-conv-info { flex: 1; min-width: 0; }
.fb-chat-suggestions { padding: 8px; border-top: 1px solid var(--fb-border); }
.fb-suggestion-item {
    display: flex; align-items: center; gap: 10px;
    padding: 8px; border-radius: 8px; transition: background 0.15s;
}
.fb-suggestion-item:hover { background: var(--fb-hover); }
.fb-avatar-sm { width: 32px; height: 32px; }
.fb-avatar-xs { width: 28px; height: 28px; }
.fb-avatar-lg { width: 96px; height: 96px; }
.fb-avatar-group { background: var(--fb-primary); color: #fff; display: flex; align-items: center; justify-content: center; }

.fb-chat-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.fb-chat-header {
    display: flex; align-items: center; gap: 12px;
    padding: 8px 16px; border-bottom: 1px solid var(--fb-border);
    background: var(--fb-card);
}
.fb-messages-area {
    flex: 1; overflow-y: auto; padding: 20px;
    display: flex; flex-direction: column; gap: 2px;
}
.fb-empty-chat { text-align: center; margin: auto; }
.fb-message-row { display: flex; align-items: flex-end; gap: 8px; max-width: 70%; }
.fb-message-row.me { margin-left: auto; flex-direction: row-reverse; }
.fb-message-bubble {
    padding: 8px 12px; border-radius: 18px; font-size: 15px;
    word-break: break-word;
}
.fb-msg-me { background: var(--fb-primary); color: #fff; border-bottom-right-radius: 4px; }
.fb-msg-them { background: var(--fb-hover); color: var(--fb-text); border-bottom-left-radius: 4px; }
.fb-msg-image { max-width: 280px; border-radius: 12px; }
.fb-message-time { font-size: 11px; color: var(--fb-text-muted); margin: 2px 0 8px; }
.fb-message-time.me { text-align: right; margin-right: 12px; }
.fb-message-time.them { margin-left: 40px; }

.fb-chat-composer {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-top: 1px solid var(--fb-border);
    background: var(--fb-card);
}
.fb-chat-form { flex: 1; }
.fb-chat-input {
    width: 100%; padding: 10px 16px;
    border-radius: 20px; border: 1px solid var(--fb-border);
    background: var(--fb-hover); color: var(--fb-text); outline: none;
}
.fb-chat-input:focus { border-color: var(--fb-primary); }

.fb-chat-empty {
    flex: 1; display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.fb-suggest-card {
    text-align: center; padding: 16px 12px; border-radius: 12px;
    background: var(--fb-card); box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    transition: transform 0.15s;
}
.fb-suggest-card:hover { transform: translateY(-2px); }

.fb-chat-info-panel {
    width: 300px; min-width: 300px;
    border-left: 1px solid var(--fb-border);
    padding: 16px; overflow-y: auto;
}
.fb-info-section { margin: 16px 0; }
.fb-info-member {
    display: flex; align-items: center; gap: 10px;
    padding: 6px 0;
}

@media (max-width: 768px) {
    .fb-chat-sidebar { display: none; }
    .fb-chat-info-panel { display: none; }
}
</style>

<script>
// Auto-scroll to bottom of messages
document.addEventListener('DOMContentLoaded', function() {
    var area = document.getElementById('messagesArea');
    if (area) { area.scrollTop = area.scrollHeight; }
});
</script>
@endsection
