<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['USD', 'US Dollar', '$', 1.000000, false, false],
            ['NGN', 'Nigerian Naira', '₦', 1500.000000, true, true],
            ['GHS', 'Ghanaian Cedi', '₵', 15.000000, false, true],
            ['ZAR', 'South African Rand', 'R', 18.000000, false, true],
            ['KES', 'Kenyan Shilling', 'KSh', 130.000000, false, true],
            ['EUR', 'Euro', '€', 0.920000, false, false],
            ['GBP', 'British Pound', '£', 0.790000, false, false],
            ['INR', 'Indian Rupee', '₹', 83.000000, false, false],
            ['PKR', 'Pakistani Rupee', '₨', 278.000000, false, false],
            ['BDT', 'Bangladeshi Taka', '৳', 110.000000, false, false],
            ['PHP', 'Philippine Peso', '₱', 56.000000, false, false],
            ['BRL', 'Brazilian Real', 'R$', 5.100000, false, false],
        ];

        $position = 0;
        foreach ($currencies as $c) {
            Currency::updateOrCreate(
                ['code' => $c[0]],
                [
                    'name'               => $c[1],
                    'symbol'             => $c[2],
                    'usd_value'          => $c[3],
                    'is_default'         => $c[4],
                    'paystack_supported' => $c[5],
                    'active'             => true,
                    'position'           => $position++,
                ]
            );
        }

        // Ensure only one default currency.
        $default = Currency::where('is_default', true)->first();
        if ($default) {
            Currency::where('id', '!=', $default->id)->update(['is_default' => false]);
        }
    }
}
