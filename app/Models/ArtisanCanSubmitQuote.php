<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtisanCanSubmitQuote extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'artisan_can_submit_quote';

    protected $fillable = [
        'artisan_id',
        'request_id',
        'service_provider_id',
    ];
}
