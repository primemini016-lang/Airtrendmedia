<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Gig;
use App\Models\MarketplaceListing;
use App\Models\Review;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Unified review / rating controller.
 * Handles 1-5 star ratings + written reviews for gigs, marketplace listings,
 * tasks, and user profiles. One rating per (user, target); re-submitting updates.
 */
class ReviewController extends Controller
{
    /** Map the "type" input to a model class. */
    private const TYPES = [
        'gig'        => Gig::class,
        'listing'    => MarketplaceListing::class,
        'task'       => Task::class,
        'profile'    => User::class,
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'         => ['required', 'in:gig,listing,task,profile'],
            'id'           => ['required', 'integer'],
            'rating'       => ['required', 'integer', 'min:1', 'max:5'],
            'body'         => ['nullable', 'string', 'max:2000'],
        ]);

        $modelClass = self::TYPES[$validated['type']] ?? null;
        if (! $modelClass) {
            return back()->with('error', 'Invalid review target.');
        }

        $target = $modelClass::find($validated['id']);
        if (! $target) {
            return back()->with('error', 'Target not found.');
        }

        $userId = Auth::guard('web')->id();

        // Prevent reviewing your own profile / own gig / own listing / own task.
        $ownerCol = ($validated['type'] === 'profile') ? 'id' : 'user_id';
        if ((int) ($target->{$ownerCol}) === (int) $userId) {
            return back()->with('error', 'You cannot review your own item.');
        }

        // Upsert: one review per (user, target).
        $review = Review::updateOrCreate(
            [
                'user_id'           => $userId,
                'reviewable_type'   => $modelClass,
                'reviewable_id'     => $target->id,
            ],
            [
                'rating'     => $validated['rating'],
                'body'       => $validated['body'] ?? null,
                'is_approved' => true,
            ]
        );

        // Recompute cached columns for user profiles.
        if ($validated['type'] === 'profile') {
            Review::recomputeUser($target->id);
        }

        return back()->with('success', 'Thank you! Your review has been posted.');
    }

    /**
     * Delete a review (only the author or admin).
     */
    public function destroy(Review $review)
    {
        $userId = Auth::guard('web')->id();
        $isAdmin = Auth::guard('admin')->check();

        if (! $isAdmin && (int) $review->user_id !== (int) $userId) {
            abort(403);
        }

        $isProfile = $review->reviewable_type === User::class;
        $profileId = $isProfile ? $review->reviewable_id : null;

        $review->delete();

        if ($isProfile) {
            Review::recomputeUser($profileId);
        }

        return back()->with('success', 'Review removed.');
    }
}
