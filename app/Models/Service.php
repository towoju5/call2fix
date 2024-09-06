<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "category_id",
        "service_name",
        "service_slug",
        "metadata",
    ];

    protected $casts = [
        "metadata" => "json"
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
