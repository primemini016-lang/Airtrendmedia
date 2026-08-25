<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentStar extends Model
{
    protected $fillable = [
        'sender_id', 'receiver_id', 'starable_type', 'starable_id',
        'stars_count', 'message', 'monetary_value',
    ];

    protected $casts = [
        'stars_count'     => 'integer',
        'monetary_value'  => 'decimal:4',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function starable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function totalStars(User $user): int
    {
        return (int) self::where('receiver_id', $user->id)->sum('stars_count');
    }
}
