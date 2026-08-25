<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\MonetizationEligibility;
use App\Models\ContentStar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * View a user's social profile (Facebook-style).
     */
    public function show(Request $request, $username)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $profile = User::where('username', $username)->firstOrFail();
        $profile->load(['country']);

        $tab = $request->input('tab', 'posts');

        // Get posts for this profile (only public, or all if viewing own profile)
        $postsQuery = SocialPost::with(['user:id,username,name,image', 'comments.user', 'likes'])
            ->where('postable_type', User::class)
            ->where('postable_id', $profile->id)
            ->orderBy('is_pinned', 'desc')
            ->latest();

        if ($profile->id !== $user->id) {
            $postsQuery->where('visibility', 'public');
        }

        $posts = $postsQuery->paginate(10);

        // Followers / Following
        $followers = $profile->followers()->with('follower:id,username,name,image')->paginate(20, pageName: 'followers_page');
        $following = $profile->following()->with('following:id,username,name,image')->paginate(20, pageName: 'following_page');

        // Pages owned by user
        $pages = $profile->socialPages()->limit(6)->get();
        $groups = $profile->socialGroups()->limit(6)->get();

        $isFollowing = $user->isFollowing($profile);
        $isOwnProfile = $profile->id === $user->id;

        // Monetization info
        $monetization = MonetizationEligibility::where('user_id', $profile->id)->first();
        $totalStars = ContentStar::totalStars($profile);

        // Friends count (mutual follows = friends on Facebook)
        $friendsCount = Follow::where('follower_id', $profile->id)
            ->whereHas('following.followers', function ($q) use ($profile) {
                $q->where('follower_id', $profile->id);
            })
            ->count();

        return view('social.profile', compact(
            'profile', 'posts', 'followers', 'following', 'pages', 'groups',
            'isFollowing', 'isOwnProfile', 'monetization', 'totalStars',
            'friendsCount', 'tab', 'user'
        ));
    }

    /**
     * Edit own social profile.
     */
    public function edit()
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        $user->load(['country']);
        return view('social.profile-edit', compact('user'));
    }

    /**
     * Update own social profile (cover, bio, social links, privacy).
     */
    public function update(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name'            => 'required|string|max:120',
            'bio'             => 'nullable|string|max:5000',
            'cover_image'     => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'account_privacy' => 'nullable|in:public,private',
            'monetization_enabled' => 'nullable|boolean',
        ]);

        $user->name = $validated['name'];
        $user->account_privacy = $validated['account_privacy'] ?? 'public';
        $user->monetization_enabled = !empty($validated['monetization_enabled']);

        // Bio stored as a column or metadata — we use a profile_bio field
        if ($request->filled('bio')) {
            $user->bio = $validated['bio'];
        }

        if ($request->hasFile('cover_image')) {
            if ($user->cover_image) {
                Storage::disk('public')->delete($user->cover_image);
            }
            $user->cover_image = $request->file('cover_image')->store('covers', 'public');
        }

        $user->save();

        return redirect()->route('social.profile', $user->username)
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload/update avatar image from the social profile.
     */
    public function updateAvatar(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($user->image) {
            Storage::disk('public')->delete($user->image);
        }

        $user->image = $request->file('image')->store('avatars', 'public');
        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Avatar updated.',
                'image_url' => $user->fresh()->avatarUrl(),
            ]);
        }

        return back()->with('success', 'Avatar updated successfully.');
    }

    /**
     * Send a star (monetary tip) to a creator.
     */
    public function sendStar(Request $request, User $creator)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($user->id === $creator->id) {
            return response()->json(['error' => 'You cannot send stars to yourself.'], 422);
        }

        $validated = $request->validate([
            'stars_count' => 'required|integer|min:1|max:1000',
            'message'     => 'nullable|string|max:500',
            'starable_type' => 'nullable|string',
            'starable_id'   => 'nullable|integer',
        ]);

        // Each star = $0.01 (configurable)
        $monetaryValue = $validated['stars_count'] * 0.01;

        // Check user has enough balance
        if ($user->balance < $monetaryValue) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Insufficient balance. Please deposit funds first.'], 422);
            }
            return back()->with('error', 'Insufficient balance. Please deposit funds to send stars.');
        }

        // Deduct from sender
        $user->decrement('balance', $monetaryValue);
        $user->save();

        // Create the star record
        $star = ContentStar::create([
            'sender_id'      => $user->id,
            'receiver_id'    => $creator->id,
            'starable_type'  => $validated['starable_type'] ?? User::class,
            'starable_id'    => $validated['starable_id'] ?? $creator->id,
            'stars_count'    => $validated['stars_count'],
            'message'        => $validated['message'] ?? null,
            'monetary_value' => $monetaryValue,
        ]);

        // Credit the creator (if monetization is enabled)
        if ($creator->monetization_enabled) {
            $creator->increment('balance', $monetaryValue);
            $creator->save();

            // Record earnings
            \App\Models\MonetizationEarning::create([
                'user_id'           => $creator->id,
                'source_type'       => 'stars',
                'source_type_id'    => $star->id,
                'source_type_type'  => ContentStar::class,
                'amount'            => $monetaryValue,
                'currency'          => 'USD',
                'stars_count'       => $validated['stars_count'],
            ]);
        }

        // Notify creator
        Notification::create([
            'user_id' => $creator->id,
            'type'    => 'star_received',
            'title'   => 'You received stars!',
            'body'    => "{$user->name} sent you {$validated['stars_count']} star" . ($validated['stars_count'] > 1 ? 's' : '') . '.',
            'url'     => route('social.profile', $creator->username),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "You sent {$validated['stars_count']} star" . ($validated['stars_count'] > 1 ? 's' : '') . " to {$creator->name}!",
                'stars_count' => $validated['stars_count'],
            ]);
        }

        return back()->with('success', "You sent {$validated['stars_count']} star" . ($validated['stars_count'] > 1 ? 's' : '') . " to {$creator->name}!");
    }

    /**
     * Show the "people you may know" / friends suggestions.
     */
    public function suggestions()
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $followingIds = $user->following()->pluck('users.id')->toArray();
        $followingIds[] = $user->id;

        $suggestions = User::whereNotIn('id', $followingIds)
            ->where('status', 1)
            ->inRandomOrder()
            ->paginate(24);

        return view('social.suggestions', compact('suggestions', 'user'));
    }

    /**
     * Show friends (mutual followers) list.
     */
    public function friends(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Mutual follows = friends
        $friendIds = Follow::where('follower_id', $user->id)
            ->whereHas('following.followers', function ($q) use ($user) {
                $q->where('follower_id', $user->id);
            })
            ->pluck('following_id');

        $friends = User::whereIn('id', $friendIds)
            ->with('country')
            ->paginate(24);

        return view('social.friends', compact('friends', 'user'));
    }
}
