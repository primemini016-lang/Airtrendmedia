<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceListing extends Model
{
    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'price',
        'listing_type', 'image', 'gallery', 'location', 'status', 'views',
    ];

    protected $casts = [
        'price'   => 'decimal:2',
        'gallery' => 'array',
        'views'   => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function inquiries()
    {
        return $this->hasMany(MarketplaceInquiry::class, 'listing_id');
    }
}
