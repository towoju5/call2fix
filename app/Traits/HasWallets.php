<?php
namespace App\Traits;

use App\Models\Wallet;
use Exception;
use Log;

trait HasWallets
{
    public function createWallet($meta, $title = null)
    {
        if(is_array($meta) && isset($meta['slug'])) {
            $currency = $meta['slug'];
        }
        
        $create = $this->wallets()->create([
            'currency' => $currency,
            '_account_type' => active_role(),
            'balance' => 0,
            'meta' => $meta,
            'title' => $title ?? ucfirst($currency. " wallet"),
        ])->toArray();

        Log::info('Wallet created successfully', $create);

        return $create;
    }

    /**
     * Method to get a specific wallet by currency and role
     * @param mixed $currency
     * @param mixed $role
     * @return Wallet
     *
     */
    public function getWallet($currency = 'ngn', $role = null)
    {
        $role = active_role();
        return $this->wallets()->where('currency', $currency)->where('_account_type', $role)->firstOrCreate([
            'currency' => $currency,
            '_account_type' => $role,
            'balance' => 0
        ]);
    }

    // Deposit into a specific wallet
    public function deposit($currency = 'ngn', $role, $amount = 0, $meta = [], $description = '', $decimalPlaces = 2)
    {
        $role ??= active_role();
        $wallet = $this->getWallet($currency, $role);
        $wallet->deposit($amount, $meta, $description, $decimalPlaces);

        return $wallet->toArray();
    }

    // Withdraw from a specific wallet
    public function withdraw($currency, $amount, $meta = [], $description = '', $decimalPlaces = 2)
    {
        $role = active_role();
        $wallet = $this->getWallet($currency, $role);

        if ($wallet->balance < $amount) {
            throw new Exception('Insufficient balance');
        }

        $wallet->withdraw($amount, $meta, $description, $decimalPlaces);

        return $wallet->toArray();
    }

    // Define relationship between User and Wallet
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    // Override toArray to return arrays
    public function toArray()
    {
        return parent::toArray();
    }
}
