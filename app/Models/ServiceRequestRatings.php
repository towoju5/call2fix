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

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
