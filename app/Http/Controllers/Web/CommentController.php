<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\MarketplaceListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Polymorphic comment controller — currently used by marketplace listings.
 */
class CommentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'       => ['required', 'in:listing'],
            'id'         => ['required', 'integer'],
            'parent_id'  => ['nullable', 'integer', 'exists:comments,id'],
            'body'       => ['required', 'string', 'max:2000'],
        ]);

        // Resolve the target.
        $modelClass = $validated['type'] === 'listing' ? MarketplaceListing::class : null;
        if (! $modelClass) {
            return back()->with('error', 'Invalid comment target.');
        }
        $target = $modelClass::find($validated['id']);
        if (! $target) {
            return back()->with('error', 'Target not found.');
        }

        Comment::create([
            'user_id'           => Auth::guard('web')->id(),
            'commentable_type'  => $modelClass,
            'commentable_id'    => $target->id,
            'parent_id'         => $validated['parent_id'] ?? null,
            'body'              => $validated['body'],
            'is_approved'       => true,
        ]);

        return back()->with('success', 'Comment posted.');
    }

    public function destroy(Comment $comment)
    {
        $userId  = Auth::guard('web')->id();
        $isAdmin = Auth::guard('admin')->check();

        if (! $isAdmin && (int) $comment->user_id !== (int) $userId) {
            abort(403);
        }

        $comment->delete();
        return back()->with('success', 'Comment removed.');
    }
}
