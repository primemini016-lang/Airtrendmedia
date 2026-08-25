<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceInquiry extends Model
{
    protected $fillable = [
        'listing_id', 'buyer_id', 'message', 'offer_price', 'status',
    ];

    protected $casts = [
        'offer_price' => 'decimal:2',
    ];

    public function listing()
    {
        return $this->belongsTo(MarketplaceListing::class, 'listing_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
