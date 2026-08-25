<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SocialGroup extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'slug', 'description', 'category',
        'profile_image', 'cover_image', 'privacy', 'requires_approval',
        'members_count', 'posts_count', 'is_monetized',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'is_monetized'      => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (SocialGroup $group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name) . '-' . Str::lower(Str::random(5));
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(SocialGroupMember::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(SocialPost::class, 'postable_id')->where('postable_type', static::class);
    }

    public function isMemberOf(?User $user): bool
    {
        if (!$user) return false;
        return $this->members()->where('user_id', $user->id)->where('status', 'approved')->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
