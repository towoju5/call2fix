<?php

namespace Database\Seeders;

use App\Models\Settings;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'max_provider_radius', 'value' => '50'],
            ['key' => 'per_page', 'value' => '15'],
            ['key' => 'withdrawal_fee', 'value' => '1'],
            ['key' => 'administrative_fee', 'value' => '5.00'],
            ['key' => 'website_name', 'value' => 'Your Website Name'],
            ['key' => 'alphamaed_service_account_id', 'value' => 'your_service_account_id'],
        ];

        foreach ($settings as $setting) {
            Settings::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}