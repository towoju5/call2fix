<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_title',
        'activity_status',
        'activity_amount',
        'activity_extra',
        '_account_type',
    ];

    protected $casts = [
        'activity_extra' => 'array',
    ];

    protected $hidden = [
        // 'activity_extra',
        'updated_at',
        'deleted_at'
    ];
    
    

   // Automatically generate a UUID when creating a new model instance
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->_account_type === null) {
                $model->_account_type = active_role();
            }
        });
    }

    // Override the newQuery method to automatically apply the _account_type filter
    public function newQuery($excludeDeleted = true)
    {
        // Call the parent method to get the base query
        $query = parent::newQuery($excludeDeleted);

        // Automatically add the where clause for _account_type
        if (auth()->guard()->check()) {
            return $query->where('_account_type', active_role());
        }
    }
}