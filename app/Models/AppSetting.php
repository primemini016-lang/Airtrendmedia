<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'need_verification'  => 'boolean',
            'saas'               => 'boolean',
            'manual_payment'     => 'boolean',
            'affiliate_enabled'  => 'boolean',
            'ann_status'         => 'boolean',
            'withdraw_com'       => 'decimal:2',
            'task_com'           => 'decimal:2',
            'activation_fee'     => 'decimal:2',
            'affiliate_reward'   => 'decimal:2',
        ];
    }

    public function defaultCurrency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }
}
