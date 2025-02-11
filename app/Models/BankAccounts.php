<?php

namespace App\Models;

use App\Services\PaystackServices;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccounts extends BaseModel
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'account_name',
        'bank_name',
        'account_number',
        'bank_code',
        'provider_response',
        'provider_name',
        'account_type' // deposit or withdrawal
    ];

    protected $hidden = [
        'provider_response',
        'provider_name'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'provider_response' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateAccount()
    {
        $paystack = new PaystackServices();
        $account = $this->createPaystackVirtualAccount();
        return $account;
    }

    private function createPaystackVirtualAccount() {
        $paystack_secret_key = get_settings_value('paystack_secret_key', 'sk_test_390011d63d233cad6838504b657721883bc096ec');
        $url = "https://api.paystack.co/dedicated_account";
    
        $fields = [
            "customer" => [
                "email" => $user->email,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name
            ],
            "preferred_bank" => "wema-bank",
            "country" => "NG",
            "currency" => "NGN",
            "account_type" => "PAY_WITH_TRANSFER"
        ];
    
        $fields_string = json_encode($fields);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $paystack_secret_key",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return json_decode($response, true);
    }
    
}
