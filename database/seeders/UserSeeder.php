<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Adjust the path based on your User model location

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'account_type' => 'providers',
                'device_id' => 'device_1',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'phone' => '+2348105733895',
                'email' => 'providers@example.com',
                'profile_picture' => 'https://example.com/profile.jpg',
            ],
            [
                'account_type' => 'co-operate_accounts',
                'device_id' => 'device_2',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+2348105733896',
                'email' => 'cooperate@example.com',
                'profile_picture' => 'https://example.com/profile.jpg',
            ],
            [
                'account_type' => 'private_accounts',
                'device_id' => 'device_3',
                'first_name' => 'Mary',
                'last_name' => 'Johnson',
                'phone' => '+2348105733897',
                'email' => 'private@example.com',
                'profile_picture' => 'https://example.com/profile.jpg',
            ],
            [
                'account_type' => 'affiliates',
                'device_id' => 'device_4',
                'first_name' => 'Alice',
                'last_name' => 'Brown',
                'phone' => '+2348105733898',
                'email' => 'affiliates@example.com',
                'profile_picture' => 'https://example.com/profile.jpg',
            ],
            [
                'account_type' => 'suppliers',
                'device_id' => 'device_5',
                'first_name' => 'Bob',
                'last_name' => 'Taylor',
                'phone' => '+2348105733899',
                'email' => 'suppliers@example.com',
                'profile_picture' => 'https://example.com/profile.jpg',
            ]
        ];

        // Same password for all users
        $password = Hash::make('Call2fix!');

        foreach ($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                array_merge($user, ['password' => $password, 'main_account_role' => $user['account_type'], 'username' => explode("@", $user['email'])[0]])
            );
        }

    }
}
