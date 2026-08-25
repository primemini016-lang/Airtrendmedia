<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialComment extends Model
{
    protected $fillable = [
        'post_id', 'user_id', 'parent_id', 'body', 'media',
        'likes_count', 'replies_count',
    ];

    protected $casts = ['media' => 'array'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(SocialPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(SocialComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SocialComment::class, 'parent_id')->latest();
    }
}
