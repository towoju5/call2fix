<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReworkMessage extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_request_id',
        'user_id',
        'message',
        'images',
        '_account_type',
        'is_read'
    ];


    protected $casts = [
        'images' => 'array',
    ];

    protected $hidden = [
        "deleted_at",
        "updated_at"
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
