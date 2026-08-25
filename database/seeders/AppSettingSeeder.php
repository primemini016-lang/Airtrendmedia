<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaultCurrency = Currency::where('is_default', true)->first();

        AppSetting::updateOrCreate(
            ['id' => 1],
            [
                'name'             => 'MiniWorkers',
                'logotext'         => 'MiniWorkers',
                'url'              => config('app.url'),
                'default_currency_id' => $defaultCurrency?->id,
                'need_verification'=> false,
                'saas'             => true,
                'manual_payment'   => true,
                'withdraw_com'     => 10.00,
                'task_com'         => 15.00,
                'activation_fee'   => 5.00,
                'affiliate_reward' => 1.50,
                'affiliate_enabled'=> true,
                'ann_status'       => false,
                'ann_text'         => null,
                'booking_limit'    => 5,
                'ss_limit'         => 3,
                'address'          => null,
                'contact_email'    => 'support@example.com',
                'phone'            => null,
                'footer_text'      => 'MiniWorkers — Earn by completing micro tasks.',
                'primary_color'    => '#2563eb',
                'accent_color'     => '#1e40af',
            ]
        );
    }
}
