<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'towojuads@gmail.com'],
            [
                'name' => 'Admin',
                'email' => 'towojuads@gmail.com',
                'password' => bcrypt('adedayo201'),
            ]
        );
    }
}
