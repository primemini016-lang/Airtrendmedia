<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'reference', 'amount', 'balance_after',
        'currency', 'status', 'description', 'related_id', 'related_type',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'balance_after'  => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
