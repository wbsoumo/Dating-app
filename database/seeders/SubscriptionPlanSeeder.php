<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Weekly Boost',
                'slug' => 'weekly-boost',
                'description' => 'Fast-track your connections with 7 days of unlimited swipes and 5 free daily stars.',
                'price' => 199.00,
                'currency' => 'INR',
                'duration_days' => 7,
                'daily_swipe_limit' => -1, // unlimited
                'daily_star_allowance' => 5,
                'can_see_likes' => true,
                'can_rewind' => true,
                'can_boost' => true,
                'badge_color' => '#3b82f6',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Monthly Gold',
                'slug' => 'monthly-gold',
                'description' => 'Our most popular plan. Unlimited swipes, see who likes you, and 10 free daily stars.',
                'price' => 599.00,
                'currency' => 'INR',
                'duration_days' => 30,
                'daily_swipe_limit' => -1,
                'daily_star_allowance' => 10,
                'can_see_likes' => true,
                'can_rewind' => true,
                'can_boost' => true,
                'badge_color' => '#eab308',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Quarterly Platinum',
                'slug' => 'quarterly-platinum',
                'description' => '3 months of premium dating power. Highest priority in discovery and 15 daily stars.',
                'price' => 1499.00,
                'currency' => 'INR',
                'duration_days' => 90,
                'daily_swipe_limit' => -1,
                'daily_star_allowance' => 15,
                'can_see_likes' => true,
                'can_rewind' => true,
                'can_boost' => true,
                'badge_color' => '#8b5cf6',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Annual Elite',
                'slug' => 'annual-elite',
                'description' => 'Full 365 days of VIP dating. Maximum compatibility matching and 25 daily stars.',
                'price' => 3999.00,
                'currency' => 'INR',
                'duration_days' => 365,
                'daily_swipe_limit' => -1,
                'daily_star_allowance' => 25,
                'can_see_likes' => true,
                'can_rewind' => true,
                'can_boost' => true,
                'badge_color' => '#ec4899',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
