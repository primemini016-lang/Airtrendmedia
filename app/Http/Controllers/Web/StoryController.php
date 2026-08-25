<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    /**
     * Get active stories for the feed stories bar (from followed users + own).
     * Returns JSON for AJAX loading.
     */
    public function active()
    {
        $user = auth('web')->user();

        $followedIds = $user->following()->pluck('users.id')->push($user->id)->unique();

        $stories = Story::with('user:id,username,name,image')
            ->whereIn('user_id', $followedIds)
            ->where('expires_at', '>', now())
            ->latest()
            ->get()
            ->groupBy('user_id');

        $grouped = $grouped = $stories->map(function ($userStories, $userId) use ($user) {
            $first = $userStories->first();
            return [
                'user_id'   => $userId,
                'username'  => $first->user->username,
                'name'      => $first->user->name,
                'avatar'    => $first->user->image ? asset('storage/' . $first->user->image) : null,
                'is_own'    => $userId == $user->id,
                'count'     => $userStories->count(),
                'stories'   => $userStories->map(fn ($s) => [
                    'id'         => $s->id,
                    'media_type' => $s->media_type,
                    'media_url'  => $s->mediaUrl(),
                    'caption'    => $s->caption,
                    'bg'         => $s->background_color,
                    'views'      => $s->views_count,
                    'seen'       => $s->hasViewedBy($user),
                ]),
            ];
        })->values();

        return response()->json(['stories' => $grouped]);
    }

    /**
     * Create a new story (image/video/text). Auto-expires in 24h.
     */
    public function store(Request $request)
    {
        $user = auth('web')->user();

        $validated = $request->validate([
            'media_type'       => ['required', 'in:image,video,text'],
            'media'            => ['nullable', 'file', 'max:25600'], // 25MB
            'caption'          => ['nullable', 'string', 'max:500'],
            'background_color' => ['nullable', 'string', 'max:30'],
        ]);

        $data = [
            'user_id'          => $user->id,
            'media_type'       => $validated['media_type'],
            'caption'          => $validated['caption'] ?? null,
            'background_color' => $validated['background_color'] ?? 'gradient-blue',
            'expires_at'       => now()->addHours(24),
            'views_count'      => 0,
        ];

        if ($request->hasFile('media')) {
            $data['media_path'] = $request->file('media')->store('stories', 'public');
        }

        $story = Story::create($data);

        return response()->json([
            'success' => true,
            'story'   => [
                'id'         => $story->id,
                'media_type' => $story->media_type,
                'media_url'  => $story->mediaUrl(),
                'caption'    => $story->caption,
            ],
        ]);
    }

    /**
     * Record a view on a story (anti-cheat: one view per user per story).
     */
    public function view(Story $story)
    {
        $user = auth('web')->user();

        if ($story->isExpired()) {
            return response()->json(['error' => 'Story expired'], 410);
        }

        // Prevent duplicate views (anti-cheat)
        if (!$story->hasViewedBy($user)) {
            StoryView::create([
                'story_id' => $story->id,
                'user_id'  => $user->id,
            ]);
            $story->increment('views_count');
        }

        return response()->json(['success' => true, 'views' => $story->fresh()->views_count]);
    }

    /**
     * List viewers of a story (owner only).
     */
    public function viewers(Story $story)
    {
        $user = auth('web')->user();
        if ($story->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $viewers = $story->views()
            ->with('user:id,username,name,image')
            ->latest()
            ->get()
            ->map(fn ($v) => [
                'username' => $v->user->username,
                'name'     => $v->user->name,
                'avatar'   => $v->user->image ? asset('storage/' . $v->user->image) : null,
                'viewed_at'=> $v->created_at->diffForHumans(),
            ]);

        return response()->json(['viewers' => $viewers]);
    }

    /**
     * Delete own story.
     */
    public function destroy(Story $story)
    {
        $user = auth('web')->user();
        if ($story->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $story->delete();
        return response()->json(['success' => true]);
    }
}
