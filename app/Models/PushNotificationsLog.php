<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationsLog extends Model
{
    protected $table = 'push_notifications_log';

    protected $fillable = [
        'type', 'user_id', 'title', 'body', 'image_url', 'slides', 'url',
        'icon', 'badge', 'data', 'provider', 'recipients',
        'sent_count', 'failed_count', 'status', 'error', 'provider_response',
    ];

    protected $casts = [
        'slides' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
