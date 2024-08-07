<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Unicodeveloper\Paystack\Facades\Paystack;

class BankAccounts extends Model
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

    /**
     * Generate a new bank account for a user.
     *
     * @param int $user_id The ID of the user
     * @return mixed
     */
    public function generateAccount($user_id)
    {
        $paystack = new Paystack();
        $generateAccount = $paystack->generateAccount($user_id);
    }

    /**
     * Get the account information for a user.
     *
     * @param int $user_id The ID of the user
     * @return mixed
     */
    public function getAccountInfo($user_id)
    {
        $result = self::whereUserId($user_id)->first();
        return $result;
    }

}
