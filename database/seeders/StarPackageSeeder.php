<?php

namespace Database\Seeders;

use App\Models\StarPackage;
use Illuminate\Database\Seeder;

class StarPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Starter Pack',
                'stars_count' => 5,
                'price' => 99.00,
                'currency' => 'INR',
                'discount_percent' => 0,
                'badge' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Crush Pack',
                'stars_count' => 25,
                'price' => 399.00,
                'currency' => 'INR',
                'discount_percent' => 20,
                'badge' => '20% OFF',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Superstar Pack',
                'stars_count' => 60,
                'price' => 799.00,
                'currency' => 'INR',
                'discount_percent' => 35,
                'badge' => 'MOST POPULAR',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'VIP Mega Pack',
                'stars_count' => 150,
                'price' => 1499.00,
                'currency' => 'INR',
                'discount_percent' => 50,
                'badge' => 'BEST VALUE',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($packages as $pkg) {
            StarPackage::updateOrCreate(
                ['name' => $pkg['name']],
                $pkg
            );
        }
    }
}
