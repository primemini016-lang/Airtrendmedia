<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceListing;
use App\Models\TaskCategory;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
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

        $related = MarketplaceListing::with('user:id,username,name,image')
            ->where('status', 'active')
            ->where('id', '!=', $listing->id)
            ->when($listing->category_id, fn ($q) => $q->where('category_id', $listing->category_id))
            ->latest()->limit(4)->get();

        return view('public.marketplace-show', compact('listing', 'related'));
    }
}
