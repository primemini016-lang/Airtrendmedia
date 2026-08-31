<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\MediaUploadService;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\ChatMessage;
use App\Models\ChatReaction;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Chat inbox — list all conversations for the user.
     */
    public function index(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $conversations = Conversation::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with(['participants.user:id,username,name,image', 'lastMessage.sender:id,username,name,image'])
        ->latest('last_message_at')
        ->paginate(20);

        // Find the active conversation if specified
        $activeConversation = null;
        $messages = collect();
        $otherUser = null;
        if ($request->has('c')) {
            $activeConversation = Conversation::whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->find($request->input('c'));

            if ($activeConversation) {
                $messages = $activeConversation->messages()
                    ->with('sender:id,username,name,image')
                    ->orderBy('created_at', 'asc')
                    ->limit(100)
                    ->get();

                // Mark as read
                $participant = $activeConversation->participants()->where('user_id', $user->id)->first();
                if ($participant) {
                    $participant->update(['last_read_at' => now()]);
                }

                // For 1-on-1 conversations, find the "other" user
                if (!$activeConversation->is_group) {
                    $otherUser = $activeConversation->participants()
                        ->where('user_id', '!=', $user->id)
                        ->with('user:id,username,name,image')
                        ->first()?->user;
                }
            }
        }

        $suggestedUsers = User::where('id', '!=', $user->id)
            ->where('banned', false)
            ->whereDoesntHave('conversations', function ($q) use ($user) {
                $q->whereHas('participants', function ($qp) use ($user) {
                    $qp->where('user_id', $user->id);
                });
            })
            ->inRandomOrder()
            ->limit(8)
            ->get(['id', 'username', 'name', 'image', 'last_seen_at', 'online_at']);

        return view('messenger.chat', compact('conversations', 'activeConversation', 'messages', 'suggestedUsers', 'user', 'otherUser'));
    }

    /**
     * Start or open a conversation with a specific user.
     */
    public function startConversation(Request $request, User $targetUser)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->id === $targetUser->id) {
            return back()->with('error', 'You cannot start a conversation with yourself.');
        }

        // Look for an existing 1-on-1 conversation between these two users
        $conversation = Conversation::where('is_group', false)
            ->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('participants', function ($q) use ($targetUser) {
                $q->where('user_id', $targetUser->id);
            })
            ->first();

        if (!$conversation) {
            // Create a new conversation
            $conversation = Conversation::create([
                'is_group'       => false,
                'created_by'     => $user->id,
                'last_message_at'=> now(),
            ]);

            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $user->id,
                'role'            => 'member',
            ]);
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $targetUser->id,
                'role'            => 'member',
            ]);
        }

        return redirect()->route('messenger.chat', ['c' => $conversation->id]);
    }

    /**
     * Send a message in a conversation.
     */
    public function send(Request $request, Conversation $conversation)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify the user is a participant
        $participant = $conversation->participants()->where('user_id', $user->id)->first();
        if (!$participant) {
            abort(403, 'You are not part of this conversation.');
        }

        $validated = $request->validate([
            'body'         => 'nullable|string|max:5000',
            'attachment'   => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf,doc,docx,zip,mp4,mp3,wav|max:10240',
            'message_type' => 'nullable|in:text,image,file,audio,video',
            'reply_to_id'  => 'nullable|exists:chat_messages,id',
        ]);

        if (empty($validated['body']) && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message cannot be empty.'], 422);
        }

        $attachments = [];
        $messageType = $validated['message_type'] ?? 'text';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = (string) $file->getMimeType();
            $path = str_starts_with($mime, 'image/')
                ? app(MediaUploadService::class)->storeImage($file, 'chat-attachments', 1600, 84)
                : $file->store('chat-attachments', 'public');
            $attachments[] = [
                'path'      => $path,
                'name'      => $file->getClientOriginalName(),
                'mime'      => $mime,
                'size'      => $file->getSize(),
            ];

            if (str_starts_with($mime, 'image/')) {
                $messageType = 'image';
            } elseif (str_starts_with($mime, 'audio/')) {
                $messageType = 'audio';
            } elseif (str_starts_with($mime, 'video/')) {
                $messageType = 'video';
            } else {
                $messageType = 'file';
            }
        }

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $validated['body'] ?? null,
            'attachments'     => $attachments,
            'message_type'    => $messageType,
            'reply_to_id'     => $validated['reply_to_id'] ?? null,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Update sender's last_read_at
        $participant->update(['last_read_at' => now()]);

        // Notify other participants
        $otherParticipants = $conversation->participants()
            ->where('user_id', '!=', $user->id)
            ->get();

        foreach ($otherParticipants as $other) {
            // Only notify if they haven't read (i.e., their last_read_at is before this message)
            if (!$other->last_read_at || $other->last_read_at < $message->created_at) {
                Notification::create([
                    'user_id' => $other->user_id,
                    'type'    => 'chat_message',
                    'title'   => 'New message from ' . $user->name,
                    'body'    => $validated['body'] ? Str::limit($validated['body'], 100) : 'Sent an attachment',
                    'url'     => route('messenger.chat', ['c' => $conversation->id]),
                ]);
            }
        }

        $message->load('sender:id,username,name,image');

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Message sent.',
                'data'     => $message,
                'html'     => view('messenger.partials.chat-message', ['message' => $message, 'user' => $user])->render(),
            ]);
        }

        return back()->with('success', 'Message sent.');
    }

    /**
     * Fetch new messages (for real-time polling).
     */
    public function fetchMessages(Request $request, Conversation $conversation)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $participant = $conversation->participants()->where('user_id', $user->id)->first();
        if (!$participant) {
            abort(403);
        }

        $afterId = (int) $request->input('after', 0);

        $messages = $conversation->messages()
            ->with('sender:id,username,name,image')
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark as read
        $participant->update(['last_read_at' => now()]);

        $html = '';
        foreach ($messages as $msg) {
            $html .= view('messenger.partials.chat-message', ['message' => $msg, 'user' => $user])->render();
        }

        return response()->json([
            'messages'    => $messages,
            'html'        => $html,
            'last_id'     => $messages->last()?->id,
        ]);
    }

    /**
     * Get unread message count (for notification badge).
     */
    public function unreadCount()
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $count = ChatMessage::whereHas('conversation.participants', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where(function ($qp) {
                  $qp->whereNull('last_read_at')
                     ->orWhereColumn('last_read_at', '<', 'chat_messages.created_at');
              });
        })
        ->where('sender_id', '!=', $user->id)
        ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Create a group conversation.
     */
    public function createGroup(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name'       => 'required|string|max:120',
            'members'    => 'required|array|min:1',
            'members.*'  => 'exists:users,id',
            'group_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $conversation = Conversation::create([
            'name'           => $validated['name'],
            'is_group'       => true,
            'created_by'     => $user->id,
            'last_message_at'=> now(),
        ]);

        if ($request->hasFile('group_avatar')) {
            $conversation->group_avatar = $request->file('group_avatar')->store('chat-groups', 'public');
            $conversation->save();
        }

        // Add creator as admin
        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $user->id,
            'role'            => 'admin',
        ]);

        // Add selected members
        foreach ($validated['members'] as $memberId) {
            if ($memberId != $user->id) {
                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id'         => $memberId,
                    'role'            => 'member',
                ]);
            }
        }

        return redirect()->route('messenger.chat', ['c' => $conversation->id])
            ->with('success', 'Group conversation created.');
    }

    /**
     * Delete a message (sender only).
     */
    public function deleteMessage(Request $request, ChatMessage $message)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($message->sender_id !== $user->id) {
            abort(403, 'You can only delete your own messages.');
        }

        if ($message->attachments) {
            foreach ($message->attachments as $attachment) {
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }
            }
        }

        $message->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Message deleted.']);
        }

        return back()->with('success', 'Message deleted.');
    }

    /**
     * Search users for starting a new chat.
     */
    public function searchUsers(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $q = $request->input('q', '');
        if (strlen($q) < 2) {
            return response()->json(['users' => []]);
        }

        $users = User::where('id', '!=', $user->id)
            ->where('banned', false)
            ->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('username', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'username', 'name', 'image', 'last_seen_at', 'online_at']);

        // Append a ready-to-use avatar URL and verification flag so the
        // front-end search results can render the profile picture and the
        // blue verification badge without extra lookups.
        $users->each(function (User $u) {
            $u->avatar_url = $u->avatarUrl();
            $u->is_verified = $u->isBlueVerified();
        });

        return response()->json(['users' => $users]);
    }

    /**
     * Typing indicator — broadcast typing status to conversation participants.
     * Stored briefly in cache so frontend can poll.
     */
    public function typing(Request $request, Conversation $conversation)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify participant
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        if (!$isParticipant) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'is_typing' => ['required', 'boolean'],
        ]);

        $cacheKey = "chat:typing:{$conversation->id}:{$user->id}";
        if ($validated['is_typing']) {
            \Cache::put($cacheKey, true, now()->addSeconds(5));
        } else {
            \Cache::forget($cacheKey);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Poll who is currently typing in a conversation.
     */
    public function typingStatus(Conversation $conversation)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $participantIds = $conversation->participants()->where('user_id', '!=', $user->id)->pluck('user_id');

        $typingUsers = [];
        foreach ($participantIds as $pid) {
            if (\Cache::has("chat:typing:{$conversation->id}:{$pid}")) {
                $u = User::find($pid, ['id', 'username', 'name']);
                if ($u) {
                    $typingUsers[] = ['id' => $u->id, 'name' => $u->name, 'username' => $u->username];
                }
            }
        }

        return response()->json(['typing' => $typingUsers]);
    }

    /**
     * React to a chat message with an emoji (unlimited emoji reactions).
     * Toggles the reaction off if the same user already reacted with the same emoji.
     */
    public function react(Request $request, ChatMessage $message)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'emoji' => ['required', 'string', 'max:10'],
        ]);

        $existing = ChatReaction::where('chat_message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('emoji', $validated['emoji'])
            ->first();

        if ($existing) {
            $existing->delete();
            $message->decrement('reactions_count');
            $action = 'removed';
        } else {
            ChatReaction::create([
                'chat_message_id' => $message->id,
                'user_id'         => $user->id,
                'emoji'           => $validated['emoji'],
            ]);
            $message->increment('reactions_count');
            $action = 'added';
        }

        $reactions = ChatReaction::where('chat_message_id', $message->id)
            ->with('user:id,username,name,image')
            ->get()
            ->groupBy('emoji')
            ->map(fn ($group) => [
                'emoji'  => $group->first()->emoji,
                'count'  => $group->count(),
                'users'  => $group->map(fn ($r) => $r->user?->username)->filter(),
            ])
            ->values();

        return response()->json([
            'success'   => true,
            'action'    => $action,
            'reactions' => $reactions,
        ]);
    }

    /**
     * Get all reactions for a message.
     */
    public function messageReactions(ChatMessage $message)
    {
        $reactions = ChatReaction::where('chat_message_id', $message->id)
            ->with('user:id,username,name,image')
            ->get()
            ->groupBy('emoji')
            ->map(fn ($group) => [
                'emoji'  => $group->first()->emoji,
                'count'  => $group->count(),
                'users'  => $group->map(fn ($r) => [
                    'username' => $r->user?->username,
                    'name'     => $r->user?->name,
                ]),
            ])
            ->values();

        return response()->json(['reactions' => $reactions]);
    }
}
