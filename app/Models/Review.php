<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'reviewable_type',
        'reviewable_id',
        'rating',
        'body',
        'is_approved',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_approved' => 'boolean',
    ];

    /** A positive review = 4 or 5 stars. */
    public const POSITIVE_THRESHOLD = 4;

    /** Minimum positive reviews to mark a user profile "recommendable". */
    public const RECOMMENDABLE_MIN = 10;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPositive(): bool
    {
        return $this->rating >= self::POSITIVE_THRESHOLD;
    }

    /* -----------------------------------------------------------------
     |  Helper: recompute cached rating columns for a User profile.
     |----------------------------------------------------------------- */
    public static function recomputeUser(?int $userId): void
    {
        if (! $userId) {
            return;
        }
        $reviews = self::where('reviewable_type', User::class)
            ->where('reviewable_id', $userId)
            ->where('is_approved', true)
            ->get();

        $count   = $reviews->count();
        $avg     = $count ? round($reviews->avg('rating'), 2) : 0;
        $positive = $reviews->filter(fn ($r) => $r->rating >= self::POSITIVE_THRESHOLD)->count();

        User::where('id', $userId)->update([
            'rating_avg'             => $avg,
            'rating_count'           => $count,
            'positive_review_count'  => $positive,
            'is_recommendable'       => $positive >= self::RECOMMENDABLE_MIN ? 1 : 0,
        ]);
    }
}
