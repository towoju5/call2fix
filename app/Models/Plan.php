<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'price', 'service_category_limit', 'artisan_limit', 'product_category_limit', 'product_limit'];
}
