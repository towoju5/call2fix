<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'e_subscriptions';

    protected $fillable = [
        'user_id',
        'task_id',
        'referral_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class);
    }

    // Override the primary key type
    protected $keyType = 'string';

    // Disable auto-incrementing for the primary key
    public $incrementing = false;

    protected $hidden = [
        '_account_type',
    ];

    // Automatically generate a UUID when creating a new model instance
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = generate_uuid();
            }
        });
    }

}

