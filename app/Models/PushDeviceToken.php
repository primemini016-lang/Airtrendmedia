<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushDeviceToken extends Model
{
    protected $fillable = [
        'user_id', 'token', 'device_id', 'platform', 'provider', 'is_active', 'last_used_at',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'last_used_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
