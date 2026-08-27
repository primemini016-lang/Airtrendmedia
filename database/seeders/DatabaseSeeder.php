<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountrySeeder::class,
            CurrencySeeder::class,
            AppSettingSeeder::class,
            CategorySeeder::class,
            SetupSeeder::class,
        ]);

        // NOTE: No demo admin or user is created by the seeder.
        // The first admin account is created securely during the web installer.
    }
}
