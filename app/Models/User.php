<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;
use DB;
use Emargareten\TwoFactor\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cookie;
use Jijunair\LaravelReferral\Models\Referral;
use Jijunair\LaravelReferral\Traits\Referrable;
use Laravel\Sanctum\HasApiTokens;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable, HasWallet, HasWallets, HasWalletFloat, SoftDeletes, TwoFactorAuthenticatable;
    use Referrable, HasRoles, SoftDeletes, HasPlanSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'device_id',
        'account_type',
        'phone',
        'is_social',
        'password_reset_code',
        'password_reset_code_expires_at',
        'profile_picture',
        'is_banned'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'is_banned',
        'password',
        'remember_token',
        'updated_at',
        'created_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(TransactionRecords::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function my_wallets()
    {
        return $this->wallets()->get()->makeHidden(['id', 'holder_type', 'holder_id', 'uuid', 'description', 'created_at', 'updated_at', 'deleted_at']);
    }

    public function artisan()
    {
        return $this->hasMany(Artisans::class, 'artisans_id', 'id');
    }

    public function bank_account()
    {
        return $this->hasOne(BankAccounts::class, 'user_id');
    }

    public function referrals()
    {
        return $this->referrals;
    }

    public function my_referral()
    {
        return $this->referralAccount;
    }

    public function my_referral_code()
    {
        return $this->getReferralCode();
    }

    public function business_info()
    {
        return $this->belongsTo(BusinessInfo::class, 'id', 'user_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_user', 'user_id', 'chat_id')
            ->withTimestamps();
    }


    // Relationship: User belongs to many departments
    public function departments()
    {
        return $this->belongsToMany(Department::class);
    }

    // Relationship: User has a current department
    public function currentDepartment()
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    // Method to create a new department
    public function createDepartment(string $name): Department
    {
        return DB::transaction(function () use ($name) {
            $department = Department::create(['name' => $name]);
            $this->departments()->attach($department->id);

            // Optionally set the created department as the current department
            $this->current_department_id = $department->id;
            $this->save();

            return $department;
        });
    }

    /**
     * Set the currently active department for the user.
     *
     * @param Department $department
     * @return void
     */
    public function setActiveDepartment(Department $department): void
    {
        $this->current_department_id = $department->id;
        $this->save();
    }

    /**
     * Clear the active department, making the user operate without a department.
     *
     * @return void
     */
    public function clearActiveDepartment(): void
    {
        $this->current_department_id = null;
        $this->save();
    }
}
