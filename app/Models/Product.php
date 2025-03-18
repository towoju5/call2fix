<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'seller_id',
        'stock',
        'sku',
        'weight',
        'dimensions',
        'is_active',
        'is_leasable',
        'product_currency',
        'product_location',
        'product_longitude',
        'product_latitude',
        'product_image',
        "rentable_price"
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_leasable' => 'boolean',
        'product_image' => 'array',
        'rentable_price' => 'array'
    ];

    protected $hidden = [
        // "created_at",
        // "updated_at",
        "deleted_at"
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function leasable()
    {
        $this->where('is_leasable', true)->where('seller_id', auth()->id());
    }
    

    // Override the primary key type
    protected $keyType = 'string';

    // Disable auto-incrementing for the primary key
    public $incrementing = false;

    // Automatically generate a UUID when creating a new model instance
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
