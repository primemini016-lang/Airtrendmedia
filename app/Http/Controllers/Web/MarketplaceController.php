<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use App\Models\TaskCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketplaceController extends Controller
{
    /** Listing types available to sellers. */
    protected const LISTING_TYPES = [
        'service'  => 'Service / Skill',
        'digital'  => 'Digital Product',
        'physical' => 'Physical Item',
        'account'  => 'Account / Profile',
        'other'    => 'Other',
    ];

    /**
     * Browse active marketplace listings.
     */
    public function index(Request $request)
    {
        $query = MarketplaceListing::with('user:id,username,name,image', 'category')
            ->where('status', 'active');

        if ($cat = $request->input('category')) {
            $query->where('category_id', $cat);
        }
        if ($type = $request->input('type')) {
            $query->where('listing_type', $type);
        }
        if ($q = $request->input('q')) {
            $query->where(function ($qq) use ($q) {
                $qq->where('title', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $listings = $query->latest()->paginate(18)->appends($request->query());
        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();

        return view('public.marketplace', compact('listings', 'categories'));
    }

    /**
     * Show a single listing.
     */
    public function show(MarketplaceListing $listing)
    {
        if (!in_array($listing->status, ['active', 'sold'])) {
            abort(404);
        }

        $listing->load('user:id,username,name,image', 'category', 'inquiries');
        $listing->increment('views');

        $reviews  = $listing->approvedReviews()->with('user:id,username,name,image')->paginate(10);
        $comments = $listing->comments()->with('user:id,username,name,image', 'replies.user:id,username,name,image')->get();
        $myReview = null;
        if (auth('web')->check()) {
            $myReview = $listing->reviews()->where('user_id', auth('web')->id())->first();
        }

        $related = MarketplaceListing::with('user:id,username,name,image')
            ->where('status', 'active')
            ->where('id', '!=', $listing->id)
            ->when($listing->category_id, fn ($q) => $q->where('category_id', $listing->category_id))
            ->latest()->limit(4)->get();

        return view('public.marketplace-show', compact('listing', 'related', 'reviews', 'comments', 'myReview'));
    }

    /* =========================================================
     * USER-FACING MARKETPLACE LISTING CREATION SYSTEM
     * ========================================================= */

    /**
     * List the current user's own marketplace listings (all statuses).
     */
    public function myListings()
    {
        $listings = MarketplaceListing::with('category')
            ->where('user_id', auth('web')->id())
            ->latest()
            ->paginate(12);

        return view('user.my-listings', compact('listings'));
    }

    /**
     * Show the marketplace listing creation form.
     */
    public function create()
    {
        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();
        $subcategories = TaskCategory::whereNotNull('parent_id')->where('active', true)->orderBy('name')->get();
        $types = self::LISTING_TYPES;
        $listing = new MarketplaceListing();

        return view('user.create-listing', compact('listing', 'categories', 'subcategories', 'types'));
    }

    /**
     * Store a newly created marketplace listing (pending admin approval).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:191',
            'category_id'  => 'nullable|exists:task_categories,id',
            'description'  => 'required|string|max:10000',
            'price'        => 'required|numeric|min:0.50|max:100000',
            'listing_type' => 'required|string|in:' . implode(',', array_keys(self::LISTING_TYPES)),
            'location'     => 'nullable|string|max:191',
            'image'        => 'nullable|image|max:4096',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('marketplace', 'public');
        }

        MarketplaceListing::create([
            'user_id'      => auth('web')->id(),
            'category_id'  => $validated['category_id'] ?? null,
            'title'        => $validated['title'],
            'description'  => $validated['description'],
            'price'        => $validated['price'],
            'listing_type' => $validated['listing_type'],
            'location'     => $validated['location'] ?? null,
            'image'        => $imagePath,
            'status'       => 'pending', // requires admin approval
        ]);

        return redirect()->route('user.marketplace.index')
            ->with('success', 'Your listing has been submitted for admin approval. It will appear in the marketplace once approved.');
    }

    /**
     * Show the listing edit form (owner only).
     */
    public function edit(MarketplaceListing $listing)
    {
        if ($listing->user_id !== auth('web')->id()) {
            abort(403);
        }

        $categories = TaskCategory::whereNull('parent_id')->where('active', true)->orderBy('position')->get();
        $subcategories = TaskCategory::whereNotNull('parent_id')->where('active', true)->orderBy('name')->get();
        $types = self::LISTING_TYPES;

        return view('user.create-listing', compact('listing', 'categories', 'subcategories', 'types'));
    }

    /**
     * Update the listing (owner only). Reverts to pending if it was rejected.
     */
    public function update(Request $request, MarketplaceListing $listing)
    {
        if ($listing->user_id !== auth('web')->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:191',
            'category_id'  => 'nullable|exists:task_categories,id',
            'description'  => 'required|string|max:10000',
            'price'        => 'required|numeric|min:0.50|max:100000',
            'listing_type' => 'required|string|in:' . implode(',', array_keys(self::LISTING_TYPES)),
            'location'     => 'nullable|string|max:191',
            'image'        => 'nullable|image|max:4096',
        ]);

        $data = [
            'title'        => $validated['title'],
            'category_id'  => $validated['category_id'] ?? null,
            'description'  => $validated['description'],
            'price'        => $validated['price'],
            'listing_type' => $validated['listing_type'],
            'location'     => $validated['location'] ?? null,
        ];

        if ($request->hasFile('image')) {
            if ($listing->image) {
                Storage::disk('public')->delete($listing->image);
            }
            $data['image'] = $request->file('image')->store('marketplace', 'public');
        }

        // If the listing was rejected, resubmit for approval on edit.
        if ($listing->status === 'rejected') {
            $data['status'] = 'pending';
        }

        $listing->update($data);

        return redirect()->route('user.marketplace.index')
            ->with('success', 'Listing updated successfully.' . ($listing->status === 'pending' ? ' It is pending admin approval.' : ''));
    }

    /**
     * Delete the listing (owner only).
     */
    public function destroy(MarketplaceListing $listing)
    {
        if ($listing->user_id !== auth('web')->id()) {
            abort(403);
        }

        if ($listing->image) {
            Storage::disk('public')->delete($listing->image);
        }

        $listing->delete();

        return redirect()->route('user.marketplace.index')
            ->with('success', 'Listing deleted.');
    }
}
