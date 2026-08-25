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
        'image', 'bio', 'balance', 'total_earned', 'referrer_id',
        'referral_code', 'is_verified', 'is_active', 'banned',
        'email_verified_at', 'activated_at',
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
            'is_verified'       => 'boolean',
            'is_active'         => 'boolean',
            'banned'            => 'boolean',
            'balance'           => 'decimal:2',
            'total_earned'      => 'decimal:2',
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

    /* ---------- Helpers ---------- */
    public function canPerformTasks(): bool
    {
        return $this->is_active && ! $this->banned;
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
