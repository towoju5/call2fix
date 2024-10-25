<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionRecords extends BaseModel
{
    use HasFactory, SoftDeletes;


    protected $guarded = [];
    
    protected $fillable = [
        'user_id',
        'wallet_id',
        'transaction_reference',
        'transaction_type',
        'transaction_slug',
        'transaction_status',
        'transaction_amount'
    ];
}
