<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Models\SocialComment;
use App\Models\SocialLike;
use App\Models\SocialShare;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SocialController extends Controller
{
    /**
     * Main social feed — Facebook-style home feed.
     */
    public function feed(Request $request)
    {
        $user = auth('web')->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Build feed: posts from user + people they follow, plus pages/groups they're in
        $followingIds = $user->following()->pluck('following_id')->toArray();
        $pageIds = $user->pageMemberships()->pluck('social_page_members.page_id')->toArray();
        $groupIds = $user->groupMemberships()->where('status', 'approved')->pluck('social_group_members.group_id')->toArray();

        $postableIds = array_merge([$user->id], $followingIds);
        $postableTypes = [User::class];

        $query = SocialPost::with(['postable', 'comments.user', 'likes'])
            ->where(function ($q) use ($postableIds, $pageIds, $groupIds) {
                $q->where(function ($q2) use ($postableIds) {
                    $q2->where('postable_type', User::class)->whereIn('postable_id', $postableIds);
                });
                if (!empty($pageIds)) {
                    $q->orWhere(function ($q2) use ($pageIds) {
                        $q2->where('postable_type', 'App\\Models\\SocialPage')->whereIn('postable_id', $pageIds);
                    });
                }
                if (!empty($groupIds)) {
                    $q->orWhere(function ($q2) use ($groupIds) {
                        $q2->where('postable_type', 'App\\Models\\SocialGroup')->whereIn('postable_id', $groupIds);
                    });
                }
            })
            ->where(function ($q) use ($user, $followingIds) {
                $q->where('visibility', 'public')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('visibility', 'private')->where('user_id', $user->id);
                  })
                  ->orWhere(function ($q2) use ($user, $followingIds) {
                      $q2->where('visibility', 'friends')->whereIn('user_id', array_merge([$user->id], $followingIds));
                  });
            })
            ->orderBy('is_pinned', 'desc')
            ->latest();

        $posts = $query->paginate(10);

        // Suggestions: users not being followed
        $suggestions = User::where('id', '!=', $user->id)
            ->whereNotIn('id', $followingIds)
            ->where('banned', false)
            ->inRandomOrder()
            ->take(5)
            ->get();

        // Trending posts
        $trending = SocialPost::with('postable')
            ->where('visibility', 'public')
            ->orderBy('likes_count', 'desc')
            ->take(5)
            ->get();

        return view('social.feed', compact('posts', 'suggestions', 'trending'));
    }

    /**
     * Create a new social post.
     */
    public function createPost(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) return redirect()->route('login');

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'media.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif,mp4,mov,webm|max:51200',
            'visibility' => 'nullable|in:public,private,friends',
            'feeling' => 'nullable|in:happy,sad,excited,loved,grateful,blessed,tired,motivated,celebrating',
            'location' => 'nullable|string|max:100',
            'background_color' => 'nullable|string|max:20',
            'postable_type' => 'nullable|string',
            'postable_id' => 'nullable|integer',
        ]);

        $post = new SocialPost();
        $post->user_id = $user->id;
        $post->content = $validated['content'];
        $post->visibility = $validated['visibility'] ?? 'public';
        $post->feeling = $validated['feeling'] ?? null;
        $post->location = $validated['location'] ?? null;
        $post->background_color = $validated['background_color'] ?? null;

        // Determine postable (user by default, or page/group)
        if (!empty($validated['postable_type']) && !empty($validated['postable_id'])) {
            $type = $validated['postable_type'];
            $id = (int) $validated['postable_id'];
            $allowed = false;
            if ($type === User::class) {
                $allowed = $id === $user->id;
            } elseif ($type === 'App\\Models\\SocialPage') {
                $allowed = $user->pageMemberships()->where('social_page_members.page_id', $id)->whereIn('role', ['owner','admin','editor'])->exists();
            } elseif ($type === 'App\\Models\\SocialGroup') {
                $allowed = $user->groupMemberships()->where('social_group_members.group_id', $id)->whereIn('role', ['owner','admin','moderator'])->where('status','approved')->exists();
            }
            if (!$allowed) return back()->with('error', 'You do not have permission to post as this account.');
            $post->postable_type = $type;
            $post->postable_id = $id;
        } else {
            $post->postable_type = User::class;
            $post->postable_id = $user->id;
        }

        // Handle media uploads
        $media = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('social/posts', 'public');
                $media[] = [
                    'type' => str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image',
                    'url' => $path,
                ];
            }
        }
        $post->media = $media;
        $post->save();

        // Increment user post count
        $user->increment('posts_count');

        // Notify followers
        $followers = $user->followers()->get(['follower_id']);
        foreach ($followers as $follower) {
            Notification::create([
                'user_id' => $follower->follower_id,
                'type' => 'social_post',
                'title' => $user->name . ' shared a new post',
                'body' => Str::limit($post->content, 100),
                'url' => route('social.feed'),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'post_id' => $post->id]);
        }

        return back()->with('success', 'Post shared!');
    }

    /**
     * Like / react to a post.
     */
    public function toggleLike(Request $request, SocialPost $post)
    {
        $user = auth('web')->user();
        if (!$user) return response()->json(['error' => 'Please login.'], 401);

        $reaction = $request->get('reaction', 'like');
        $validReactions = ['like', 'love', 'haha', 'wow', 'sad', 'angry'];
        if (!in_array($reaction, $validReactions)) {
            $reaction = 'like';
        }

        $existing = SocialLike::where('likeable_type', SocialPost::class)
            ->where('likeable_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->reaction === $reaction) {
                $existing->delete();
                $post->decrement('likes_count');
                $reacted = false;
            } else {
                $existing->update(['reaction' => $reaction]);
                $reacted = true;
            }
        } else {
            SocialLike::create([
                'likeable_type' => SocialPost::class,
                'likeable_id' => $post->id,
                'user_id' => $user->id,
                'reaction' => $reaction,
            ]);
            $post->increment('likes_count');
            $reacted = true;

            // Notify post owner
            $owner = $post->postable;
            if ($owner instanceof User && $owner->id !== $user->id) {
                Notification::create([
                    'user_id' => $owner->id,
                    'type' => 'social_like',
                    'title' => $user->name . ' reacted to your post',
                    'body' => Str::limit($post->content, 80),
                    'url' => route('social.feed'),
                ]);
            }
        }

        return response()->json([
            'reacted' => $reacted,
            'count' => $post->fresh()->likes_count,
        ]);
    }

    /**
     * Comment on a post.
     */
    public function comment(Request $request, SocialPost $post)
    {
        $user = auth('web')->user();
        if (!$user) return response()->json(['error' => 'Please login.'], 401);

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:social_comments,id',
            'media' => 'nullable|image|max:5120',
        ]);

        $comment = new SocialComment();
        $comment->post_id = $post->id;
        $comment->user_id = $user->id;
        $comment->parent_id = $validated['parent_id'] ?? null;
        $comment->body = $validated['body'];

        if ($request->hasFile('media')) {
            $comment->media = $request->file('media')->store('social/comments', 'public');
        }

        $comment->save();
        $post->increment('comments_count');

        // Notify post owner
        $owner = $post->postable;
        if ($owner instanceof User && $owner->id !== $user->id) {
            Notification::create([
                'user_id' => $owner->id,
                'type' => 'social_comment',
                'title' => $user->name . ' commented on your post',
                'body' => Str::limit($validated['body'], 80),
                'url' => route('social.feed'),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'user_name' => $user->name,
                    'user_avatar' => $user->avatarUrl(),
                    'time' => 'Just now',
                ],
            ]);
        }

        return back()->with('success', 'Comment posted!');
    }

    /**
     * Like a comment.
     */
    public function toggleCommentLike(Request $request, SocialComment $comment)
    {
        $user = auth('web')->user();
        if (!$user) return response()->json(['error' => 'Please login.'], 401);

        $existing = SocialLike::where('likeable_type', SocialComment::class)
            ->where('likeable_id', $comment->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $comment->decrement('likes_count');
            $liked = false;
        } else {
            SocialLike::create([
                'likeable_type' => SocialComment::class,
                'likeable_id' => $comment->id,
                'user_id' => $user->id,
                'reaction' => 'like',
            ]);
            $comment->increment('likes_count');
            $liked = true;
        }

        return response()->json([
            'liked' => $liked,
            'count' => $comment->fresh()->likes_count,
        ]);
    }

    /**
     * Share a post.
     */
    public function share(Request $request, SocialPost $post)
    {
        $user = auth('web')->user();
        if (!$user) return response()->json(['error' => 'Please login.'], 401);

        $validated = $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        // Create a new post that shares the original
        $newPost = SocialPost::create([
            'user_id' => $user->id,
            'content' => $validated['comment'] ?? '',
            'postable_type' => User::class,
            'postable_id' => $user->id,
            'visibility' => 'public',
        ]);

        // Track the share
        SocialShare::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'comment' => $validated['comment'] ?? null,
        ]);

        $post->increment('shares_count');
        $user->increment('posts_count');

        return response()->json(['success' => true, 'message' => 'Post shared!']);
    }

    /**
     * Delete a post (owner only).
     */
    public function deletePost(SocialPost $post)
    {
        $user = auth('web')->user();
        if (!$user) return response()->json(['error' => 'Please login.'], 401);

        if ($post->postable_type === User::class && $post->postable_id === $user->id) {
            // Delete media files
            if ($post->media) {
                foreach ($post->media as $m) {
                    if (isset($m['url'])) Storage::disk('public')->delete($m['url']);
                }
            }
            $post->delete();
            $user->decrement('posts_count');
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    /**
     * Follow / unfollow a user.
     */
    public function toggleFollow(Request $request, User $target)
    {
        $user = auth('web')->user();
        if (!$user) return response()->json(['error' => 'Please login.'], 401);

        if ($user->id === $target->id) {
            return response()->json(['error' => 'Cannot follow yourself.'], 400);
        }

        $existing = Follow::where('follower_id', $user->id)->where('following_id', $target->id)->first();
        if ($existing) {
            $existing->delete();
            $user->decrement('following_count');
            $target->decrement('followers_count');
            $following = false;
        } else {
            Follow::create([
                'follower_id' => $user->id,
                'following_id' => $target->id,
            ]);
            $user->increment('following_count');
            $target->increment('followers_count');
            $following = true;

            // Notify target
            Notification::create([
                'user_id' => $target->id,
                'type' => 'social_follow',
                'title' => $user->name . ' started following you',
                'body' => 'You have a new follower!',
                'url' => route('social.profile', $user->username),
            ]);
        }

        return response()->json([
            'following' => $following,
            'followers_count' => $target->fresh()->followers_count,
        ]);
    }

    /**
     * Explore / discover page — find people and content.
     */
    public function explore(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) return redirect()->route('login');

        $query = $request->get('q', '');
        $users = collect();
        $posts = collect();

        if ($query) {
            $users = User::where('name', 'LIKE', "%{$query}%")
                ->orWhere('username', 'LIKE', "%{$query}%")
                ->where('banned', false)
                ->take(20)
                ->get();

            $posts = SocialPost::where('content', 'LIKE', "%{$query}%")
                ->where('visibility', 'public')
                ->with('postable')
                ->latest()
                ->take(20)
                ->get();
        } else {
            $users = User::where('id', '!=', $user->id)
                ->where('banned', false)
                ->inRandomOrder()
                ->take(12)
                ->get();
        }

        return view('social.explore', compact('users', 'posts', 'query'));
    }

    /**
     * Get comments for a post (AJAX).
     */
    public function getComments(SocialPost $post)
    {
        $comments = $post->comments()->whereNull('parent_id')->with(['user', 'replies.user'])->latest()->get();

        return response()->json([
            'comments' => $comments->map(function ($c) {
                return [
                    'id' => $c->id,
                    'body' => $c->body,
                    'user_name' => $c->user?->name,
                    'user_avatar' => $c->user?->avatarUrl(),
                    'user_url' => $c->user ? route('social.profile', $c->user->username) : '#',
                    'time' => $c->created_at->diffForHumans(),
                    'likes_count' => $c->likes_count,
                    'replies' => $c->replies->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'body' => $r->body,
                            'user_name' => $r->user?->name,
                            'user_avatar' => $r->user?->avatarUrl(),
                            'time' => $r->created_at->diffForHumans(),
                        ];
                    }),
                ];
            }),
        ]);
    }
}
