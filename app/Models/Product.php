<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends BaseModel
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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_leasable' => 'boolean',
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
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
}
