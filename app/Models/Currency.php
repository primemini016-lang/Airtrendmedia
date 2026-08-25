<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code', 'name', 'symbol', 'usd_value', 'is_default',
        'paystack_supported', 'active', 'position',
    ];

    protected function casts(): array
    {
        return [
            'usd_value'           => 'decimal:6',
            'is_default'          => 'boolean',
            'paystack_supported'  => 'boolean',
            'active'              => 'boolean',
        ];
    }

    /**
     * Convert an amount in USD to this currency.
     */
    public function fromUsd(float $usd): float
    {
        return round($usd * (float) $this->usd_value, 2);
    }

    /**
     * Convert an amount in this currency to USD.
     */
    public function toUsd(float $amount): float
    {
        $value = (float) $this->usd_value;
        if ($value <= 0) {
            return 0.0;
        }
        return round($amount / $value, 6);
    }

    public function format(float $amount): string
    {
        return $this->symbol.number_format($amount, 2);
    }
}
