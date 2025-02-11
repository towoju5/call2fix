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
            ['key' => 'support_email', 'value' => 'support@call2fix.com'],
            ['key' => 'administrative_fee', 'value' => '5.00'],
            ['key' => 'website_name', 'value' => 'Your Website Name'],
            ['key' => 'alphamaed_service_account_id', 'value' => 'your_service_account_id'],
            ['key' => 'recharge_points', 'value' => '0.05'],
            ['key' => 'private_referal_commission', 'value' => '50'],
            ['key' => 'supplier_referal_commission', 'value' => '50'],
            ['key' => 'providers_referal_commission', 'value' => '50'],
            ['key' => 'affiliates_referal_commission', 'value' => '50'],
            ['key' => 'private_accounts_referal_commission', 'value' => '50'],
            ['key' => 'co-operate_accounts_referal_commission', 'value' => '50'],

            // paystack keys
            ['key' => 'paystack_secret_key', 'value' => 'pk_test_efa4faf12f2a68dabbef9bdab4f2d725b3bef706'],
            ['key' => 'paystack_public_key', 'value' => 'sk_test_390011d63d233cad6838504b657721883bc096ec'],
        ];

        foreach ($settings as $setting) {
            Settings::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
