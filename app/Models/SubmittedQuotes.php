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
class SubmittedQuotes extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "artisan_id",
        "request_id",
        "workmanship",
        "items",
        "sla_duration",
        "sla_start_date",
        "summary_note",
        "administrative_fee",
        "service_vat",
        "attachments",
        "quote_status",
    ];
}

