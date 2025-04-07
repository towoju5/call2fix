<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class BusinessOfficeAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'address',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected static function booted()
    {
        // $subscription = $user->activeSubscription();
        // $allowedOfficeAddresses = $subscription->getRemainingOf('locations');
        // if(count($officeAddresses) > $allowedOfficeAddresses) {
        //     return get_error_response('Feature limit reached', ['error' => 'Feature limit reached'], 403);
        // }
        // Automatically apply a global scope to only return active records
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('is_active', true);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
