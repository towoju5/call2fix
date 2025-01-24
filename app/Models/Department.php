<?php

namespace App\Models;

use App\Models\WalletTransaction as Transaction;
use App\Traits\HasWallets;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends BaseModel
{
    use HasFactory, HasWallets;

    protected $fillable = ['name', 'owner_id', '_account_type'];

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


    // Automatically generate a UUID when creating a new model instance
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->_account_type === null) {
                $model->_account_type = active_role();
            }
        });

        static::where('_account_type', active_role());
    }
}
