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
        $plan = Plan::create([
            'name' => 'Pro',
            'description' => 'Pro plan',
            'price' => 9.99,
            'signup_fee' => 1.99,
            'invoice_period' => 1,
            'invoice_interval' => Interval::MONTH->value,
            'trial_period' => 15,
            'trial_interval' => Interval::DAY->value,
            'sort_order' => 1,
            'currency' => 'USD',
        ]);
        
        // Create multiple plan features at once
        $plan->features()->saveMany([
            new Feature(['name' => 'priority_badge', 'value' => 50, 'sort_order' => 1]),
            new Feature(['name' => 'featured_listing', 'value' => 10, 'sort_order' => 5]),
            new Feature(['name' => 'listing_duration_days', 'value' => 30, 'sort_order' => 10, 'resettable_period' => 1, 'resettable_interval' => 'month']),
            new Feature(['name' => 'listing_title_bold', 'value' => 'Y', 'sort_order' => 15])
        ]);
    }
}
