<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KwikDelivery extends BaseModel
{
    use HasFactory;

    protected $table = 'kwik_deliveries';

    protected $fillable = [
        "estimate", // shipping cost
        "billed", // total charged to user including product fee
        "seller_id", // supplier ID
        "customer_id", // customer purchasing the item
        "order_id",
        "kwik_delivery_id",
        "metadata"
    ];

    protected $casts = [
        "metadata" => "array"
    ];

    /**
     * Relationship: KwikDelivery belongs to an Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: KwikDelivery belongs to a Customer (User)
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Relationship: KwikDelivery belongs to a Seller (User)
     */
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
