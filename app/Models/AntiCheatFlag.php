<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntiCheatFlag extends Model
{
    protected $fillable = [
        'user_id', 'type', 'description', 'evidence',
        'severity', 'status', 'ip_address',
        'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'evidence'    => 'array',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'resolved_by');
    }
}
