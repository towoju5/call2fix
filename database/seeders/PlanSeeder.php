<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravelcm\Subscriptions\Models\Plan;
use Laravelcm\Subscriptions\Models\Feature;
use Laravelcm\Subscriptions\Interval;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::firstOrCreate(
            [
                'slug' => strtolower(str_replace(" ", "-", 'Basic Plan'))
            ],
            [
                'name' => 'Basic Plan',
                'slug' => strtolower(str_replace(" ", "-", 'Basic Plan')),
                'price' => 5000,
                'service_category_limit' => 3,
                'artisan_limit' => 5,
                'product_category_limit' => 3,
                'product_limit' => 15,
                'currency' => 'NGN'
            ]
        );

        Plan::firstOrCreate(
            [
                'slug' => strtolower(str_replace(" ", "-", 'Standard Plan'))
            ],
            [
                'name' => 'Standard Plan',
                'slug' => strtolower(str_replace(" ", "-", 'Standard Plan')),
                'price' => 10000,
                'service_category_limit' => 6,
                'artisan_limit' => 10,
                'product_category_limit' => 6,
                'product_limit' => 20,
                'currency' => 'NGN'
            ]
        );

        Plan::firstOrCreate(
            [
                'slug' => strtolower(str_replace(" ", "-", 'Premium Plan'))
            ],
            [
                'name' => 'Premium Plan',
                'slug' => strtolower(str_replace(" ", "-", 'Premium Plan')),
                'price' => 25000,
                'service_category_limit' => null, // Unlimited
                'artisan_limit' => null, // Unlimited
                'product_category_limit' => null, // Unlimited
                'product_limit' => null, // Unlimited
                'currency' => 'NGN'
            ]
        );
    }
}
