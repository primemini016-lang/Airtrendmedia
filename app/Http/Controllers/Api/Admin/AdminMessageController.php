<?php

namespace App\Http\Controllers\Api\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin messaging: list conversations, view a user's messages, reply to users.
 */
class AdminMessageController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $users = User::whereHas('messages')->select('id', 'username', 'name', 'email', 'image')
            ->withCount(['messages as unread' => fn ($q) => $q->where('from_admin', true)->where('seen', false)])
            ->latest()
            ->paginate(25);

        return $this->ok('Conversations retrieved.', $users);
    }

    public function show(User $user): JsonResponse
    {
        $messages = Message::where('user_id', $user->id)->orderBy('created_at')->get();
        return $this->ok('Messages retrieved.', ['user' => $user, 'messages' => $messages]);
    }

    public function reply(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'user_id'   => $user->id,
            'message'   => $validated['message'],
            'from_admin'=> true,
            'seen'      => false,
        ]);

        return $this->ok('Message sent.', $message, 201);
    }
}
