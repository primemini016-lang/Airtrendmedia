@extends('layouts.social')

@section('title', 'Messenger — Airtrendmedia')

@section('content')
<div class="fb-chat-container" x-data="chatComponent()" x-init="init()">
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
                                <span x-show="typingUsers.length === 0">Active now</span>
                                <span x-show="typingUsers.length > 0" style="color: #31a24c;">
                                    <span x-text="typingUsers.map(u => u.name).join(', ')"></span>
                                    <span x-show="typingUsers.length === 1"> is typing…</span>
                                    <span x-show="typingUsers.length > 1"> are typing…</span>
                                </span>
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
                            <p class="fb-text-muted text-sm">You're friends on Airtrendmedia</p>
                            <a href="{{ route('social.profile', $otherUser?->username) }}" class="fb-btn fb-btn-primary mt-4">View Profile</a>
                        </div>
                    @else
                        @foreach($messages as $msg)
                            @php $isMe = $msg->sender_id === $user->id; @endphp
                            <div class="fb-message-row {{ $isMe ? 'me' : 'them' }}">
                                @if(!$isMe)
                                    <img src="{{ $msg->sender?->avatarUrl() }}" class="fb-avatar fb-avatar-xs" alt="">
                                @endif
                                <div class="fb-message-group">
                                    <div class="fb-message-bubble {{ $isMe ? 'fb-msg-me' : 'fb-msg-them' }}"
                                         @contextmenu.prevent="toggleEmojiPicker({{ $msg->id }})"
                                         @dblclick="toggleEmojiPicker({{ $msg->id }})"
                                         style="position: relative;">
                                        @if($msg->type === 'image' && $msg->body)
                                            <img src="{{ asset('storage/' . $msg->body) }}" class="fb-msg-image" alt="">
                                        @else
                                            {{ $msg->body }}
                                        @endif
                                        {{-- Emoji reaction picker --}}
                                        <div class="fb-emoji-picker" x-show="showEmojiPicker && activeMessageId === {{ $msg->id }}" x-cloak>
                                            @php
                                                $emojis = ['👍','❤️','😂','😮','😢','🔥','🎉','🙏','👏','😍','💯','✅','😍','🤔','😮','😎','🥳','😢','😡','👍'];
                                            @endphp
                                            @foreach(array_unique($emojis) as $emo)
                                                <button type="button" class="fb-emoji-btn"
                                                    @click="reactToMessage({{ $msg->id }}, '{{ $emo }}'); showEmojiPicker = false; activeMessageId = null;"
                                                    data-sound="like">{{ $emo }}</button>
                                            @endforeach
                                        </div>
                                        {{-- Quick react button --}}
                                        <button type="button" class="fb-msg-react-btn" @click="toggleEmojiPicker({{ $msg->id }})" title="React">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                                        </button>
                                    </div>
                                    {{-- Reactions display --}}
                                    <div class="fb-msg-reactions" id="reactions-{{ $msg->id }}">
                                        @php
                                            $msgReactions = method_exists($msg, 'reactions') ? $msg->reactions()->get()->groupBy('emoji') : collect();
                                        @endphp
                                        @foreach($msgReactions as $emoji => $group)
                                            <span class="fb-msg-reaction-chip" title="{{ $group->pluck('user.username')->filter()->join(', ') }}">
                                                {{ $emoji }} @if($group->count() > 1)<b>{{ $group->count() }}</b>@endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="fb-message-time {{ $isMe ? 'me' : 'them' }}">{{ $msg->created_at->format('M j, g:i A') }}</div>
                        @endforeach
                        {{-- Typing indicator bubble --}}
                        <div class="fb-message-row them" x-show="typingUsers.length > 0" x-cloak>
                            @if($otherUser)<img src="{{ $otherUser->avatarUrl() }}" class="fb-avatar fb-avatar-xs" alt="">@endif
                            <div class="fb-message-bubble fb-msg-them fb-typing-bubble">
                                <span class="fb-typing-dot"></span>
                                <span class="fb-typing-dot"></span>
                                <span class="fb-typing-dot"></span>
                            </div>
                        </div>
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
                               @input="sendTyping(true)"
                               @blur="sendTyping(false)"
                               @keydown.enter="if(messageText.trim()){ sendTyping(false); $el.closest('form').submit(); }">
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
        @if($activeConversation)
            <aside class="fb-chat-info-panel" x-show="showInfo" x-transition.opacity>
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

[x-cloak] { display: none !important; }

