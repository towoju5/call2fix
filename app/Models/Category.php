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

    protected $hidden = [
        '_account_type',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = generate_uuid();
            }

            if ($model->_account_type === null) {
                $model->_account_type = active_role();
            }
        });
    }
}
