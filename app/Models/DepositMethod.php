<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositMethod extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo', 'min_amount', 'instructions',
        'active', 'manual', 'position',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'active'     => 'boolean',
            'manual'     => 'boolean',
        ];
    }
}
