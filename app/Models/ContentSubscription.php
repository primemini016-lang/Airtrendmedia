<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentSubscription extends Model
{
    protected $fillable = [
        'subscriber_id', 'creator_id', 'monthly_amount',
        'status', 'started_at', 'ends_at',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'started_at'     => 'datetime',
        'ends_at'        => 'datetime',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subscriber_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
