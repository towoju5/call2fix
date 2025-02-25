<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function configs()
    {
        return get_success_response([
            'adminstrative_fee' => get_settings_value('administrative_fee'), 
            'assessment_fee' => get_settings_value('assessment_fee'), 
            'vat_percentage' => get_settings_value('vat_percentage', 7.5),
            'paystack_key' => get_settings_value('paystack_public_key', "pk_test_efa4faf12f2a68dabbef9bdab4f2d725b3bef706"),
        ], "service configs");
    }
}
