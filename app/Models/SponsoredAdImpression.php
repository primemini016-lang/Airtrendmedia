<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SponsoredAdImpression extends Model
{
    protected $fillable = ['sponsored_ad_id', 'user_id', 'ip_address'];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(SponsoredAd::class, 'sponsored_ad_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
