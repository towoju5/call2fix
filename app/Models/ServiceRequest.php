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
        'request_status',    // 'Draft','Pending','Processing','Bidding In Progress','Quote Accepted','Awaiting Payment','Payment Confirmed','On Hold','Work In Progress','Cancelled','Completed','Overdue','Closed','Rejected','Rework issued'
        'department_id',
        'approved_providers_id',
        'approved_artisan_id',
        'total_cost'
    ];

    protected $casts = [
        'problem_images' => 'array',
        'featured_providers_id' => 'array',
        'use_featured_providers' => 'boolean',
        'inspection_date' => 'date',
    ];
    
    
    protected $with = [
        'negotiations',
        'submittedQuotes',
        'user',
        'service_provider'
    ];

    public function negotiations()
    {
        return $this->hasMany(Negotiation::class, 'request_id');
    }

    public function submittedQuotes()
    {
        return $this->hasMany(SubmittedQuotes::class, 'request_id');
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service_provider()
    {
        return $this->belongsTo(User::class, 'approved_providers_id');
    }

    public function artisan()
    {
        return $this->belongsTo(User::class, 'approved_artisan_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(Category::class, 'service_category_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function reworkMessages()
    {
        return $this->hasMany(ReworkMessage::class);
    }

    public function checkIns()
    {
        return $this->hasMany(CheckIn::class);
    }

    // Handle featured providers (array)
    public function getFeaturedProvidersAttribute()
    {
        return User::whereIn('id', $this->featured_providers_id ?? [])->get();
    }
}
