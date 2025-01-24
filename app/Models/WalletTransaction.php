<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends \Towoju5\Wallet\Models\WalletTransaction
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wallet_id', 'type', 'amount', 'balance_before', 'balance_after',
        'decimal_places', 'meta', 'description'
    ];

    // Relationship to Wallet
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    // Cast the meta field as an array (not JSON)
    protected $casts = [
        'meta' => 'array',
        'description' => 'array',
    ];

    // Override the default toArray method to ensure it returns arrays
    public function toArray()
    {
        $array = parent::toArray();
        return $array;
    }
}
