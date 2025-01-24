<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtisanCanSubmitQuote extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'artisan_can_submit_quote';

    protected $fillable = [
        'artisan_id',
        'request_id',
        'service_provider_id',
    ];
    
    // Automatically generate a UUID when creating a new model instance
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = generate_uuid();
            }
        });
    }
}
