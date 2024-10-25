<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends BaseModel
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'user_id',
        'property_id',
        'service_category_id',
        'service_id',
        'problem_title',
        'problem_description',
        'inspection_time',
        'inspection_date',
        'problem_images',
        'use_featured_providers',
        'featured_providers_id',
        'request_status',
        'department_id',
    ];

    protected $casts = [
        'problem_images' => 'array',
        'featured_providers_id' => 'array',
        'use_featured_providers' => 'boolean',
        'inspection_date' => 'date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(Category::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function featuredProviders()
    {
        return $this->belongsToMany(User::class, 'featured_providers_id');
    }

    public function reworkMessages()
    {
        return $this->hasMany(ReworkMessage::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }
    
}
