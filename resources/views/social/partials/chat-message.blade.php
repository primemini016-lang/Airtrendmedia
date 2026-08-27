@php
    /** @var \App\Models\ChatMessage $message */
    /** @var \App\Models\User $user */
    $isMe = $message->sender_id === $user->id;
    $sender = $message->sender;
@endphp

<div class="fb-message-row {{ $isMe ? 'me' : 'them' }}" data-message-id="{{ $message->id }}">
    @if(!$isMe && $sender)
        <img src="{{ $sender->avatarUrl() }}" class="fb-avatar fb-avatar-xs" alt="{{ $sender->name }}">
    @endif
    <div class="fb-message-group">
        <div class="fb-message-bubble {{ $isMe ? 'fb-msg-me' : 'fb-msg-them' }}"
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
            @else
                {{ $message->body }}
            @endif
        </div>
        <div class="fb-msg-reactions" id="reactions-{{ $message->id }}">
            @php
                $msgReactions = method_exists($message, 'reactions') ? $message->reactions()->get()->groupBy('emoji') : collect();
            @endphp
            @foreach($msgReactions as $emoji => $group)
                <span class="fb-msg-reaction-chip" title="{{ $group->pluck('user.username')->filter()->join(', ') }}">
                    {{ $emoji }} @if($group->count() > 1)<b>{{ $group->count() }}</b>@endif
                </span>
            @endforeach
        </div>
    </div>
</div>
<div class="fb-message-time {{ $isMe ? 'me' : 'them' }}">{{ $message->created_at->format('M j, g:i A') }}</div>
