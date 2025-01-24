<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artisans extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "artisan_id",
        "service_provider_id",
        "first_name",
        "last_name",
        "email",
        "phone",
        "trade",
        "location",
        "id_type",
        "id_image",
        "trade_certificate",
        "payment_plan",
        "payment_amount",
        "bank_code",
        "account_number",
        "account_name",
        "artisan_category"
    ];

    protected $casts = [
        "location" => "array",
        "artisan_category" => "array"
    ];

    // This assumes each artisan record has a single user relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'artisan_id');
    }

    // If each artisan can have multiple users, this relationship is fine; otherwise, it may need adjustment
    public function users()
    {
        return $this->hasMany(User::class, 'artisan_id');
    }
}
