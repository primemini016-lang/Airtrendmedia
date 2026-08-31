<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SponsoredAd extends Model
{
    protected $fillable = [
        'advertiser_id', 'title', 'description', 'link_url', 'ad_type',
        'media_path', 'cta_button', 'cost_per_click', 'budget',
        'amount_spent', 'impressions', 'clicks', 'views', 'status',
        'rejection_reason', 'starts_at', 'ends_at',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'cost_per_click' => 'decimal:4',
        'budget'         => 'decimal:2',
        'amount_spent'   => 'decimal:2',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'reviewed_at'    => 'datetime',
    ];

    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(SponsoredAdClick::class, 'sponsored_ad_id');
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(SponsoredAdImpression::class, 'sponsored_ad_id');
    }

    public function mediaUrl(): ?string
    {
        return $this->media_path ? storage_asset($this->media_path) : null;
    }

    public function remainingBudget(): float
    {
        return max(0, (float)$this->budget - (float)$this->amount_spent);
    }

    public function isLive(): bool
    {
        return $this->status === 'approved'
            && $this->remainingBudget() > 0
            && (!$this->ends_at || $this->ends_at->isFuture());
    }

    public function ctr(): float
    {
        if ($this->impressions == 0) return 0;
        return round(($this->clicks / $this->impressions) * 100, 2);
    }
}
