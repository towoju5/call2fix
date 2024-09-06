<?php

namespace Modules\Artisan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Artisan\Database\Factories\ArtisanQuotesFactory;

class ArtisanQuotes extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "artisan_id",
        "request_id",
        "service_provider_id",
        "workmanship",
        "items",
        "sla_duration",
        "sla_start_date",
        "attachments",
        "summary_note",
        "administrative_fee",
        "service_vat",
        "request_status"
    ];

    protected $casts = [
        'attachments' => 'array',
        'items' => 'array'
    ];
}
