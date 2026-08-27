<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialPost extends Model
{
    protected $fillable = [
        'user_id', 'postable_type', 'postable_id', 'content', 'media',
        'link_url', 'link_title', 'link_description', 'link_image',
        'visibility', 'feeling', 'location', 'background_color',
        'likes_count', 'comments_count', 'shares_count', 'views_count',
        'is_monetized', 'is_pinned',
    ];

    protected $casts = [
        'media'         => 'array',
        'is_monetized'  => 'boolean',
        'is_pinned'     => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function postable(): MorphTo
    {
        return $this->morphTo();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SocialComment::class, 'post_id')->whereNull('parent_id')->latest();
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(SocialComment::class, 'post_id')->latest();
    }

    public function likes(): HasMany
    {
        return $this->hasMany(SocialLike::class, 'likeable_id')->where('likeable_type', static::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(SocialShare::class, 'post_id');
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return SocialLike::where('user_id', $user->id)
            ->where('likeable_type', static::class)
            ->where('likeable_id', $this->id)
            ->exists();
    }

    public function getReactionBy(?User $user): ?string
    {
        if (!$user) return null;
        $like = SocialLike::where('user_id', $user->id)
            ->where('likeable_type', static::class)
            ->where('likeable_id', $this->id)
            ->first();
        return $like?->reaction;
    }
}
