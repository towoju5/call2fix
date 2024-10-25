<?php

namespace App\Http\Controllers;

use App\Models\BusinessInfo;
use App\Notifications\PasswordResetComplete;
use App\Notifications\PasswordResetNotification;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Jijunair\LaravelReferral\Models\Referral;
use Mail;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            if (!isset($request->username)) {
                $request->merge([
                    'username' => explode('@', $request->email ?? $request->phone)[0] . rand(1, 99)
                ]);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required_without:phone|string|email|max:255|unique:users',
                'phone' => 'required_without:email|string|regex:/^\+[1-9]\d{1,14}$/|max:20|unique:users',
                'account_type' => 'required|string|in:providers,co-operate_accounts,private_accounts,affiliates,suppliers',
                'device_id' => 'required|string|max:255',
                'password' => 'required|string|min:8',
                'username' => 'required|string|max:255|unique:users',
                'profile_picture' => 'nullable|string',
                'referred_by' => 'sometimes|string',
            ]);

            if ($validator->fails()) {
                return get_error_response("Validation error", $validator->errors());
            }

            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => Hash::make($request->password),
                'username' => $request->username,
                'profile_picture' => $request->profile_picture,
                'is_social' => false,
                'account_type' => $request->account_type,
                'device_id' => $request->device_id,
                'current_role' => $request->account_type,
                'main_account_role' => $request->account_type,
            ];

            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }

            if ($request->has('phone')) {
                $userData['phone'] = str_replace(" ", "", $request->phone);
            }

            $user = DB::transaction(function () use ($userData, $request) {
                $user = User::create($userData);

                if (!$user) {
                    return get_error_response('Failed to create user');
                }

                // implement referal system
                if ($request->has('referred_by')) {
                    $referral = Referral::create([
                        'user_id' => $user->id,
                        'referred_by' => $request->referred_by,
                    ]);

                    $referrer = Referral::userByReferralCode($request->referred_by);
                    if ($referrer) {
                        $wallet = $user->getWallet('bonus');
                        if ($wallet) {
                            $wallet->deposit(get_settings_value('referal_commission', 0), 'bonus', $request->account_type, ["description" => "Referral Bonus"], ["description" => "Referral Bonus"]);
                        }
                    }
                }

                $user->assignRole($request->account_type);
                // Create business info
                if (in_array($request->account_type, ['suppliers', 'providers', 'corporate_account'])) {
                    $businessInfo = BusinessInfo::create([
                        'user_id' => $user->id,
                        'account_type' => $request->account_type,
                    ]);

                    if (!$businessInfo) {
                        return get_error_response('Failed to create business info');
                    }
                }

                return $user;
            });

            return get_success_response(array_merge($user->toArray(), ['wallets' => $user->my_wallets()]), 'User registered successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return get_error_response($e->getMessage(), ['error' => $e->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string',
                'password' => 'required|string',
                'device_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return get_error_response($validator->errors());
            }

            $loginField = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            $credentials = [
                $loginField => $request->input('login'),
                'password' => $request->input('password'),
            ];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                if ($user->device_id !== $request->device_id) {
                    // Logout all other devices
                    Auth::logoutOtherDevices($request->password);

                    // Remove all old tokens
                    // $user->tokens()->delete();

                    // Update device_id
                    $user->update(['device_id' => $request->device_id]);
                }

                $token = $user->createToken('auth_token')->plainTextToken;
                $token = explode('|', $token);
                return get_success_response(['user' => $user, 'token' => $token[1]], 'Login successful');
            }

            return get_error_response('Invalid credentials', ['message' => 'Invalid credentials']);
        } catch (\Exception $e) {
            return get_error_response('An error occurred during authentication', ['error' => $e->getMessage()]);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return get_success_response(['message' => 'Logged out successfully']);
    }

    public function profile()
    {
        try {
            $user = User::with("properties", "orders", "transactions", "products", "bankAccount", "wallets", "business_info", "roles")->whereId(auth()->id())->first();
            if (!$user) {
                return get_error_response('User not found', ['message' => 'User not found']);
            }
            $user['active_plans'] = $user?->subscribedPlans();
            $user['ref_code'] = $user?->getReferralCode();
            $user['referrer'] = $user?->referralAccount?->referrer;
            return get_success_response($user);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Update all fields except email and phone
        $user->update($request->except(['email', 'phone']));

        return get_success_response(['user' => $user], 'Profile updated successfully');
    }


    public function verifyEmail(Request $request)
    {
        $user = Auth::user();
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
            return get_success_response(['message' => 'Email verified successfully']);
        }
        return get_error_response('Email already verified', ['message' => 'Email already verified']);
    }

    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'provider' => 'required|string',
            'provider_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        if (!isset($request->username)) {
            $request->merge([
                'username' => explode('@', $request->email ?? $request->phone)[0] . rand(1, 99)
            ]);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'email' => $request->email,
                'is_social' => true,
                'email_verified_at' => now(),
                'password' => Hash::make(\Str::random(10)),
                'main_account_role' => 'private_accounts',
                'account_type' => 'private_accounts',
            ]);
        }

        $user->assignRole('private_accounts');
        $token = explode('|', $user->createToken('auth_token')->plainTextToken);
        return get_success_response(['user' => $user, 'token' => $token[1]], 'Login successful');
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        $user = User::where('email', $request->identifier)
            ->orWhere('phone', $request->identifier)
            ->first();

        if (!$user) {
            return get_error_response('User not found', ['message' => 'No user found with the provided email or phone number']);
        }

        $resetCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->password_reset_code = $resetCode;
        $user->password_reset_code_expires_at = now()->addMinutes(10);
        $user->save();

        if (filter_var($request->identifier, FILTER_VALIDATE_EMAIL)) {
            // Send email with reset code
            $type = "Email";
            $user->notify(new PasswordResetNotification($resetCode));
        } else {
            // Send SMS with reset code
            $type = "Phone Number";
            $this->sendSMS($user->phone, "Your password reset code is: " . $resetCode);
        }

        return get_success_response(['message' => "Password reset code sent to your {$type}"]);
    }

    public function validateResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'reset_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->identifier)
                ->orWhere('phone', $request->identifier);
        })
            ->where('password_reset_code', $request->reset_code)
            ->where('password_reset_code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return get_error_response('Invalid or expired reset code', ['error' => 'Invalid or expired reset code']);
        }

        return get_success_response(['message' => 'Reset code validated successfully']);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
            'reset_code' => 'required|string|size:6',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->identifier)
                ->orWhere('phone', $request->identifier);
        })
            ->where('password_reset_code', $request->reset_code)
            ->where('password_reset_code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return get_error_response('Invalid or expired reset code', ['message' => 'Invalid or expired reset code']);
        }

        $user->password = Hash::make($request->password);
        $user->password_reset_code = null;
        $user->password_reset_code_expires_at = null;
        $user->save();

        $user->notify(new PasswordResetComplete());

        return get_success_response(['message' => 'Password has been successfully reset']);
    }

    public function businessProfile(Request $request)
    {
        try {
            $user = Auth::user();
            $user->business_info()->updateOrCreate([
                'user_id' => $user->id,
            ], $request->only([
                            'businessName',
                            'cacNumber',
                            'officeAddress',
                            'businessCategory',
                            'businessDescription',
                            'businessIdType',
                            'businessIdNumber',
                            'businessIdImage',
                            'businessBankInfo'
                        ]));
            return get_success_response(['message' => 'Business profile updated successfully']);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function getUserById($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return get_error_response('User not found', ['error' => 'User not found']);
            }
            return get_success_response(['user' => $user]);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return get_error_response('User not found', ['error' => 'User not found']);
            }
            if ($user->tokens()->delete() && $user->delete()) {
                Auth::logout();
                return get_success_response([], "Account deleted successfully");
            } else {
                return get_error_response('Failed to delete account', ['error' => 'Failed to delete account']);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    private function sendSMS($phone, $message)
    {
        $dojah = new DojaWebhookController();
        $send = $dojah->sendSMS($phone, $message);
        return $send;
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|min:8|different:current_password',
                'new_password_confirmation' => 'required|same:new_password',
            ]);

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return get_error_response('Current password is incorrect', ['error' => 'Current password is incorrect']);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return get_success_response(['message' => 'Password updated successfully']);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function validateReferrer(Request $request)
    {
        try {
            $ref_code = $request->referred_by;
            $referrer = User::where('referral_code', $ref_code)->first();
            if (!$referrer) {
                return get_error_response('Referrer not found', ['is_valid_referrer' => false]);
            }
            return get_success_response(['is_valid_referrer' => true], 'Referrer found');
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }
}
