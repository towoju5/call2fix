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
        // $paystack = new PaystackServices();
        // $account = self::createPaystackVirtualAccount();
        // return $account;
    }
    
}
