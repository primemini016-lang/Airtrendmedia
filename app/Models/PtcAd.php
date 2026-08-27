<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtcAd extends Model
{
    protected $fillable = [
        'user_id', 'title', 'url', 'description', 'image',
        'duration_seconds', 'reward_per_view', 'cost_per_view',
        'budget', 'views_count', 'max_views',
        'status', 'mode', 'admin_note',
        'approved_at', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'reward_per_view'  => 'decimal:4',
        'cost_per_view'    => 'decimal:4',
        'budget'           => 'integer',
        'views_count'      => 'integer',
        'max_views'        => 'integer',
        'approved_at'      => 'datetime',
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function views()
    {
        return $this->hasMany(PtcView::class, 'ptc_ad_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isLive(): bool
    {
        if (! $this->isApproved()) {
            return false;
        }

        if ($this->max_views > 0 && $this->views_count >= $this->max_views) {
            return false;
        }

        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function imageUrl(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->title) . '&size=400&background=2563eb&color=fff';
    }
}
