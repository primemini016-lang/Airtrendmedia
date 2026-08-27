<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceListing extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'price',
        'listing_type', 'image', 'gallery', 'location', 'status', 'views',
    ];

    protected $casts = [
        'price'   => 'decimal:2',
        'gallery' => 'array',
        'views'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function inquiries()
    {
        return $this->hasMany(MarketplaceInquiry::class, 'listing_id');
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('is_approved', true)->latest();
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id')->latest();
    }

    public function getRatingAvgAttribute(): float
    {
        return (float) $this->approvedReviews()->avg('rating') ?: 0;
    }

    public function getRatingCountAttribute(): int
    {
        return (int) $this->approvedReviews()->count();
    }

    public function getReviewCountAttribute(): int
    {
        return $this->rating_count;
    }

    public function getCommentCountAttribute(): int
    {
        return (int) $this->comments()->count();
    }
}
