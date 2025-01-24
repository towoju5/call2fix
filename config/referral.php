<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Referral System Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the referral system.
    |
    */
    // The name of the referral cookie
    'cookie_name' => 'call2Fix',

    // Expiry time for the referral cookie in minutes
    'cookie_expiry' => 525600, // 1 year

    // The prefix used for referral links
    'route_prefix' => 'referral',

    // The prefix used for referral code
    'ref_code_prefix' => '',

    // The route where users will be redirected after clicking on a referral link
    'redirect_route' => 'orders.create',

    // The model class for the user
    'user_model' => 'App\Models\User',

    // The length of the referral code generated for each user
    'referral_length' => 8,
];
