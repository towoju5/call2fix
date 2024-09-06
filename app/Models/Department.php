<?php

namespace App\Models;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Traits\HasWallets;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory, HasWallets;

    protected $fillable = ['name'];

    // Relationship: Department belongs to many users
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Relationship: Department has many transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
