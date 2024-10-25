<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends BaseModel
{
    protected $fillable = ['user_id', 'currency', '_account_type', 'balance', 'meta', 'title'];


    protected $hidden = [
        '_account_type',
        'deleted_at',
        'decimal_places'
    ];

    protected $casts = [
        'meta' => 'json',
    ];

    // Method to get a specific wallet by currency and role
    public function getWallet($currency, $role = null)
    {
        // Use the passed $role if provided, otherwise use active_role()
        $role = $role ?? active_role();

        // Directly query the Wallet model for a wallet matching the currency, role, and user_id
        $wallet = Wallet::where([
            'currency' => $currency,
            '_account_type' => $role,
            'user_id' => auth()->id()
        ])->first();

        // If wallet doesn't exist, create it
        if (!$wallet) {
            $wallet = Wallet::create([
                'currency' => $currency,
                '_account_type' => $role,
                'user_id' => auth()->id()
            ]);
        }

        return $wallet;
    }


    // Method to get the balance of a specific wallet
    public function balance($currency = null)
    {
        if(null == $currency) {
            $currency = get_default_currency(auth()->id());
        }
        $role = active_role();
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

        return $save;
    }

    // Withdraw from the wallet
    public function withdraw($amount, $meta = [], $description = '', $decimalPlaces = 2)
    {
        if (self::balance() >= $amount * 100) {
            $balanceBefore = $this->balance;
            $this->balance -= $amount * 100;
            $save = $this->save();

            // Log the withdrawal transaction
            $this->logTransaction('withdrawal', $amount, $balanceBefore, $this->balance, $meta, $description, $decimalPlaces);

            return $save;
        } else {
            throw new \Exception("Insufficient balance: {$this->balance}, requested: {$amount}");
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
            '_account_type' => active_role(),
        ]);
    }

    // The user who owns the wallet
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationship between User and Wallet
    public function wallets()
    {
        return $this->user->wallets(); // Fetch wallets from the User model
    }
}
