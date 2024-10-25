<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function service_requests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'item_id');
    }

    // Override the primary key type
    protected $keyType = 'string';

    // Disable auto-incrementing for the primary key
    public $incrementing = false;

    protected $fillable = [
        '_account_type',
    ];

    protected $hidden = [
        '_account_type',
    ];

    // Automatically generate a UUID when creating a new model instance
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Uuid::uuid4();
            }

            if ($model->_account_type === null) {
                $model->_account_type = active_role();
            }
        });
    }
}
