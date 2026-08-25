<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gig extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'price',
        'image', 'gallery', 'social_platform', 'social_url',
        'status', 'views', 'sales',
    ];

    protected $casts = [
        'price'   => 'decimal:2',
        'gallery' => 'array',
        'views'   => 'integer',
        'sales'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function orders()
    {
        return $this->hasMany(GigOrder::class);
    }
}
