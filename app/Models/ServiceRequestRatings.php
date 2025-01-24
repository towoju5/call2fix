<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestRatings extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'user_id',
        'work_quality',
        'timeliness',
        'communication',
        'professionalism',
        'cleanliness',
        'pricing_transparency',
        'tools_quality',
        'issue_handling',
        'safety_adherence',
        'overall_satisfaction',
        'comment',
    ];
    
    protected $casts = [
        'work_quality' => 'array',
        'timeliness' => 'array',
        'communication' => 'array',
        'professionalism' => 'array',
        'cleanliness' => 'array',
        'pricing_transparency' => 'array',
        'tools_quality' => 'array',
        'issue_handling' => 'array',
        'safety_adherence' => 'array',
        'overall_satisfaction' => 'array',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
