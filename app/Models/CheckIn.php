<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckIn extends BaseModel
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'check_ins';
    
    protected $fillable = [
        'user_id',
        'check_in_time',
        'check_out_time',
    ];
}