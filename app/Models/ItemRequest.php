<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemRequest extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'details',
        'status',
        'request_category',
        'request_artisans'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
