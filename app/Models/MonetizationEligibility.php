<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonetizationEligibility extends Model
{
    protected $fillable = [
        'user_id', 'is_eligible', 'content_monetization', 'fan_subscriptions',
        'stars_enabled', 'followers_count', 'content_count', 'engagement_score',
        'country', 'rejection_reason', 'approved_at',
    ];

    protected $casts = [
        'is_eligible'          => 'boolean',
        'content_monetization' => 'boolean',
        'fan_subscriptions'    => 'boolean',
        'stars_enabled'        => 'boolean',
        'approved_at'          => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Facebook-style eligibility check:
     * - At least 500 followers (configurable)
     * - At least 10 original content posts
     * - Active for at least 30 days
     * - Engagement score >= 50
     */
    public static function checkEligibility(User $user): array
    {
        $followerCount  = $user->followers_count;
        $contentCount   = $user->socialPosts()->count();
        $accountAgeDays = $user->created_at->diffInDays(now());
        $engagementScore = self::calculateEngagementScore($user);

        $requirements = [
            'followers' => ['required' => 500, 'actual' => $followerCount, 'label' => '500+ Followers'],
            'content'   => ['required' => 10, 'actual' => $contentCount, 'label' => '10+ Original Posts'],
            'age'       => ['required' => 30, 'actual' => $accountAgeDays, 'label' => '30+ Days Active'],
            'engagement'=> ['required' => 50, 'actual' => $engagementScore, 'label' => 'Engagement Score 50+'],
        ];

        $eligible = true;
        foreach ($requirements as $req) {
            if ($req['actual'] < $req['required']) {
                $eligible = false;
                break;
            }
        }

        return [
            'eligible'      => $eligible,
            'requirements'  => $requirements,
            'engagement'    => $engagementScore,
        ];
    }

    public static function calculateEngagementScore(User $user): int
    {
        $posts = $user->socialPosts()->take(50)->get();
        if ($posts->isEmpty()) return 0;

        $totalEngagement = $posts->sum(function ($post) {
            return $post->likes_count + $post->comments_count + $post->shares_count;
        });

        return min(100, (int) ($totalEngagement / max(1, $posts->count())));
    }
}
