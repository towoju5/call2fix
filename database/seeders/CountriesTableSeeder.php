<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ['dialing_code' => '+1', 'country_name' => 'United States', 'iso2' => 'US', 'iso3' => 'USA', 'currency_name' => 'US Dollar', 'currency_code' => 'USD', 'currency_symbol' => '$'],
            ['dialing_code' => '+44', 'country_name' => 'United Kingdom', 'iso2' => 'GB', 'iso3' => 'GBR', 'currency_name' => 'Pound Sterling', 'currency_code' => 'GBP', 'currency_symbol' => '£'],
            ['dialing_code' => '+91', 'country_name' => 'India', 'iso2' => 'IN', 'iso3' => 'IND', 'currency_name' => 'Indian Rupee', 'currency_code' => 'INR', 'currency_symbol' => '₹'],
            ['dialing_code' => '+81', 'country_name' => 'Japan', 'iso2' => 'JP', 'iso3' => 'JPN', 'currency_name' => 'Japanese Yen', 'currency_code' => 'JPY', 'currency_symbol' => '¥'],
            ['dialing_code' => '+86', 'country_name' => 'China', 'iso2' => 'CN', 'iso3' => 'CHN', 'currency_name' => 'Chinese Yuan', 'currency_code' => 'CNY', 'currency_symbol' => '¥'],
            ['dialing_code' => '+49', 'country_name' => 'Germany', 'iso2' => 'DE', 'iso3' => 'DEU', 'currency_name' => 'Euro', 'currency_code' => 'EUR', 'currency_symbol' => '€'],
            ['dialing_code' => '+33', 'country_name' => 'France', 'iso2' => 'FR', 'iso3' => 'FRA', 'currency_name' => 'Euro', 'currency_code' => 'EUR', 'currency_symbol' => '€'],
            ['dialing_code' => '+61', 'country_name' => 'Australia', 'iso2' => 'AU', 'iso3' => 'AUS', 'currency_name' => 'Australian Dollar', 'currency_code' => 'AUD', 'currency_symbol' => '$'],
            ['dialing_code' => '+39', 'country_name' => 'Italy', 'iso2' => 'IT', 'iso3' => 'ITA', 'currency_name' => 'Euro', 'currency_code' => 'EUR', 'currency_symbol' => '€'],
            ['dialing_code' => '+55', 'country_name' => 'Brazil', 'iso2' => 'BR', 'iso3' => 'BRA', 'currency_name' => 'Brazilian Real', 'currency_code' => 'BRL', 'currency_symbol' => 'R$'],
            ['dialing_code' => '+7', 'country_name' => 'Russia', 'iso2' => 'RU', 'iso3' => 'RUS', 'currency_name' => 'Russian Ruble', 'currency_code' => 'RUB', 'currency_symbol' => '₽'],
            ['dialing_code' => '+234', 'country_name' => 'Nigeria', 'iso2' => 'NG', 'iso3' => 'NGA', 'currency_name' => 'Nigerian Naira', 'currency_code' => 'NGN', 'currency_symbol' => '₦'],
            ['dialing_code' => '+27', 'country_name' => 'South Africa', 'iso2' => 'ZA', 'iso3' => 'ZAF', 'currency_name' => 'South African Rand', 'currency_code' => 'ZAR', 'currency_symbol' => 'R'],
            ['dialing_code' => '+971', 'country_name' => 'United Arab Emirates', 'iso2' => 'AE', 'iso3' => 'ARE', 'currency_name' => 'UAE Dirham', 'currency_code' => 'AED', 'currency_symbol' => 'د.إ'],
            ['dialing_code' => '+82', 'country_name' => 'South Korea', 'iso2' => 'KR', 'iso3' => 'KOR', 'currency_name' => 'South Korean Won', 'currency_code' => 'KRW', 'currency_symbol' => '₩'],
            ['dialing_code' => '+94', 'country_name' => 'Sri Lanka', 'iso2' => 'LK', 'iso3' => 'LKA', 'currency_name' => 'Sri Lankan Rupee', 'currency_code' => 'LKR', 'currency_symbol' => 'Rs'],
        ];

        DB::table('countries')->insert($countries);
    }
}
