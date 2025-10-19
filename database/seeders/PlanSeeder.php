<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'description' => 'Perfect for small businesses getting started',
                'price' => 29.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'max_networks' => 3,
                'sync_window_unit' => 'hour',
                'sync_window_size' => 2,
                'daily_sync_limit' => 100,
                'monthly_sync_limit' => 2000,
                'revenue_cap' => 10000,
                'orders_cap' => 500,
                'is_popular' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'description' => 'Ideal for growing businesses with multiple networks',
                'price' => 79.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'max_networks' => 10,
                'sync_window_unit' => 'hour',
                'sync_window_size' => 1,
                'daily_sync_limit' => 500,
                'monthly_sync_limit' => 10000,
                'revenue_cap' => 50000,
                'orders_cap' => 2500,
                'is_popular' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'For large businesses with high-volume operations',
                'price' => 199.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'max_networks' => 50,
                'sync_window_unit' => 'minute',
                'sync_window_size' => 30,
                'daily_sync_limit' => 2000,
                'monthly_sync_limit' => 50000,
                'revenue_cap' => 200000,
                'orders_cap' => 10000,
                'is_popular' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Unlimited',
                'description' => 'No limits for enterprise-level operations',
                'price' => 499.99,
                'billing_cycle' => 'monthly',
                'trial_days' => 14,
                'max_networks' => 999,
                'sync_window_unit' => 'minute',
                'sync_window_size' => 15,
                'daily_sync_limit' => null,
                'monthly_sync_limit' => null,
                'revenue_cap' => null,
                'orders_cap' => null,
                'is_popular' => false,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::firstOrCreate(
                ['name' => $planData['name']],
                $planData
            );
        }
    }
}