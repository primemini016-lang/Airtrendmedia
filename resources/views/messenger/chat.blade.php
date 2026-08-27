@extends('layouts.messenger')

@section('title', 'Messenger — Airtrendmedia')

@section('content')
<div class="fb-chat-container" x-data="chatComponent()" x-init="init()">
    <div class="fb-chat-layout">

        {{-- LEFT: Conversation List --}}
        <aside class="fb-chat-sidebar">
            <div class="fb-chat-sidebar-header">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-bold fb-text">Chats</h2>
                    <button class="fb-nav-icon-btn" title="New message" onclick="document.getElementById('newChatSearch').classList.toggle('hidden')">
                        <x-icon name="create" class="w-5 h-5" />
                    </button>
                </div>
                <div class="fb-search-box mb-3">
                    <x-icon name="search" class="w-4 h-4 fb-text-muted" />
                    <input type="text" id="newChatSearch" placeholder="Search people to message" class="fb-search-input" oninput="searchChatUsers(this.value)">
                </div>
                <div id="chatSearchResults" class="hidden bg-white dark:bg-slate-800 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
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
                    @endphp
                    <a href="{{ route('messenger.chat', ['c' => $conv->id]) }}"
                       class="fb-conversation-item {{ $isActive ? 'active' : '' }}">
                        <div class="fb-avatar-wrapper">
                            @if($conv->is_group)
                                <div class="fb-avatar fb-avatar-group">
                                    <x-icon name="user-group" class="w-5 h-5" />
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
                                    {{ $lastMsg->message_type === 'text' ? Str::limit($lastMsg->body, 40) : ucfirst($lastMsg->message_type ?? 'text') }}
                                    · {{ $lastMsg->created_at->diffForHumans() }}
                                @else
                                    No messages yet
                                @endif
                            </div>
                        </div>
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
                    <a href="{{ route('messenger.chat.start', $sugUser) }}" class="fb-suggestion-item">
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
                    <a href="{{ route('messenger.chat') }}" class="fb-nav-icon-btn md:hidden">
                        <x-icon name="arrow-left" class="w-5 h-5" />
                    </a>
                    @if($activeConversation->is_group)
                        <div class="fb-avatar fb-avatar-group"><x-icon name="user-group" class="w-6 h-6" /></div>
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
                        <button class="fb-nav-icon-btn" @click="showInfo = !showInfo" title="Conversation info">
                            <x-icon name="info" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {{-- Messages Area --}}
                <div class="fb-messages-area" id="messagesArea">
                    @if($messages->isEmpty())
                        <div class="fb-empty-chat">
                            @if($otherUser)
                                <img src="{{ $otherUser->avatarUrl() }}" class="fb-avatar fb-avatar-lg mb-4" alt="">
                                <h3 class="fb-text font-bold text-lg">{{ $otherUser->name }}</h3>
                                <p class="fb-text-muted text-sm mb-4">{{ $otherUser->username }}</p>
                                <p class="fb-text-muted text-sm">You're connected on Airtrendmedia</p>
                            @else
                                <h3 class="fb-text font-bold text-lg">Start chatting</h3>
                                <p class="fb-text-muted text-sm">Send the first message!</p>
                            @endif
                        </div>
                    @else
                        @foreach($messages as $msg)
                            @include('messenger.partials.chat-message', ['message' => $msg])
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
                    <label class="fb-nav-icon-btn cursor-pointer" title="Attach image or file">
                        <x-icon name="photo" class="w-6 h-6 fb-text-muted" />
                        <input type="file" name="attachment" id="chatAttachment" class="hidden" accept="image/*,application/pdf,.doc,.docx,.zip,audio/*,video/*" onchange="sendAttachment()">
                    </label>
                    <form action="{{ route('messenger.chat.send', $activeConversation) }}" method="POST" enctype="multipart/form-data" class="fb-chat-form" id="chatForm" onsubmit="return false;">
                        @csrf
                        <input type="hidden" name="reply_to_id" id="replyToId" value="">
                        <input type="text" name="body" placeholder="Aa"
                               class="fb-chat-input"
                               x-model="messageText"
                               @input="sendTyping(true)"
                               @blur="sendTyping(false)"
                               @keydown.enter.prevent="if(messageText.trim()){ sendMessage(); }">
                    </form>
                    <button class="fb-nav-icon-btn" title="Send" @click="if(messageText.trim()){ sendMessage(); }">
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
                                <a href="{{ route('messenger.chat.start', $sugUser) }}" class="fb-suggest-card">
                                    <img src="{{ $sugUser->avatarUrl() }}" class="fb-avatar mb-2 mx-auto" alt="{{ $sugUser->name }}">
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
            <aside class="fb-chat-info-panel" x-show="showInfo" x-transition.opacity x-cloak>
                <div class="text-center py-4">
                    @if($activeConversation->is_group)
                        <div class="fb-avatar fb-avatar-group fb-avatar-lg mx-auto mb-3"><x-icon name="user-group" class="w-8 h-8" /></div>
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

                <form action="{{ route('messenger.chat.delete', $activeConversation) }}" method="POST" onsubmit="return confirm('Delete this conversation?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="fb-btn fb-btn-danger w-full mb-2">
                        <x-icon name="trash" class="w-4 h-4 inline" /> Delete Conversation
                    </button>
                </form>
            </aside>
        @endif
    </div>
