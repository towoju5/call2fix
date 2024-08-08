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
            \App\Models\Category::create($cat);
        }
    }
}
