<?php

namespace Modules\Artisan\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Artisan\Database\Factories\RequestQuotesFactory;

class RequestQuotes extends BaseModel
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "user_id",
        "request_id",
        "workmanship",
        "items",
        "sla_duration",
        "sla_start_date",
        "attachments",
        "summary_note",
        "administrative_fee",
        "total_charges",
        "service_vat",
    ];

    protected $casts = [
        'attachments' => 'array',
        'items' => 'array'
    ];
}
