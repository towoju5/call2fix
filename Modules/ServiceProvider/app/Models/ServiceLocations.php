<?php

namespace Modules\ServiceProvider\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ServiceProvider\Database\Factories\ServiceLocationsFactory;

class ServiceLocations extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "address",
        "latitude",
        "longitude",
        "metadata",
        "user_id",
    ];

    protected $casts = [
        "metadata" => "array"
    ];

    protected static function newFactory(): ServiceLocationsFactory
    {
        //return ServiceLocationsFactory::new();
    }
}
