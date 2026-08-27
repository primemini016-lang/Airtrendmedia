<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SocialPage;
use App\Models\SocialPageMember;
use App\Models\SocialPost;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SocialPageController extends Controller
{
    /**
     * Browse all published pages.
     */
    public function index(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = SocialPage::where('is_published', true)
            ->with('owner:id,username,name,image');

        if ($q = $request->input('q')) {
            $query->where(function ($sq) use ($q) {
                $sq->where('name', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
                   ->orWhere('category', 'like', "%{$q}%");
            });
        }

        if ($cat = $request->input('category')) {
            $query->where('category', $cat);
        }

        $pages = $query->latest()->paginate(12)->appends($request->query());
        $categories = SocialPage::where('is_published', true)
            ->whereNotNull('category')->distinct()->pluck('category');

        $myPages = $user->socialPages()->latest()->limit(5)->get();

        return view('social.pages', compact('pages', 'categories', 'myPages', 'user'));
    }

    /**
     * Show the create-page form.
     */
    public function create()
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }
        return view('social.page-create', compact('user'));
    }

    /**
     * Store a new page.
     */
    public function store(Request $request)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:5000',
            'category'    => 'nullable|string|max:80',
            'website'     => 'nullable|url|max:191',
            'location'    => 'nullable|string|max:120',
            'phone'       => 'nullable|string|max:40',
            'email'       => 'nullable|email|max:191',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $page = new SocialPage();
        $page->owner_id = $user->id;
        $page->name = $validated['name'];
        $page->description = $validated['description'] ?? null;
        $page->category = $validated['category'] ?? null;
        $page->website = $validated['website'] ?? null;
        $page->location = $validated['location'] ?? null;
        $page->phone = $validated['phone'] ?? null;
        $page->email = $validated['email'] ?? null;
        $page->is_published = true;
        $page->followers_count = 0;
        $page->likes_count = 0;
        $page->verification_status = 'pending';

        if ($request->hasFile('profile_image')) {
            $page->profile_image = $request->file('profile_image')->store('pages', 'public');
        }
        if ($request->hasFile('cover_image')) {
            $page->cover_image = $request->file('cover_image')->store('pages/covers', 'public');
        }

        $page->save();

        // Owner becomes an admin member of the page
        SocialPageMember::create([
            'page_id' => $page->id,
            'user_id' => $user->id,
            'role'    => 'admin',
        ]);

        return redirect()->route('social.page.show', $page->slug)
            ->with('success', 'Page created successfully! Start posting to grow your audience.');
    }

    /**
     * Show a single page with its feed.
     */
    public function show(Request $request, SocialPage $page)
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (!$page->is_published && $page->owner_id !== $user->id) {
            abort(404);
        }

        $posts = SocialPost::with(['user:id,username,name,image', 'comments.user', 'likes'])
            ->where('postable_type', SocialPage::class)
            ->where('postable_id', $page->id)
            ->where('visibility', 'public')
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(10);

        $isMember = $page->isMemberOf($user);
        $memberRole = $isMember ? $page->members()->where('user_id', $user->id)->value('role') : null;
        $members = $page->members()->with('user:id,username,name,image')->latest()->limit(12)->get();
        $page->load('owner:id,username,name,image');

        return view('social.page-show', compact('page', 'posts', 'isMember', 'memberRole', 'members', 'user'));
    }

    /**
     * Join (like/follow) a page.
     */
    public function join(Request $request, SocialPage $page)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($page->isMemberOf($user)) {
            return response()->json(['message' => 'Already a member']);
        }

        SocialPageMember::create([
            'page_id' => $page->id,
            'user_id' => $user->id,
            'role'    => 'member',
        ]);

        $page->increment('followers_count');
        $page->increment('likes_count');

        // Notify page owner
        if ($page->owner_id !== $user->id) {
            Notification::create([
                'user_id' => $page->owner_id,
                'type'    => 'page_join',
                'title'   => 'New page follower',
                'body'    => "{$user->name} started following your page \"{$page->name}\".",
                'url'     => route('social.page.show', $page->slug),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You are now following this page.',
                'followers_count' => $page->fresh()->followers_count,
            ]);
        }

        return back()->with('success', 'You are now following this page.');
    }

    /**
     * Leave (unfollow) a page.
     */
    public function leave(Request $request, SocialPage $page)
    {
        $user = auth('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $membership = $page->members()->where('user_id', $user->id)->first();

        if (!$membership) {
            return response()->json(['message' => 'Not a member']);
        }

        // Don't allow the owner to leave their own page
        if ($membership->role === 'admin' && $page->owner_id === $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Page owner cannot leave.'], 403);
            }
            return back()->with('error', 'As the page owner, you cannot unfollow your own page.');
        }

        $membership->delete();
        $page->decrement('followers_count');
        $page->decrement('likes_count');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'You unfollowed this page.',
                'followers_count' => $page->fresh()->followers_count,
            ]);
        }

        return back()->with('success', 'You unfollowed this page.');
    }

    /**
     * Edit page (owner only).
     */
    public function edit(SocialPage $page)
    {
        $user = auth('web')->user();
        if (!$user || $page->owner_id !== $user->id) {
            abort(403);
        }
        return view('social.page-edit', compact('page', 'user'));
    }

    /**
     * Update page (owner only).
     */
    public function update(Request $request, SocialPage $page)
    {
        $user = auth('web')->user();
        if (!$user || $page->owner_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name'        => 'required|string|max:120',
            'description' => 'nullable|string|max:5000',
            'category'    => 'nullable|string|max:80',
            'website'     => 'nullable|url|max:191',
            'location'    => 'nullable|string|max:120',
            'phone'       => 'nullable|string|max:40',
            'email'       => 'nullable|email|max:191',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'cover_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $page->fill($validated);

        if ($request->hasFile('profile_image')) {
            if ($page->profile_image) {
                Storage::disk('public')->delete($page->profile_image);
            }
            $page->profile_image = $request->file('profile_image')->store('pages', 'public');
        }
        if ($request->hasFile('cover_image')) {
            if ($page->cover_image) {
                Storage::disk('public')->delete($page->cover_image);
            }
            $page->cover_image = $request->file('cover_image')->store('pages/covers', 'public');
        }

        $page->save();

        return redirect()->route('social.page.show', $page->slug)
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Delete page (owner only).
     */
    public function destroy(SocialPage $page)
    {
        $user = auth('web')->user();
        if (!$user || $page->owner_id !== $user->id) {
            abort(403);
        }

        // Delete associated posts' media
        foreach ($page->posts as $post) {
            if ($post->media) {
                foreach ($post->media as $media) {
                    if (isset($media['path'])) {
                        Storage::disk('public')->delete($media['path']);
                    }
                }
            }
        }

        if ($page->profile_image) {
            Storage::disk('public')->delete($page->profile_image);
        }
        if ($page->cover_image) {
            Storage::disk('public')->delete($page->cover_image);
        }

        $page->delete();

        return redirect()->route('social.pages')
            ->with('success', 'Page deleted successfully.');
    }

    /**
     * Show pages owned by the current user (my pages dashboard).
     */
    public function myPages()
    {
        $user = auth('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $ownedPages = $user->socialPages()->latest()->paginate(12);
        $joinedPages = $user->pageMemberships()
            ->with('page.owner:id,username,name,image')
            ->where('role', 'member')
            ->latest()
            ->paginate(12);

        return view('social.my-pages', compact('ownedPages', 'joinedPages', 'user'));
    }
}
