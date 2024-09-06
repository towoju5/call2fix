<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Artisans extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'artisan_id',
        'service_provider_id',
    ];
}
