<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GigOrder extends Model
{
    protected $fillable = [
        'gig_id', 'buyer_id', 'requirements', 'status',
        'proof_images', 'delivered_at', 'completed_at',
    ];

    protected $casts = [
        'proof_images' => 'array',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function gig()
    {
        return $this->belongsTo(Gig::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
