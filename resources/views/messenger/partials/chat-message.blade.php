@php
    /** @var \App\Models\ChatMessage $message */
    /** @var \App\Models\User $user */
    $isMe = $message->sender_id === $user->id;
    $sender = $message->sender;
    $msgReactions = method_exists($message, 'reactions') ? $message->reactions()->get()->groupBy('emoji') : collect();
    $emojis = ['👍','❤️','😂','😮','😢','🔥','🎉','🙏','👏','😍','💯','✅','🤔','😎','🥳'];
@endphp

<div class="fb-message-row {{ $isMe ? 'me' : 'them' }}" data-message-id="{{ $message->id }}">
    @if(!$isMe && $sender)
        <img src="{{ $sender->avatarUrl() }}" class="fb-avatar fb-avatar-xs" alt="{{ $sender->name }}">
    @endif
    <div class="fb-message-group">
        @if($message->reply_to_id && $message->replyTo)
            @php $replied = $message->replyTo; @endphp
            <div class="text-xs fb-text-muted italic border-l-2 pl-2 mb-1 opacity-70">
                ↳ Reply to {{ $replied->sender?->name ?? 'someone' }}: {{ Str::limit($replied->body ?? '[attachment]', 60) }}
            </div>
        @endif
        <div class="fb-message-bubble {{ $isMe ? 'fb-msg-me' : 'fb-msg-them' }}"
             x-data="{ open: false }"
             @contextmenu.prevent="open = !open"
             @dblclick="open = !open"
             style="position: relative;">
            @if($message->message_type === 'image' && !empty($message->attachments))
                @php $att = is_array($message->attachments) ? $message->attachments[0] : null; @endphp
                @if($att && isset($att['path']))
                    <img src="{{ asset('storage/' . $att['path']) }}" class="fb-msg-image" alt="{{ $att['name'] ?? '' }}">
                @endif
            @elseif($message->message_type === 'file' && !empty($message->attachments))
                @php $att = is_array($message->attachments) ? $message->attachments[0] : null; @endphp
                @if($att && isset($att['path']))
                    <a href="{{ asset('storage/' . $att['path']) }}" target="_blank" class="fb-msg-file">
                        <x-icon name="paperclip" class="w-4 h-4 inline" /> {{ $att['name'] ?? 'Attachment' }}
                    </a>
                @endif
            @elseif($message->message_type === 'audio' && !empty($message->attachments))
                @php $att = is_array($message->attachments) ? $message->attachments[0] : null; @endphp
                @if($att && isset($att['path']))
                    <audio controls class="max-w-[240px]"><source src="{{ asset('storage/' . $att['path']) }}" type="{{ $att['mime'] ?? 'audio/mpeg' }}"></audio>
                @endif
            @elseif($message->message_type === 'video' && !empty($message->attachments))
                @php $att = is_array($message->attachments) ? $message->attachments[0] : null; @endphp
                @if($att && isset($att['path']))
                    <video controls class="max-w-[240px] rounded-lg"><source src="{{ asset('storage/' . $att['path']) }}" type="{{ $att['mime'] ?? 'video/mp4' }}"></video>
                @endif
            @else
                {{ $message->body }}
            @endif
            {{-- Emoji reaction picker --}}
            <div class="fb-emoji-picker" x-show="open" x-cloak x-transition>
                @foreach($emojis as $emo)
                    <button type="button" class="fb-emoji-btn"
                        x-on:click="$dispatch('react-{{ $message->id }}', '{{ $emo }}'); open = false;"
                        data-sound="like">{{ $emo }}</button>
                @endforeach
            </div>
            {{-- Quick react button --}}
            <button type="button" class="fb-msg-react-btn" x-on:click="open = !open" title="React">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
            </button>
        </div>
        <div class="fb-msg-reactions" id="reactions-{{ $message->id }}"
             x-data
             @react-{{ $message->id }}.window="reactToMessage({{ $message->id }}, $event.detail)">
            @foreach($msgReactions as $emoji => $group)
                <span class="fb-msg-reaction-chip" title="{{ $group->pluck('user.username')->filter()->join(', ') }}">
                    {{ $emoji }} @if($group->count() > 1)<b>{{ $group->count() }}</b>@endif
                </span>
            @endforeach
        </div>
    </div>
</div>
<div class="fb-message-time {{ $isMe ? 'me' : 'them' }}">{{ $message->created_at->format('M j, g:i A') }}</div>
