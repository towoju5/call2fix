<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiLog extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method',
        'url',
        'request_headers',
        'request_body',
        'response_status',
        'response_headers',
        'response_body',
        'request_ip_address'
    ];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
        'response_headers' => 'array',
        'response_body' => 'array',
    ];

    protected $hidden = [
        'request_headers',
        'response_headers',
        // 'response_body',
    ];
}