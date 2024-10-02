<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceArea extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_area_title',
    ];
}
