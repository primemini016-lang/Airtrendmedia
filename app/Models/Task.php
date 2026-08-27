<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'code', 'title', 'price', 'action_url', 'details', 'category_id',
        'user_id', 'date', 'amount', 'time', 'total_price', 'booked',
        'submitted', 'completed', 'status', 'reject_note',
    ];

    protected function casts(): array
    {
        return [
            'price'       => 'decimal:2',
            'total_price' => 'decimal:2',
            'date'        => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function bookings()
    {
        return $this->hasMany(TaskBooking::class);
    }

    public function proofs()
    {
        return $this->hasMany(TaskProof::class);
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

    public function isActive(): bool
    {
        return (int) $this->status === 1;
    }
}
