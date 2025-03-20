<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Withdrawal extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_id',
        'amount',
        'fee',
        'status',
        'transaction_reference',
    ];

    protected $with = ['user', 'bank'];

    protected $hidden = ['meta'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(BankAccounts::class, 'bank_id');
    }
}
