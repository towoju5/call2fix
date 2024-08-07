<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends BaseModel
{
    use HasFactory, SoftDeletes;

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function service_requests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
