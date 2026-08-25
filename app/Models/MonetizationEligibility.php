<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class MonetizationEligibility extends Model
{
    protected $fillable = [
        'user_id', 'is_eligible', 'content_monetization', 'fan_subscriptions',
        'stars_enabled', 'followers_count', 'paid_followers', 'eligible_views',
        'real_engagement', 'content_count', 'engagement_score',
        'country', 'rejection_reason', 'approved_at', 'auto_enabled_at',
    ];

    protected $casts = [
        'is_eligible'          => 'boolean',
        'content_monetization' => 'boolean',
        'fan_subscriptions'    => 'boolean',
        'stars_enabled'        => 'boolean',
        'approved_at'          => 'datetime',
        'auto_enabled_at'      => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Airtrendmedia eligibility check:
     * - 500 paid ($5 activation) followers
     * - 1000 eligible views
     * - 1000 real engagement
     * When all met → auto-enable monetization.
     */
    public static function checkEligibility(User $user): array
    {
        $followerCount  = (int) $user->followers_count;
        $contentCount   = $user->socialPosts()->count();
        $accountAgeDays = $user->created_at->diffInDays(now());
        $engagementScore = self::calculateEngagementScore($user);

        // Airtrendmedia-specific: eligible views (sum of views_count on posts)
        $eligibleViews = (int) $user->socialPosts()->sum('views_count');

        // Real engagement = total likes + comments + shares across all posts
        $realEngagement = (int) $user->socialPosts()->sum(DB::raw('likes_count + comments_count + shares_count'));

        // Paid followers = followers who have activated ($5 activation fee paid)
        $paidFollowers = self::countPaidFollowers($user);

        $requirements = [
            'paid_followers' => ['required' => 500, 'actual' => $paidFollowers, 'label' => '500+ Paid ($5) Followers'],
            'eligible_views' => ['required' => 1000, 'actual' => $eligibleViews, 'label' => '1000+ Eligible Views'],
            'real_engagement'=> ['required' => 1000, 'actual' => $realEngagement, 'label' => '1000+ Real Engagement'],
            'followers'      => ['required' => 500, 'actual' => $followerCount, 'label' => '500+ Total Followers'],
            'content'        => ['required' => 10, 'actual' => $contentCount, 'label' => '10+ Original Posts'],
            'engagement'     => ['required' => 50, 'actual' => $engagementScore, 'label' => 'Engagement Score 50+'],
        ];

        $eligible = true;
        foreach ($requirements as $req) {
            if ($req['actual'] < $req['required']) {
                $eligible = false;
                break;
            }
        }

        return [
            'eligible'        => $eligible,
            'requirements'    => $requirements,
            'engagement'      => $engagementScore,
            'paid_followers'  => $paidFollowers,
            'eligible_views'  => $eligibleViews,
            'real_engagement' => $realEngagement,
        ];
    }

    /**
     * Count followers who have paid the $5 activation fee (activated_at not null).
     */
    public static function countPaidFollowers(User $user): int
    {
        return Follow::where('following_id', $user->id)
            ->whereHas('follower', function ($q) {
                $q->whereNotNull('activated_at');
            })
            ->count();
    }

    /**
     * Auto-enable monetization when all requirements are met.
     * Returns true if monetization was just enabled.
     */
    public static function autoEnableIfEligible(User $user): bool
    {
        // Already enabled or admin-disabled → don't auto-enable
        if ($user->monetization_enabled) {
            return false;
        }
        if ($user->monetization_disabled_reason) {
            return false; // admin disabled, requires admin re-enable
        }

        $check = self::checkEligibility($user);
        if (!$check['eligible']) {
            return false;
        }

        $eligibility = self::firstOrCreate(
            ['user_id' => $user->id],
            ['is_eligible' => true]
        );

        $eligibility->update([
            'is_eligible'          => true,
            'content_monetization' => true,
            'fan_subscriptions'    => true,
            'stars_enabled'        => true,
            'paid_followers'       => $check['paid_followers'],
            'eligible_views'       => $check['eligible_views'],
            'real_engagement'      => $check['real_engagement'],
            'followers_count'      => $user->followers_count,
            'content_count'        => $user->socialPosts()->count(),
            'engagement_score'     => $check['engagement'],
            'auto_enabled_at'      => now(),
        ]);

        $user->update(['monetization_enabled' => true]);

        return true;
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
