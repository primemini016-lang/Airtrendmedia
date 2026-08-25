<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MonetizationEarning extends Model
{
    protected $fillable = [
        'user_id', 'source_type', 'source_type_id', 'source_type_type',
        'amount', 'currency', 'views_count', 'stars_count', 'metadata',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public static function totalEarnings(User $user): float
    {
        return (float) self::where('user_id', $user->id)->sum('amount');
    }

    public static function monthlyEarnings(User $user): float
    {
        return (float) self::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
    }
}
