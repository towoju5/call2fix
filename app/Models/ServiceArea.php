<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceArea extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_area_title',
    ];

    // Override the primary key type
    protected $keyType = 'string';

    // Disable auto-incrementing for the primary key
    public $incrementing = false;

    protected $hidden = [
        '_account_type',
    ];

    // Automatically generate a UUID when creating a new model instance
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) generate_uuid();
            }

            if ($model->_account_type === null) {
                $model->_account_type = active_role();
            }
        });
    }
}
