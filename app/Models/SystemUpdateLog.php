<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemUpdateLog extends Model
{
    protected $fillable = [
        'from_commit', 'to_commit', 'from_version', 'to_version',
        'output', 'status', 'initiated_by',
    ];

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'initiated_by');
    }
}
