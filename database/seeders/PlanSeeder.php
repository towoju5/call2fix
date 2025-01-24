<?php

namespace Database\Seeders;

use Creatydev\Plans\Models\PlanFeatureModel;
use Creatydev\Plans\Models\PlanModel;
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
        // Free Plan
        $plan = PlanModel::create([
            'name' => 'Free Plan',
            'description' => 'The biggest plans of all.',
            'price' => 0,
            'currency' => 'NGN',
            'duration' => 0,
            'metadata' => [
                'service_provider' => [
                    'description' => 'Get featured and reach customers before non-subscribers.',
                    'features' => [],
                ],
                'suppliers' => [
                    'description' => 'Get featured and reach customers before non-subscribers.',
                    'features' => [],
                ],
            ],
        ]);
    
        // Basic Plan
        $plan = PlanModel::create([
            'name' => 'Basic Plan',
            'description' => 'The biggest plans of all.',
            'price' => 5000,
            'currency' => 'NGN',
            'duration' => 30,
            'metadata' => [
                'service_provider' => [
                    'description' => 'Get featured and reach customers before non-subscribers.',
                    'features' => [
                        'Appear on the featured list but below Standard and Premium tiers',
                        'Get seen by customers before non-subscribed merchants',
                        'Receive a Priority Badge to highlight your listing',
                    ],
                ],
                'suppliers' => [
                    'description' => 'Get featured and reach customers before non-subscribers.',
                    'features' => [
                        'Appear on the featured list but below Standard and Premium tiers',
                        'Get seen by customers before non-subscribed merchants',
                        'Receive a Priority Badge to highlight your listing',
                    ],
                ],
            ],
        ]);

        // Standard Plan
        $plan2 = PlanModel::create([
            'name' => 'Standard Plan',
            'description' => 'The biggest plans of all.',
            'price' => 10000,
            'currency' => 'NGN',
            'duration' => 30, // in days
            'metadata' => [
                'service_provider' => [
                    'description' => 'Broaden your reach with more categories, artisans, and locations.',
                    'features' => [
                        'Includes all Basic Plan features',
                        'Add up to 5 more artisans per category',
                        'Showcase up to 3 additional service categories',
                        'Add two more locations',
                    ],
                ],
                'suppliers' => [
                    'description' => 'Broaden your reach with more categories and products.',
                    'features' => [
                        'Includes all Basic Plan features',
                        'Showcase up to 3 additional product categories',
                        'Display up to 20 additional products per category',
                    ],
                ],
            ],
        ]);

        // Premium Plan
        $plan3 = PlanModel::create([
            'name' => 'Premium Plan',
            'description' => 'The biggest plans of all.',
            'price' => 25000,
            'currency' => 'NGN',
            'duration' => 30, // in days
            'metadata' => [
                'service_provider' => [
                    'description' => 'Unlimited listings for maximum visibility and customer preference.',
                    'features' => [
                        'Includes all Standard Plan features',
                        'Unlimited service categories',
                        'Unlimited artisans per category',
                        'Unlimited locations',
                    ],
                ],
                'suppliers' => [
                    'description' => 'Unlimited listings for maximum visibility and customer preference.',
                    'features' => [
                        'Includes all Standard Plan features',
                        'Unlimited product categories',
                        'Unlimited products per category',
                    ],
                ],
            ],
        ]);


        // Basic Plan Features
        $plan->features()->saveMany([
            new PlanFeatureModel([
                'name' => 'Featured Listing for Service Providers',
                'code' => 'service_provider.featured',
                'description' => 'Appear on the featured list but below Standard and Premium tiers for service providers.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Basic', 'tier' => 'service_provider'],
            ]),
            new PlanFeatureModel([
                'name' => 'Priority Badge for Service Providers',
                'code' => 'service_provider.priority_badge',
                'description' => 'Receive a Priority Badge to highlight your listing as a service provider.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Basic', 'tier' => 'service_provider'],
            ]),
            new PlanFeatureModel([
                'name' => 'Featured Listing for Suppliers',
                'code' => 'supplier.featured',
                'description' => 'Appear on the featured list but below Standard and Premium tiers for suppliers.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Basic', 'tier' => 'suppliers'],
            ]),
            new PlanFeatureModel([
                'name' => 'Priority Badge for Suppliers',
                'code' => 'supplier.priority_badge',
                'description' => 'Receive a Priority Badge to highlight your listing as a supplier.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Basic', 'tier' => 'suppliers'],
            ]),
        ]);

        // Standard Plan Features
        $plan2->features()->saveMany([
            new PlanFeatureModel([
                'name' => 'Additional Artisans for Service Providers',
                'code' => 'service_provider.artisans',
                'description' => 'Add up to 5 more artisans per category for service providers.',
                'type' => 'limit',
                'limit' => 5,
                'metadata' => ['plan' => 'Standard', 'tier' => 'service_provider'],
            ]),
            new PlanFeatureModel([
                'name' => 'Additional Service Categories for Service Providers',
                'code' => 'service_provider.categories',
                'description' => 'Showcase up to 3 additional service categories for service providers.',
                'type' => 'limit',
                'limit' => 3,
                'metadata' => ['plan' => 'Standard', 'tier' => 'service_provider'],
            ]),
            new PlanFeatureModel([
                'name' => 'Additional Locations for Service Providers',
                'code' => 'service_provider.locations',
                'description' => 'Add two more locations for service providers.',
                'type' => 'limit',
                'limit' => 2,
                'metadata' => ['plan' => 'Standard', 'tier' => 'service_provider'],
            ]),
            new PlanFeatureModel([
                'name' => 'Additional Product Categories for Suppliers',
                'code' => 'supplier.product_categories',
                'description' => 'Showcase up to 3 additional product categories for suppliers.',
                'type' => 'limit',
                'limit' => 3,
                'metadata' => ['plan' => 'Standard', 'tier' => 'suppliers'],
            ]),
            new PlanFeatureModel([
                'name' => 'Additional Products per Category for Suppliers',
                'code' => 'supplier.products',
                'description' => 'Display up to 20 additional products per category for suppliers.',
                'type' => 'limit',
                'limit' => 20,
                'metadata' => ['plan' => 'Standard', 'tier' => 'suppliers'],
            ]),
        ]);

        // Premium Plan Features
        $plan3->features()->saveMany([
            new PlanFeatureModel([
                'name' => 'Unlimited Service Categories for Service Providers',
                'code' => 'service_provider.unlimited_categories',
                'description' => 'Unlimited service categories for maximum visibility.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Premium', 'tier' => 'service_provider'],
            ]),
            new PlanFeatureModel([
                'name' => 'Unlimited Artisans per Category for Service Providers',
                'code' => 'service_provider.unlimited_artisans',
                'description' => 'Unlimited artisans per category for service providers.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Premium', 'tier' => 'service_provider'],
            ]),
            new PlanFeatureModel([
                'name' => 'Unlimited Locations for Service Providers',
                'code' => 'service_provider.unlimited_locations',
                'description' => 'Unlimited locations for service providers.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Premium', 'tier' => 'service_provider'],
            ]),
            new PlanFeatureModel([
                'name' => 'Unlimited Product Categories for Suppliers',
                'code' => 'supplier.unlimited_categories',
                'description' => 'Unlimited product categories for maximum reach.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Premium', 'tier' => 'suppliers'],
            ]),
            new PlanFeatureModel([
                'name' => 'Unlimited Products per Category for Suppliers',
                'code' => 'supplier.unlimited_products',
                'description' => 'Unlimited products per category for suppliers.',
                'type' => 'feature',
                'metadata' => ['plan' => 'Premium', 'tier' => 'suppliers'],
            ]),
        ]);

    }
}
