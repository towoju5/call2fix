<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class BaseModel extends Model
{
    use HasFactory;

    // Override the primary key type
    protected $keyType = 'string';

    // Disable auto-incrementing for the primary key
    public $incrementing = false;

    protected $fillable = [
        '_account_type',
    ]; 

    protected $hidden = [
        '_account_type',
    ];

    // Automatically generate a UUID when creating a new model instance
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Uuid::uuid4();
            }

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
        // if (auth()->guard()->check() && (!auth('admin')->check())) {
        //     return $query->where('_account_type', active_role());
        // }
        return $query;
    }
}
