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
            DemoDataSeeder::class,
        ]);

        // NOTE: No demo admin or user is created by the core seeders.
        // The first admin account is created securely during the web installer.
        // DemoDataSeeder creates 10 demo users with sample content for testing.
    }
}
