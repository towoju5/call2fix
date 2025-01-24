<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * this model handles the service provider's
 *  quotes for a particular service request.
 * 
 */
class SubmittedQuotes extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "provider_id", // service provider ID
        "request_id",
        "workmanship",
        "items",
        "sla_duration",
        "sla_start_date",
        "summary_note",
        "administrative_fee",
        "service_vat",
        "attachments",
        "total_charges",
        "quote_status",
        "artisan_id" // artisan working on the project
    ];

    protected $casts = [
        "items" => "array",
        "attachments" => "array"
    ];
    
    
    protected $with = [
        'negotiations',
        // 'serviceRequest',
        'provider'
    ];

    public function negotiations()
    {
        return $this->hasMany(Negotiation::class, 'submitted_quote_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'request_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}

