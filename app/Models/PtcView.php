<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtcView extends Model
{
    protected $fillable = [
        'ptc_ad_id', 'user_id', 'reward', 'watched_seconds',
        'ip_address', 'status', 'started_at', 'confirmed_at',
    ];

    protected $casts = [
        'reward'         => 'decimal:4',
        'watched_seconds'=> 'integer',
        'started_at'     => 'datetime',
        'confirmed_at'   => 'datetime',
    ];

    public function ad()
    {
        return $this->belongsTo(PtcAd::class, 'ptc_ad_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