.fb-message-group { position: relative; max-width: 75%; }
.fb-emoji-picker {
    position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%);
    margin-bottom: 6px; background: var(--fb-card); border: 1px solid var(--fb-border);
    border-radius: 999px; padding: 4px 6px; display: flex; gap: 2px; flex-wrap: wrap;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 20; max-width: 240px;
}
.fb-emoji-btn {
    background: transparent; border: none; font-size: 18px; cursor: pointer;
    padding: 2px 3px; border-radius: 50%; transition: background .15s, transform .15s;
}
.fb-emoji-btn:hover { background: var(--fb-hover); transform: scale(1.25); }
.fb-msg-react-btn {
    position: absolute; top: -8px; right: -8px; width: 22px; height: 22px;
    border-radius: 50%; background: var(--fb-card); border: 1px solid var(--fb-border);
    display: none; align-items: center; justify-content: center; cursor: pointer;
    color: var(--fb-text-muted); box-shadow: 0 1px 4px rgba(0,0,0,0.15);
}
.fb-message-bubble:hover .fb-msg-react-btn { display: flex; }
.fb-msg-reactions {
    display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px; min-height: 20px;
}
.fb-msg-reaction-chip {
    background: var(--fb-card); border: 1px solid var(--fb-border); border-radius: 999px;
    padding: 1px 6px; font-size: 12px; cursor: pointer; display: inline-flex;
    align-items: center; gap: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.fb-msg-reaction-chip b { font-size: 10px; color: var(--fb-text-muted); }

.fb-typing-bubble { display: inline-flex; gap: 4px; align-items: center; padding: 10px 14px; }
.fb-typing-dot {
    width: 7px; height: 7px; border-radius: 50%; background: var(--fb-text-muted);
    animation: fb-typing-bounce 1.2s infinite ease-in-out;
}
.fb-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.fb-typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes fb-typing-bounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
    30% { transform: translateY(-5px); opacity: 1; }
}

@media (max-width: 768px) {
    .fb-chat-sidebar { display: none; }
    .fb-chat-info-panel { display: none; }
}
</style>

<script src="{{ asset('js/action-sounds.js') }}" defer></script>
<script>
// Register Alpine chat component BEFORE Alpine initializes
document.addEventListener('alpine:init', function () {
    window.Alpine.data('chatComponent', function () {
        return {
            showInfo: false,
            messageText: '',
            typingUsers: [],
            showEmojiPicker: false,
            activeMessageId: null,
            pollTyping() {
                @if($activeConversation)
                fetch('{{ route('social.chat.typing-status', $activeConversation) }}', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        this.typingUsers = data.typing || [];
                        if (this.typingUsers.length && window.AirtrendSounds) AirtrendSounds.message();
                    }.bind(this))
                    .catch(function () {});
                @endif
            },
            sendTyping(isTyping) {
                @if($activeConversation)
                fetch('{{ route('social.chat.typing', $activeConversation) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ is_typing: isTyping })
                }).catch(function () {});
                @endif
            },
            reactToMessage(messageId, emoji) {
                var self = this;
                fetch('/messenger/message/' + messageId + '/react', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ emoji: emoji })
                }).then(function (r) { return r.json(); }).then(function (data) {
                    self.renderReactions(messageId, data.reactions || []);
                    if (window.AirtrendSounds) AirtrendSounds.like();
                }).catch(function () {});
            },
            renderReactions(messageId, reactions) {
                var el = document.getElementById('reactions-' + messageId);
                if (!el) return;
                if (!reactions.length) { el.innerHTML = ''; return; }
                el.innerHTML = reactions.map(function (r) {
                    return '<span class="fb-msg-reaction-chip" title="' + (r.users || []).join(', ') + '">' +
                        r.emoji + (r.count > 1 ? ' <b>' + r.count + '</b>' : '') + '</span>';
                }).join('');
            },
            toggleEmojiPicker(messageId) {
                if (this.activeMessageId === messageId) { this.showEmojiPicker = false; this.activeMessageId = null; }
                else { this.showEmojiPicker = true; this.activeMessageId = messageId; }
            },
            init() {
                var self = this;
                setInterval(function () { self.pollTyping(); }, 3000);
                this.pollTyping();
            }
        };
    });
});
// Auto-scroll to bottom of messages on load and after rendering.
document.addEventListener('DOMContentLoaded', function() {
    var area = document.getElementById('messagesArea');
    if (area) { area.scrollTop = area.scrollHeight; }
});
</script>
@endsection
