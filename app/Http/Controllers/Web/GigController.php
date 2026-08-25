<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use App\Models\TaskCategory;
use Illuminate\Http\Request;

class GigController extends Controller
{
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

        $related = Gig::with('user:id,username,name,image')
            ->where('status', 'active')
            ->where('id', '!=', $gig->id)
            ->when($gig->category_id, fn ($q) => $q->where('category_id', $gig->category_id))
            ->latest()->limit(4)->get();

        return view('public.gigs-show', compact('gig', 'related'));
    }
}
