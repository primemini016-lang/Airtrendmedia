<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalMethod extends Model
{
    protected $fillable = [
        'parent_id', 'name', 'slug', 'logo', 'min_amount',
        'active', 'gift_card', 'position',
    ];

    protected function casts(): array
    {
        return [
            'min_amount' => 'decimal:2',
            'active'     => 'boolean',
            'gift_card'  => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(WithdrawalMethod::class, 'parent_id');
    }

    public function options()
    {
        return $this->hasMany(WithdrawalMethod::class, 'parent_id')->orderBy('min_amount');
    }
}
