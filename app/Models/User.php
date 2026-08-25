<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * Auto-generate a unique referral code on creation if none was set,
     * and (defensively) ensure a fresh code if a rare collision happens.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });
    }

    public static function generateUniqueReferralCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    protected $fillable = [
        'username', 'name', 'email', 'phone', 'country_code', 'password',
        'image', 'cover_image', 'bio', 'balance', 'total_earned', 'referrer_id',
        'referral_code', 'is_verified', 'is_active', 'banned',
        'email_verified_at', 'activated_at',
        'trial_started_at', 'trial_ends_at',
        'followers_count', 'following_count', 'posts_count',
        'account_privacy', 'monetization_enabled',
        // Airtrendmedia extended features
        'account_type', 'kyc_status', 'kyc_approved_at',
        'verification_status', 'verification_valid_until',
        'registration_ip', 'monetization_disabled_reason', 'monetization_disabled_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'          => 'hashed',
            'email_verified_at' => 'datetime',
            'activated_at'      => 'datetime',
            'trial_started_at'  => 'datetime',
            'trial_ends_at'     => 'datetime',
            'is_verified'       => 'boolean',
            'is_active'         => 'boolean',
            'banned'            => 'boolean',
            'balance'           => 'decimal:2',
            'total_earned'      => 'decimal:2',
            'monetization_enabled' => 'boolean',
            'kyc_approved_at'      => 'datetime',
            'verification_valid_until' => 'datetime',
            'monetization_disabled_at' => 'datetime',
        ];
    }

    /* ---------- JWT ---------- */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['guard' => 'user'];
    }

    /* ---------- Relationships ---------- */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    /* ---------- Airtrendmedia Extended Features ---------- */

    public function kycSubmission()
    {
        return $this->hasOne(KycSubmission::class)->latestOfMany();
    }

    public function verificationBadge()
    {
        return $this->hasOne(VerificationBadge::class)->latestOfMany();
    }

    public function stories()
    {
        return $this->hasMany(Story::class);
    }

    public function sponsoredAds()
    {
        return $this->hasMany(SponsoredAd::class, 'advertiser_id');
    }

    public function pushTokens()
    {
        return $this->hasMany(PushDeviceToken::class);
    }

    public function isFreelancer(): bool
    {
        return in_array($this->account_type, ['freelancer', 'both']);
    }

    public function isAdvertiser(): bool
    {
        return in_array($this->account_type, ['advertiser', 'both']);
    }

    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'approved';
    }

    public function isBlueVerified(): bool
    {
        if ($this->verification_status !== 'verified') {
            return false;
        }
        if ($this->verification_valid_until && $this->verification_valid_until->isPast()) {
            return false;
        }
        return true;
    }

    public function canWithdraw(): bool
    {
        return $this->isKycVerified();
    }

    public function affiliateReferrals()
    {
        return $this->hasMany(AffiliateReferral::class, 'referrer_id');
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function bookings()
    {
        return $this->hasMany(TaskBooking::class);
    }

    public function proofs()
    {
        return $this->hasMany(TaskProof::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function reports()
    {
        return $this->hasMany(Complaint::class, 'user2_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function gigs()
    {
        return $this->hasMany(Gig::class);
    }

    public function gigOrders()
    {
        return $this->hasMany(GigOrder::class, 'buyer_id');
    }

    public function marketplaceListings()
    {
        return $this->hasMany(MarketplaceListing::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /* ---------- Blog Relationships ---------- */
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function blogComments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function blogLikes()
    {
        return $this->hasMany(BlogLike::class);
    }

    public function blogRatings()
    {
        return $this->hasMany(BlogRate::class);
    }

    /* ---------- Social Relationships ---------- */
    public function socialPosts()
    {
        return $this->hasMany(SocialPost::class);
    }

    public function socialComments()
    {
        return $this->hasMany(SocialComment::class);
    }

    public function socialLikes()
    {
        return $this->hasMany(SocialLike::class);
    }

    // Followers = people following me
    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    // Following = people I follow
    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    public function isFollowing(User $other): bool
    {
        return $this->following()->where('following_id', $other->id)->exists();
    }

    public function isFollowedBy(User $other): bool
    {
        return $this->followers()->where('follower_id', $other->id)->exists();
    }

    public function socialPages()
    {
        return $this->hasMany(SocialPage::class, 'owner_id');
    }

    public function pageMemberships()
    {
        return $this->hasMany(SocialPageMember::class);
    }

    public function socialGroups()
    {
        return $this->hasMany(SocialGroup::class, 'owner_id');
    }

    public function groupMemberships()
    {
        return $this->hasMany(SocialGroupMember::class);
    }

    /* ---------- Chat Relationships ---------- */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['role', 'last_read_at', 'is_muted'])
            ->withTimestamps()
            ->orderBy('last_message_at', 'desc');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    /* ---------- Monetization Relationships ---------- */
    public function monetizationEligibility()
    {
        return $this->hasOne(MonetizationEligibility::class);
    }

    public function monetizationEarnings()
    {
        return $this->hasMany(MonetizationEarning::class);
    }

    public function contentSubscriptions()
    {
        return $this->hasMany(ContentSubscription::class, 'subscriber_id');
    }

    public function subscribers()
    {
        return $this->hasMany(ContentSubscription::class, 'creator_id')->where('status', 'active');
    }

    public function starsReceived()
    {
        return $this->hasMany(ContentStar::class, 'receiver_id');
    }

    public function starsSent()
    {
        return $this->hasMany(ContentStar::class, 'sender_id');
    }

    /* ---------- Helpers ---------- */
    public function canPerformTasks(): bool
    {
        return $this->is_active && ! $this->banned;
    }

    /**
     * Check if the user is on an active free trial.
     */
    public function isOnTrial(): bool
    {
        if ($this->is_active) return false;
        if (!$this->trial_ends_at) return false;
        return now()->lt($this->trial_ends_at);
    }

    /**
     * Check if the user has trial access (active trial or paid).
     */
    public function hasAccess(): bool
    {
        if ($this->is_active) return true;
        if ($this->isOnTrial()) return true;
        return false;
    }

    /**
     * Days remaining in trial (0 if expired or not on trial).
     */
    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at) return 0;
        return max(0, now()->diffInDays($this->trial_ends_at));
    }

    /**
     * Start a 3-day free trial.
     */
    public function startTrial(): void
    {
        $this->update([
            'trial_started_at' => now(),
            'trial_ends_at'    => now()->addDays(3),
        ]);
    }

    /**
     * Full profile URL for the social system.
     */
    public function profileUrl(): string
    {
        return route('social.profile', $this->username);
    }

    /**
     * Avatar URL (with fallback).
     */
    public function avatarUrl(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=2563eb&background=eef2ff&size=128';
    }

    public function creditedReferralsCount(): int
    {
        return $this->affiliateReferrals()->where('status', 'paid')->count();
    }

    public function affiliateEarnings(): string
    {
        return $this->affiliateReferrals()->where('status', 'paid')->sum('reward_amount');
    }
}
