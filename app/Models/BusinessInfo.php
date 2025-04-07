<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessInfo extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'businessName',
        'cacNumber',
        'officeAddress',
        'businessCategory',
        'businessDescription',
        'businessIdType',
        'businessIdNumber',
        'businessIdImage',
        'businessBankInfo'
    ];

    protected $casts = [
        'businessBankInfo' => 'array',
        'businessCategory' => 'array',
        'officeAddress' => 'array',
    ];
}
