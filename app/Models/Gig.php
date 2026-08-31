<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gig extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'price',
        'image', 'gallery', 'social_platform', 'social_url',
        'status', 'views', 'sales',
    ];

    protected $casts = [
        'price'   => 'decimal:2',
        'gallery' => 'array',
        'views'   => 'integer',
        'sales'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function orders()
    {
        return $this->hasMany(GigOrder::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function approvedReviews()
    {
        return $this->reviews()->where('is_approved', true)->latest();
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
}
