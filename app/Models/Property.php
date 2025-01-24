<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "property_address",
        "property_type",
        "property_nearest_landmark",
        "property_name",
        "user_id",
        "porperty_longitude",
        "porperty_latitude"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(TransactionRecords::class);
    }
}