</div>

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
                fetch('/messenger/{{ $activeConversation->id }}/typing-status', { headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var wasTyping = this.typingUsers.length > 0;
                        this.typingUsers = data.typing || [];
                        if (!wasTyping && this.typingUsers.length && window.AirtrendSounds) AirtrendSounds.message();
                    }.bind(this))
                    .catch(function () {});
                @endif
            },
            sendTyping(isTyping) {
                @if($activeConversation)
                fetch('/messenger/{{ $activeConversation->id }}/typing', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ is_typing: isTyping })
                }).catch(function () {});
                @endif
            },
            sendMessage() {
                @if($activeConversation)
                var self = this;
                var text = this.messageText.trim();
                if (!text) return;
                var formData = new FormData();
                formData.append('body', text);
                var replyId = document.getElementById('replyToId');
                if (replyId && replyId.value) formData.append('reply_to_id', replyId.value);
                fetch('/messenger/{{ $activeConversation->id }}/send', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                }).then(function (r) { return r.json(); }).then(function (data) {
                    if (data.html) {
                        var area = document.getElementById('messagesArea');
                        if (area) {
                            area.insertAdjacentHTML('beforeend', data.html);
                            area.scrollTop = area.scrollHeight;
                        }
                    }
                    self.messageText = '';
                    self.sendTyping(false);
                    if (replyId) replyId.value = '';
                    if (window.AirtrendSounds) AirtrendSounds.send();
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

// React to a message (global so partials can dispatch the event)
function reactToMessage(messageId, emoji) {
    fetch('/messenger/message/' + messageId + '/react', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ emoji: emoji })
    }).then(function (r) { return r.json(); }).then(function (data) {
        var el = document.getElementById('reactions-' + messageId);
        if (!el) return;
        var reactions = data.reactions || [];
        if (!reactions.length) { el.innerHTML = ''; return; }
        el.innerHTML = reactions.map(function (r) {
            return '<span class="fb-msg-reaction-chip" title="' + (r.users || []).join(', ') + '">' +
                r.emoji + (r.count > 1 ? ' <b>' + r.count + '</b>' : '') + '</span>';
        }).join('');
        if (window.AirtrendSounds) AirtrendSounds.like();
    }).catch(function () {});
}
window.reactToMessage = reactToMessage;

// Search users to start a new chat
function searchChatUsers(q) {
    var results = document.getElementById('chatSearchResults');
    if (!results) return;
    if (q.length < 2) { results.classList.add('hidden'); results.innerHTML = ''; return; }
    fetch('/messenger/search?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.users || !data.users.length) {
                results.innerHTML = '<div class="p-3 text-sm fb-text-muted">No users found.</div>';
                results.classList.remove('hidden');
                return;
            }
            results.innerHTML = data.users.map(function (u) {
                return '<a href="/messenger/start/' + u.id + '" class="flex items-center gap-2 p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded">' +
                    '<img src="' + (u.avatar_url || ('https://ui-avatars.com/api/?name=' + encodeURIComponent(u.name))) + '" class="w-8 h-8 rounded-full">' +
                    '<div><div class="text-sm font-medium fb-text">' + u.name + '</div><div class="text-xs fb-text-muted">@' + u.username + '</div></div></a>';
            }).join('');
            results.classList.remove('hidden');
        }).catch(function () {});
}

// Send attachment (image/file)
function sendAttachment() {
    var input = document.getElementById('chatAttachment');
    if (!input || !input.files.length) return;
    @if($activeConversation)
    var formData = new FormData();
    formData.append('attachment', input.files[0]);
    fetch('/messenger/{{ $activeConversation->id }}/send', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: formData
    }).then(function (r) { return r.json(); }).then(function (data) {
        if (data.html) {
            var area = document.getElementById('messagesArea');
            if (area) {
                area.insertAdjacentHTML('beforeend', data.html);
                area.scrollTop = area.scrollHeight;
            }
        }
        if (window.AirtrendSounds) AirtrendSounds.send();
    }).catch(function () {});
    @endif
    input.value = '';
}

// Auto-scroll to bottom of messages on load and after rendering.
document.addEventListener('DOMContentLoaded', function() {
    var area = document.getElementById('messagesArea');
    if (area) { area.scrollTop = area.scrollHeight; }
});
</script>
@endsection
