<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with master data.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            InterestSeeder::class,
            SubscriptionPlanSeeder::class,
            StarPackageSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
