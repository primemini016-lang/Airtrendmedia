<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id', 'method_id', 'amount', 'fee', 'paid', 'details',
        'status', 'reject_note', 'date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee'    => 'decimal:2',
            'paid'   => 'decimal:2',
            'date'   => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function method()
    {
        return $this->belongsTo(WithdrawalMethod::class, 'method_id');
    }
}
