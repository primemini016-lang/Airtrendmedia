<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SocialPage extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'slug', 'description', 'category',
        'profile_image', 'cover_image', 'website', 'location',
        'phone', 'email', 'verification_status', 'followers_count',
        'likes_count', 'is_monetized', 'is_published',
    ];

    protected $casts = [
        'is_monetized'  => 'boolean',
        'is_published'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (SocialPage $page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->name) . '-' . Str::lower(Str::random(5));
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(SocialPageMember::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class, 'postable_id')->where('postable_type', static::class);
    }

    public function isMemberOf(?User $user): bool
    {
        if (!$user) return false;
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
