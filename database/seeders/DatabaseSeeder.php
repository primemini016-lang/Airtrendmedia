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

        // NOTE: During the web installer (APP_INSTALL=true) the DemoDataSeeder
        // skips its demo admin and the installer creates the real super-admin
        // from the user's form input. When seeding standalone (artisan db:seed
        // or migrate:fresh --seed without the installer) a demo admin is created
        // automatically (username=admin / password=admin123) so the platform is
        // usable immediately. DemoDataSeeder also creates 10 demo users with
        // sample gigs, listings, tasks, blogs and reviews for testing.
    }
}
