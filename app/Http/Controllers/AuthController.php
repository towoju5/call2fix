<?php

namespace App\Http\Controllers;

use App\Models\BusinessInfo;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Jijunair\LaravelReferral\Models\Referral;


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

                $user->assignRole($request->account_type);

                // create customer wallets
                $mainWallet = $user->createWallet([
                    'name' => 'Naira Wallet',
                    'slug' => 'ngn',
                    'meta' => [
                        'symbol' => '₦',
                        'code' => 'NGN',
                    ],
                ]);

                if (!$mainWallet) {
                    return get_error_response('Failed to create main wallet');
                }

                $bonusWallet = $user->createWallet([
                    'name' => 'Bonus Wallet',
                    'slug' => 'bonus',
                    'meta' => [
                        'symbol' => '₱',
                        'code' => 'bonus',
                    ]
                ]);

                if (!$bonusWallet) {
                    return get_error_response('Failed to create bonus wallet');
                }

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
                    $user->tokens()->delete();

                    // Update device_id
                    $user->update(['device_id' => $request->device_id]);
                }

                $token = $user->createToken('auth_token')->plainTextToken;
                return get_success_response(['user' => $user, 'token' => $token]);
            }

            return get_error_response('Invalid credentials', ['message' => 'Invalid credentials']);
        } catch (\Exception $e) {
            return get_error_response('An error occurred during authentication', ['message' => 'An error occurred during authentication']);
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
            $user = Auth::user()->with("properties", "orders", "transactions", "products", "bank_account", "wallets", "business_info")->first();
            if (!$user) {
                return get_error_response('User not found', ['message' => 'User not found']);
            }
            return get_success_response($user);
        }  catch (\Throwable $th) {
                return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
            }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'phone' => 'string|max:20',
            'email' => 'sometimes|string|max:20',
            'username' => 'string|max:255|unique:users,username,' . $user->id,
            'profile_picture' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        $user->update($request->only(['name', 'phone', 'username', 'profile_picture']));
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'provider' => 'required|string',
            'provider_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'is_social' => true,
                'email_verified_at' => now(),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return get_success_response(['user' => $user, 'token' => $token]);
    }


    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        $user = User::where('email', $request->email)->first();
        $resetCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->password_reset_code = $resetCode;
        $user->password_reset_code_expires_at = now()->addMinutes(10);
        $user->save();

        // Send email with $resetCode to user
        // You'll need to implement the email sending logic here

        return get_success_response(['message' => 'Password reset code sent to your email']);
    }

    public function validateResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'reset_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        $user = User::where('email', $request->email)
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
            'email' => 'required|email|exists:users,email',
            'reset_code' => 'required|string|size:6',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }

        $user = User::where('email', $request->email)
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
}
