<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'item_id',
        'seller_id',
        'status',
        'quantity',
        'total_price',
        'estimated_delivery',
        'is_leasable',
        'order_id',
        'kwik_order_id',
        'duration_type',
        'lease_duration',
        'lease_rate',
        'lease_notes',
        'product_id',
        'delivery_address',
        'delivery_longitude',
        'delivery_latitude',
        'additional_info',
        'shipping_fee'
    ];

    protected $hidden = [
        'kwik_order_id',
    ];

    public function leasableProducts()
    {
        return $this->where('is_leasable', true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(User::class);
    }

    public function seller()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->order_id) {
                $model->order_id = (string)(self::count() + 1);
            }
        });
    }
}
