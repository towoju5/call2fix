<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "category_id",
        "service_name",
        "service_slug",
        "metadata",
    ];

    // Override the primary key type
    protected $keyType = 'string';

    // Disable auto-incrementing for the primary key
    public $incrementing = false;

    protected $casts = [
        "metadata" => "json"
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'item_id');
    }



    /**
     * Scope to get top services by order counts
     */
    public function scopeTopServices($query, $categoryId = null, $limit = null, $direction = 'desc')
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $query = $query
            ->leftJoin('orders', 'services.id', '=', 'orders.product_id')
            ->leftJoin('categories', 'services.category_id', '=', 'categories.id')
            ->select(
                'services.id as service_id',
                'services.service_name as service_name',
                'categories.id as category_id',
                'categories.category_name as category_name',
                \DB::raw('COUNT(orders.id) as order_count')
            )
            ->groupBy('services.id', 'categories.id')
            ->orderBy('order_count', $direction);

        if ($categoryId) {
            $query->where('categories.id', $categoryId);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }
}
