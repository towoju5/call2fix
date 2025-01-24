<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $table = 'e_referrals';

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'task_id',
    ];

    /**
     * Referrer (User who shared the referral code)
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Referred User (User who used the referral code)
     */
    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    /**
     * Associated Task
     */
    public function task()
    {
        return $this->belongsTo(Tasks::class);
    }

    /**
     * Scope for filtering referrals by task
     */
    public function scopeByTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    /**
     * Scope for filtering referrals by referrer
     */
    public function scopeByReferrer($query, $referrerId)
    {
        return $query->where('referrer_id', $referrerId);
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
