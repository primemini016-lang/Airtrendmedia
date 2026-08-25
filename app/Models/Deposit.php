<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'user_id', 'method_id', 'amount', 'amount_paid', 'type',
        'reference', 'manual', 'note', 'reject_note', 'status', 'date',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'amount_paid'  => 'decimal:2',
            'manual'       => 'boolean',
            'date'         => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function method()
    {
        return $this->belongsTo(DepositMethod::class, 'method_id');
    }

    public function isPaid(): bool
    {
        return (int) $this->status === 1;
    }

    public function isActivation(): bool
    {
        return $this->type === 'activation';
    }
}
