<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;


    public function service()
    {
        return $this->belongsTo(Service::class);
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

    /**
     * Relationship: A category belongs to a service area.
     */
    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class, 'parent_category', 'id');
    }

    /**
     * Relationship: A category has many services.
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'category_id', 'id');
    }


    /**
     * Scope to get top categories by order counts
     */
    public function scopeTopOrders($query, $limit = null, $direction = 'desc')
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $query = $query
            ->leftJoin('orders', 'categories.id', '=', 'orders.product_category_id')
            ->select('categories.*', \DB::raw('COUNT(orders.id) as order_count'))
            ->groupBy('categories.id')
            ->orderBy('order_count', $direction);

        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }

    // Scope to order categories by the number of orders
    public function scopeWithOrderCounts($query, $direction = 'desc')
    {
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        return $query
            ->leftJoin('orders', 'categories.id', '=', 'orders.product_category_id')
            ->select('categories.*', \DB::raw('COUNT(orders.id) as order_count'))
            ->groupBy('categories.id')
            ->orderBy('order_count', $direction);
    }
}
