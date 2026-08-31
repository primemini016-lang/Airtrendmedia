<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogShare extends Model
{
    protected $fillable = ['post_id', 'user_id', 'platform'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class);
    }
}
