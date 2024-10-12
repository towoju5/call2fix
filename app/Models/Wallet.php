<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'currency', '_account_type', 'balance', 'meta', 'title'];

    protected $casts = [
        'meta' => 'json',
    ];

    // Method to get a specific wallet by currency and role
    public function getWallet($currency, $role)
    {
        return $this->wallets()->where('currency', $currency)->where('_account_type', $role)->firstOrCreate([
            'currency' => $currency,
            '_account_type' => $role,
            'balance' => 0
        ]);
    }

    // Method to get the balance of a specific wallet
    public function balance($currency, $role)
    {
        $wallet = $this->getWallet($currency, $role);
        return $wallet->balance;
    }


    // Relationship to WalletTransaction
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    // Deposit to the wallet
    public function deposit($amount, $meta = [], $description = '', $decimalPlaces = 2)
    {
        $balanceBefore = $this->balance;
        $this->balance += $amount * 100;
        $save = $this->save();

        // Log the deposit transaction
        $this->logTransaction('deposit', $amount, $balanceBefore, $this->balance, $meta, $description, $decimalPlaces);

        if ($save) {
            return true;
        }

        return false;
    }

    // Withdraw from the wallet
    public function withdraw($amount, $meta = [], $description = '', $decimalPlaces = 2)
    {
        if ($this->balance >= $amount) {
            $balanceBefore = $this->balance;
            $this->balance -= $amount * 100;
            $save = $this->save();

            // Log the withdrawal transaction
            $this->logTransaction('withdrawal', $amount, $balanceBefore, $this->balance, $meta, $description, $decimalPlaces);

            if ($save) {
                return true;
            }

            return false;
        } else {
            throw new \Exception('Insufficient balance');
        }
    }

    // Log each transaction in the WalletTransaction model
    protected function logTransaction($type, $amount, $balanceBefore, $balanceAfter, $meta, $description, $decimalPlaces)
    {
        $this->transactions()->create([
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'decimal_places' => $decimalPlaces,
            'meta' => $meta,
            'description' => $description,
        ]);
    }

    // The user who owns the wallet
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Override the default toArray method to ensure it returns arrays
    public function toArray()
    {
        $array = parent::toArray();
        return $array;
    }

     // Define relationship between User and Wallet
     public function wallets()
     {
         return $this->hasMany(Wallet::class);
     }
}
