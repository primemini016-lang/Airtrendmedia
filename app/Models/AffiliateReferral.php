<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateReferral extends Model
{
    protected $fillable = [
        'referrer_id', 'referee_id', 'reward_amount', 'status',
        'activation_deposit_id', 'reward_transaction_id', 'rewarded_at',
        'revoke_reason',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount' => 'decimal:2',
            'rewarded_at'   => 'datetime',
        ];
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    public function activationDeposit()
    {
        return $this->belongsTo(Deposit::class, 'activation_deposit_id');
    }

    public function rewardTransaction()
    {
        return $this->belongsTo(Transaction::class, 'reward_transaction_id');
    }
}
