<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GigController extends Controller
{
    /** Social platforms selectable when creating a gig. */
    protected const PLATFORMS = [
        'facebook', 'twitter', 'instagram', 'youtube', 'tiktok', 'linkedin',
        'telegram', 'whatsapp', 'messenger', 'x', 'threads', 'pinterest',
        'reddit', 'snapchat', 'discord', 'twitch', 'youtube-shorts', 'vimeo',
        'dailymotion', 'soundcloud', 'spotify', 'medium', 'vk', 'weibo',
        'quora', 'tumblr', 'patreon', 'kick', 'rumble', 'clubhouse', 'signal',
        'viber', 'line', 'skype', 'truth-social', 'mastodon', 'github',
        'dribbble', 'behance', 'flickr', 'we-chat', 'likee', 'sharechat',
        'kuaishou', 'onlyfans', 'trovo', 'xing', 'meetup', 'goodreads',
        'untappd', 'substack', 'other',
    ];

    /**
     * Browse active gigs.
     */
    public function index(Request $request)
    {
        $query = Gig::with('user:id,username,name,image', 'category')
            ->where('status', 'active');

        if ($cat = $request->input('category')) {
            $query->where('category_id', $cat);
        }
        if ($platform = $request->input('platform')) {
            $query->where('social_platform', $platform);
        }
        if ($q = $request->input('q')) {
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }
        if ($max = $request->input('max_price')) {
            $query->where('price', '<=', $max);
        }

        $gigs = $query->latest()->paginate(18)->appends($request->query());
        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();

        $platforms = ['facebook', 'twitter', 'instagram', 'youtube', 'tiktok', 'linkedin', 'telegram', 'whatsapp', 'other'];

        return view('public.gigs', compact('gigs', 'categories', 'platforms'));
    }

    /**
     * Show a single gig.
     */
    public function show(Gig $gig)
    {
        if (!in_array($gig->status, ['active', 'paused'])) {
            abort(404);
        }

        $gig->load('user:id,username,name,image', 'category', 'orders');
        $gig->increment('views');

        $reviews = $gig->approvedReviews()->with('user:id,username,name,image')->paginate(10);
        $myReview = null;
        if (auth('web')->check()) {
            $myReview = $gig->reviews()->where('user_id', auth('web')->id())->first();
        }

        $related = Gig::with('user:id,username,name,image')
            ->where('status', 'active')
            ->where('id', '!=', $gig->id)
            ->when($gig->category_id, fn ($q) => $q->where('category_id', $gig->category_id))
            ->latest()->limit(4)->get();

        return view('public.gigs-show', compact('gig', 'related', 'reviews', 'myReview'));
    }

    /* =========================================================
     * USER-FACING GIG CREATION SYSTEM
     * ========================================================= */

    /**
     * List the current user's own gigs (all statuses).
     */
    public function myGigs()
    {
        $gigs = Gig::with('category')
            ->where('user_id', auth('web')->id())
            ->latest()
            ->paginate(12);

        return view('user.my-gigs', compact('gigs'));
    }

    /**
     * Show the gig creation form.
     */
    public function create()
    {
        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();
        $subcategories = TaskCategory::whereNotNull('parent_id')->where('active', true)->orderBy('name')->get();
        $platforms = self::PLATFORMS;
        $gig = new Gig();

        return view('user.create-gig', compact('gig', 'categories', 'subcategories', 'platforms'));
    }

    /**
     * Store a newly created gig (pending admin approval).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:191',
            'category_id'     => 'nullable|exists:task_categories,id',
            'description'     => 'required|string|max:10000',
            'price'           => 'required|numeric|min:0.50|max:10000',
            'social_platform' => 'nullable|string|max:50',
            'social_url'      => 'nullable|url|max:500',
            'image'           => 'nullable|image|max:4096',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('gigs', 'public');
        }

        Gig::create([
            'user_id'         => auth('web')->id(),
            'category_id'     => $validated['category_id'] ?? null,
            'title'           => $validated['title'],
            'description'     => $validated['description'],
            'price'           => $validated['price'],
            'social_platform' => $validated['social_platform'] ?? null,
            'social_url'      => $validated['social_url'] ?? null,
            'image'           => $imagePath,
            'status'          => 'pending', // requires admin approval
        ]);

        return redirect()->route('user.gigs.index')
            ->with('success', 'Your gig has been submitted for admin approval. It will go live once approved.');
    }

    /**
     * Show the gig edit form (owner only).
     */
    public function edit(Gig $gig)
    {
        if ($gig->user_id !== auth('web')->id()) {
            abort(403);
        }

        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();
        $subcategories = TaskCategory::whereNotNull('parent_id')->where('active', true)->orderBy('name')->get();
        $platforms = self::PLATFORMS;

        return view('user.create-gig', compact('gig', 'categories', 'subcategories', 'platforms'));
    }

    /**
     * Update the gig (owner only). Reverts to pending if it was rejected.
     */
    public function update(Request $request, Gig $gig)
    {
        if ($gig->user_id !== auth('web')->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title'           => 'required|string|max:191',
            'category_id'     => 'nullable|exists:task_categories,id',
            'description'     => 'required|string|max:10000',
            'price'           => 'required|numeric|min:0.50|max:10000',
            'social_platform' => 'nullable|string|max:50',
            'social_url'      => 'nullable|url|max:500',
            'image'           => 'nullable|image|max:4096',
        ]);

        $data = [
            'title'           => $validated['title'],
            'category_id'     => $validated['category_id'] ?? null,
            'description'     => $validated['description'],
            'price'           => $validated['price'],
            'social_platform' => $validated['social_platform'] ?? null,
            'social_url'      => $validated['social_url'] ?? null,
        ];

        if ($request->hasFile('image')) {
            if ($gig->image) {
                Storage::disk('public')->delete($gig->image);
            }
            $data['image'] = $request->file('image')->store('gigs', 'public');
        }

        // If the gig was rejected, resubmit for approval on edit.
        if ($gig->status === 'rejected') {
            $data['status'] = 'pending';
        }

        $gig->update($data);

        return redirect()->route('user.gigs.index')
            ->with('success', 'Gig updated successfully.' . ($gig->status === 'pending' ? ' It is pending admin approval.' : ''));
    }

    /**
     * Delete the gig (owner only).
     */
    public function destroy(Gig $gig)
    {
        if ($gig->user_id !== auth('web')->id()) {
            abort(403);
        }

        if ($gig->image) {
            Storage::disk('public')->delete($gig->image);
        }

        $gig->delete();

        return redirect()->route('user.gigs.index')
            ->with('success', 'Gig deleted.');
    }
}
