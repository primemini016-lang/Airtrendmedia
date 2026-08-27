<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Story extends Model
{
    protected $fillable = [
        'user_id', 'media_type', 'media_path', 'caption',
        'background_color', 'views_count', 'expires_at', 'is_pinned',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_pinned'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(StoryView::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function mediaUrl(): ?string
    {
        return $this->media_path ? asset('storage/' . $this->media_path) : null;
    }

    public function hasViewedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->views()->where('user_id', $user->id)->exists();
    }
}
