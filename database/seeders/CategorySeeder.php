<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cats = [
            [
                'parent_category' => null,
                'category_name' => 'Electronics',
                'category_slug' => 'electronics',
                'category_image' => 'electronics.jpg',
                'category_description' => 'All electronic devices and accessories'
            ],
            [
                'parent_category' => null,
                'category_name' => 'Clothing',
                'category_slug' => 'clothing',
                'category_image' => 'clothing.jpg',
                'category_description' => 'Fashion items for all ages'
            ],
            [
                'parent_category' => null,
                'category_name' => 'Home & Garden',
                'category_slug' => 'home-garden',
                'category_image' => 'home-garden.jpg',
                'category_description' => 'Products for home improvement and gardening'
            ],
            [
                'parent_category' => 'Electronics',
                'category_name' => 'Smartphones',
                'category_slug' => 'smartphones',
                'category_image' => 'smartphones.jpg',
                'category_description' => 'Latest smartphones and accessories'
            ],
            [
                'parent_category' => 'Clothing',
                'category_name' => 'Men\'s Wear',
                'category_slug' => 'mens-wear',
                'category_image' => 'mens-wear.jpg',
                'category_description' => 'Clothing and accessories for men'
            ]
        ];


        foreach ($cats as $cat) {
            $category = \App\Models\Category::firstOrCreate($cat);

            $services = [];
            switch ($category->category_slug) {
                case 'electronics':
                    $services = [
                        ['name' => 'Repair', 'description' => 'Electronic device repair services'],
                        ['name' => 'Installation', 'description' => 'Electronic device installation services'],
                        ['name' => 'Maintenance', 'description' => 'Electronic device maintenance services']
                    ];
                    break;
                case 'clothing':
                    $services = [
                        ['name' => 'Tailoring', 'description' => 'Custom tailoring services'],
                        ['name' => 'Dry Cleaning', 'description' => 'Professional dry cleaning services'],
                        ['name' => 'Alterations', 'description' => 'Clothing alteration services']
                    ];
                    break;
                case 'home-garden':
                    $services = [
                        ['name' => 'Landscaping', 'description' => 'Professional landscaping services'],
                        ['name' => 'Interior Design', 'description' => 'Home interior design services'],
                        ['name' => 'Pest Control', 'description' => 'Home and garden pest control services']
                    ];
                    break;
                case 'smartphones':
                    $services = [
                        ['name' => 'Screen Repair', 'description' => 'Smartphone screen repair services'],
                        ['name' => 'Battery Replacement', 'description' => 'Smartphone battery replacement services'],
                        ['name' => 'Data Recovery', 'description' => 'Smartphone data recovery services']
                    ];
                    break;
                case 'mens-wear':
                    $services = [
                        ['name' => 'Custom Suits', 'description' => 'Custom suit tailoring services'],
                        ['name' => 'Shoe Repair', 'description' => 'Men\'s shoe repair services'],
                        ['name' => 'Personal Styling', 'description' => 'Personal styling services for men']
                    ];
                    break;
            }

            foreach ($services as $service) {
                \App\Models\Service::create([
                    'category_id' => $category->id,
                    'service_name' => $service['name'],
                    'service_slug' => \Str::slug($service['name']),
                ]);
            }
        }


    }
}
