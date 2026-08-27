<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogView extends Model
{
    protected $fillable = ['post_id', 'user_id', 'ip_address', 'user_agent'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }
}
