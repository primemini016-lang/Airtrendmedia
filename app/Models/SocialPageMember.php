<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPageMember extends Model
{
    protected $fillable = ['page_id', 'user_id', 'role'];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SocialPage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
