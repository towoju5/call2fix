<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasWallets;

class SubAccounts extends Model
{
    use HasFactory, SoftDeletes, HasWallets; // Use Bavix HasWallet for wallet management

    protected $table = "users"; // assuming users table holds account data


    // Define the relationship to the main account
    public function mainAccount()
    {
        return $this->belongsTo(User::class, 'parent_account_id');
    }

    // Define the relationship to sub-accounts
    public function subAccounts()
    {
        return $this->hasMany(User::class, 'parent_account_id');
    }

    // Fetch sub-accounts for the current user based on role
    public function fetchSubAccountsByRole($role)
    {
        return User::where("main_account_role", $role)
            ->where('parent_account_id', Auth::id())
            ->get();
    }

    // Fetch specific sub-account by ID and role
    public function fetchAccount($role, $subAccountId)
    {
        return User::where("main_account_role", $role)
            ->where('parent_account_id', Auth::id())
            ->where('id', $subAccountId)
            ->first();
    }

    // Check if the user can have a wallet based on account role
    public function canHaveWallet()
    {
        $user = Auth::user();

        // If the user is private or has private account role, no wallet allowed
        if (in_array($user->main_account_role, ['private', 'private_account'])) {
            return false;
        }

        // Check if it's a family friend account or department
        if ((bool) $user->is_family_friend_account) {
            return false;
        }

        // Corporate accounts and their sub-accounts can hold wallets
        if ($user->main_account_role === 'corporate' || $user->sub_account_type == 'department') {
            return true;
        }

        return true;
    }

    // Check if the sub-account is of type 'department'
    public function isDepartmentAccount($subAccount)
    {
        return $subAccount->sub_account_type === 'department';
    }

    // Handle login response, return role and Sanctum token
    public static function loginResponse(User $user)
    {
        // Generate Sanctum auth token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Fetch user role and sub-account role (if applicable)
        $role = $user->main_account_role;
        $subAccountRole = $user->subAccounts()->first()?->main_account_role;

        return [
            'user' => $user,
            'role' => $role,
            'account' => $subAccountRole,
            'token' => $token
        ];
    }

    // Handle wallet funding for corporate accounts
    public function fundSubAccountWallet(User $subAccount, $amount)
    {
        $mainAccount = $this->mainAccount;

        // Only corporate accounts can fund sub-accounts
        if ($this->main_account_role !== 'corporate') {
            return false;
        }

        // Fund sub-account using Bavix Wallet
        if ($subAccount->canHaveWallet()) {
            $subAccount->deposit($amount);
            return true;
        }

        return false;
    }

    public function getSubAccountBalance(User $subAccount)
    {
        return $subAccount->wallet;
    }

    // Charge all transactions to the main account for private accounts
    public function chargeMainAccountForPrivateSubAccount(User $subAccount, $transactionAmount)
    {
        $mainAccount = $this->mainAccount;

        if ($mainAccount && in_array($mainAccount->main_account_role, ['private', 'private_account'])) {
            // Deduct the transaction amount from the main account using Bavix Wallet
            if ($mainAccount->balance >= $transactionAmount) {
                $mainAccount->withdraw($transactionAmount);
                return true;
            }
        }

        return false;
    }
}
