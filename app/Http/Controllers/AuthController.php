<?php

namespace App\Http\Controllers;

use App\Models\BusinessInfo;
use App\Notifications\PasswordResetComplete;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Jijunair\LaravelReferral\Models\Referral;
use Towoju5\Wallet\Models\Wallet;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Log;


class AuthController extends Controller
{
    public function __construct()
    {
        if (!Schema::hasColumn('users', 'country_dialing_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('country_dialing_code')->nullable();
            });
        }
    }

    public function register(Request $request)
    {
        try {
            switch ($request->account_type) {
                case 'private_account':
                    $accountType = "private_accounts";
                    break;

                case 'co-operate_account':
                    $accountType = "co-operate_accounts";
                    break;
                
                case 'affiliate':
                    $accountType = "affiliates";
                    break;
                
                default:
                    $accountType = $request->account_type;
                    break;
            }

            if (!$request->username) {
                $request->merge([
                    'username' => explode('@', $request->email ?? $request->phone)[0] . rand(1, 99),
                    'account_type' => $accountType,
                ]);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required_without:phone|string|email|max:255|unique:users',
                'phone' => 'required_without:email|string|regex:/^\+[1-9]\d{1,14}$/|max:20|unique:users',
                'account_type' => 'required|string|in:co-operate_accounts,private_accounts,affiliates',
                'device_id' => 'required|string|max:255',
                'password' => 'required|string|min:8',
                'username' => 'required|string|max:255|unique:users',
                'profile_picture' => 'nullable|string',
                // 'referred_by' => 'sometimes',
                'country_code' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::debug("Validation Error", ['error' => $validator->errors()]);
                return get_error_response("Validation error", $validator->errors());
            }

            $userData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'password' => Hash::make($request->password),
                'username' => $request->username,
                'profile_picture' => $request->profile_picture,
                'is_social' => false,
                '_account_type' => $request->account_type,
                'device_id' => $request->device_id,
                'current_role' => $request->account_type,
                'main_account_role' => $request->account_type,
                'country_dialing_code' => str_replace("+", "", $request->country_code),
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

                // Implement referral system if referred_by exists
                if ($request->has('referred_by')) {
                    if ($request->has('referred_by')) {
                        $this->process_referral($user, $request->referred_by, $request->account_type);
                    }
                }

                $user->getWallet('ngn');
                $user->getWallet('bonus');
                $user->assignRole($request->account_type);

                if (in_array($request->account_type, ['suppliers', 'providers', 'corporate_account', 'co-operate_accounts', 'private_accounts', 'affiliates'])) {
                    $businessInfo = BusinessInfo::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            '_account_type' => $request->account_type,
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );

                    if (!$businessInfo) {
                        return get_error_response('Failed to create business info');
                    }
                }

                return $user;
            });

            return get_success_response(
                array_merge($user->toArray(), ['wallets' => $user->my_wallets()]),
                'User registered successfully'
            );
        } catch (\Exception $e) {
            return get_error_response($e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => json_encode($e->getTrace()) // Convert trace to a string
            ]);
        }
    }

    public function registerBis(Request $request)
    {
        try {
            switch ($request->account_type) {
                case 'private_account':
                    $accountType = "private_accounts";
                    break;

                case 'co-operate_account':
                    $accountType = "co-operate_accounts";
                    break;
                
                case 'affiliate':
                    $accountType = "affiliates";
                    break;
                
                default:
                    $accountType = $request->account_type;
                    break;
            }

            if (!$request->username) {
                $request->merge([
                    'username' => explode('@', $request->email ?? $request->phone)[0] . rand(1, 99),
                    'account_type' => $accountType,
                ]);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required_without:phone|string|email|max:255|unique:users',
                'phone' => 'required_without:email|string|regex:/^\+[1-9]\d{1,14}$/|max:20|unique:users',
                'account_type' => 'required|string|in:providers,suppliers',
                'device_id' => 'required|string|max:255',
                'password' => 'required|string|min:8',
                'username' => 'required|string|max:255|unique:users',
                'profile_picture' => 'nullable|string',
                // 'referred_by' => 'sometimes',
                "businessName" => "required|string",
                "cacNumber" => "required|string",
                "officeAddress" => "required|string",
                "businessCategory" => "required|string",
                "businessDescription" => "required|string",
                "businessIdType" => "required|string",
                "businessIdNumber" => "required|string",
                "businessIdImage" => "required|string",
                "businessBankInfo" => "required|string",
                'country_code' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::debug("Validation Error", ['error' => $validator->errors()]);
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
                'country_dialing_code' => str_replace("+", "", $request->country_code),
            ];

            if ($request->has('email')) {
                $userData['email'] = $request->email;
            }

            if ($request->has('phone')) {
                $userData['phone'] = str_replace(" ", "", $request->phone);
            }

            // Start a database transaction to ensure atomicity
            $user = DB::transaction(function () use ($userData, $request) {
                // Create the user
                $user = User::create($userData);

                if (!$user) {
                    return get_error_response('Failed to create user');
                }

                // Create or update the user's business info
                $business = $user->business_info()->updateOrCreate([
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

                // Check if business creation failed
                if (!$business) {
                    return get_error_response('Failed to create or update business info');
                }

                // Implement referral system if referred_by exists
                if ($request->has('referred_by')) {
                    if ($request->has('referred_by')) {
                        $this->process_referral($user, $request->referred_by, $request->account_type);
                    }
                }

                // Generate wallet balance for NGN and bonus
                $user->getWallet('ngn');
                $user->getWallet('bonus');

                // Assign role to the user based on account type
                $user->assignRole($request->account_type);

                // If the account is a 'supplier', 'provider', or 'corporate_account', create business info
                if (in_array($request->account_type, ['providers', 'suppliers'])) {
                    $businessInfo = BusinessInfo::create([
                        'user_id' => $user->id,
                        'account_type' => $request->account_type,
                    ]);

                    if (!$businessInfo) {
                        return get_error_response('Failed to create business info for the account');
                    }
                }

                return $user;
            });

            // Return the success response with user data and wallets
            return get_success_response(array_merge($user->toArray(), ['wallets' => $user->my_wallets()]), 'User registered successfully');
        } catch (\Exception $e) {
            // If any exception occurs, roll back the transaction and return error response
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
            \Log::info('An error occurred during authentication', ['error' => $e->getMessage()]);
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
            $user = User::with([
                "properties",
                "orders",
                "transactions",
                "products",
                "bankAccount",
                "business_info",
                "business_office_address",
                "roles",
                "task_referrals"
            ])->whereId(auth()->id())->first();

            if (!$user) {
                return get_error_response('User not found', ['message' => 'User not found']);
            }

            // Add additional attributes to the response without modifying the model directly
            $response = $user->toArray(); // Convert model and relations to an array
            $response['current_plan'] = $user->activeSubscription() ?? get_free_plan();
            $response['ref_code'] = $user->getReferralCode();
            $response['wallets'] = Wallet::where('user_id', $user->id)->where('role', active_role())->get();
            $response['referrer'] = $user->referralAccount?->referrer;
            $response['business_info']['officeAddress'] = $user->business_office_address; // Fetch related addresses

            return get_success_response($response);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!Schema::hasColumn('users', 'is_notification_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_notification_enabled')->default(true);
            });
        }
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

    public function socialLogin(Request $request, $social = null)
    {
        try {
            // Validate the request
            $validate = Validator::make($request->all(), [
                'email' => 'required|email',
                'access_token' => 'required|string',
                'provider' => 'required|string|in:google,apple',
                'device_id' => 'required|string|max:255',
                // 'referred_by' => 'sometimes|string|max:255',
            ]);

            if(!User::whereEmail($request->email)->exists()) {
                $notRegistered = Validator::make($request->all(), [
                    'account_type' => 'required|string|in:co-operate_accounts,private_accounts,affiliates,providers,suppliers',
                    'country_code' => 'required|string|max:255',
                ]);
                if ($notRegistered->fails()) {
                    return get_error_response(['error' => $notRegistered->errors()->toArray()]);
                }
            }

            if (in_array($request->account_type, ["providers", "suppliers"])) {
                $businessValidation = Validator::make($request->all(), [
                    "businessName" => "required|string",
                    "cacNumber" => "required|string",
                    "officeAddress" => "required|string",
                    "businessCategory" => "required|string",
                    "businessDescription" => "required|string",
                    "businessIdType" => "required|string",
                    "businessIdNumber" => "required|string",
                    "businessIdImage" => "required|string",
                    "businessBankInfo" => "required|string",
                ]);
                if ($businessValidation->fails()) {
                    return get_error_response(['error' => $businessValidation->errors()->toArray()]);
                }
            }

            if ($validate->fails()) {
                return get_error_response(['error' => $validate->errors()->toArray()]);
            }

            $is_registered = false;
            $socialData = null;

            // Handle Google or Apple validation
            if ($request->provider === 'google') {
                $social_url = "https://www.googleapis.com/oauth2/v1/userinfo?access_token={$request->access_token}";
                $response = Http::get($social_url);
                if ($response->failed()) {
                    return get_error_response('Invalid Google access token', ['error' => 'Invalid access token']);
                }
                $socialData = $response->json();
            } elseif ($request->provider === 'apple') {
                // Apple token validation (assumes `AppleSignInValidator` is available)
                // $appleValidation = AppleSignInValidator::validateToken($request->access_token);
                // if (!$appleValidation['success']) {
                //     return get_error_response('Invalid Apple access token', ['error' => $appleValidation['error']]);
                // }
                // $socialData = [
                //     'email' => $request->email,
                //     'first_name' => $appleValidation['first_name'] ?? 'Apple',
                //     'last_name' => $appleValidation['last_name'] ?? 'User',
                // ];
            }

            // Proceed with user creation or update
            if (isset($socialData['email'])) {
                // Prepare update data with only fields that have values
                $updateData = [
                    'is_social' => true,
                ];
            
                // Add fields only if they exist in the input
                if (isset($socialData['picture'])) {
                    $updateData['profile_picture'] = $socialData['picture'];
                }
            
                if (isset($socialData['first_name'])) {
                    $updateData['first_name'] = $socialData['first_name'];
                } else {
                    $updateData['first_name'] = 'Unknown';
                }
            
                if (isset($socialData['last_name'])) {
                    $updateData['last_name'] = $socialData['last_name'];
                } else {
                    $updateData['last_name'] = 'User';
                }
            
                if ($request->has('country_code') && !empty($request->country_code)) {
                    $updateData['country_dialing_code'] = str_replace("+", "", $request->country_code);
                }
            
                // Update or create the user
                $user = User::updateOrCreate([
                    'email' => $socialData['email'],
                ], $updateData);
            
                $is_registered = !$user->wasRecentlyCreated;
            
                // Create token
                $token = $user->createToken('auth_token')->plainTextToken;
                $token = explode('|', $token);
            
                // Handle device ID updates
                if ($request->has('device_id') && $user->device_id !== $request->device_id) {
                    $user->tokens()->delete(); // Remove old tokens
                    $user->update(['device_id' => $request->device_id]); // Update device ID
                }
            
                // Implement referral system if referred_by exists
                if ($request->has('referred_by')) {
                    $this->process_referral($user, $request->referred_by, $request->account_type);
                }
            
                return get_success_response([
                    'user' => $user,
                    'token' => $token[1],
                    "is_registered" => $is_registered,
                ], "User logged in successfully");
            }

            return get_error_response('Invalid social login', ['error' => 'Email not found in social login data']);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), [
                'error' => $th->getMessage(),
            ]);
        }
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

            // Validate the input
            $validatedData = $request->validate([
                'businessName' => 'required|string|max:255',
                'cacNumber' => 'required|string|max:50',
                'businessCategory' => 'array|max:5', // Maximum 5 categories
                'businessCategory.*' => 'string|max:100',
                'businessDescription' => 'required|string',
                'businessIdType' => 'required|string|max:50',
                'businessIdNumber' => 'required|string|max:50',
                'businessIdImage' => 'required|url',
                'businessBankInfo.bank_name' => 'required|string|max:255',
                'businessBankInfo.bank_code' => 'required|string|max:10',
                'businessBankInfo.account_number' => 'required|string|max:20',
                'businessBankInfo.account_name' => 'required|string|max:255',
                'officeAddress' => 'array',
                'officeAddress.*.address' => 'required|string|max:255',
                'officeAddress.*.latitude' => 'required|string|max:50',
                'officeAddress.*.longitude' => 'required|string|max:50',
            ]);

            // Step 1: Handle business info update or create
            $businessInfoData = [
                'businessName' => $validatedData['businessName'],
                'cacNumber' => $validatedData['cacNumber'],
                'businessCategory' => $validatedData['businessCategory'],
                'businessDescription' => $validatedData['businessDescription'] ?? null,
                'businessIdType' => $validatedData['businessIdType'] ?? null,
                'businessIdNumber' => $validatedData['businessIdNumber'] ?? null,
                'businessIdImage' => $validatedData['businessIdImage'] ?? null,
                'businessBankInfo' => $validatedData['businessBankInfo'],
            ];

            $businessInfo = $user->business_info()->updateOrCreate(
                ['user_id' => $user->id],
                $businessInfoData
            );

            // Step 2: Handle multiple office addresses
            $officeAddresses = $validatedData['officeAddress'] ?? [];
            foreach ($officeAddresses as $addressData) {
                $user->business_office_address()->updateOrCreate(
                    ['address' => $addressData['address']], // Match by address (or use `id` if available)
                    [
                        'latitude' => $addressData['latitude'],
                        'longitude' => $addressData['longitude'],
                    ]
                );
            }

            return get_success_response([
                'message' => 'Business profile and addresses updated successfully',
                'businessInfo' => $businessInfo,
                'officeAddresses' => $user->business_office_address, // Return all addresses
            ]);
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
            $referrer = Referral::userByReferralCode($ref_code);
            if (!$referrer) {
                return get_error_response('Referrer not found', ['is_valid_referrer' => false]);
            }
            return get_success_response(['is_valid_referrer' => true, 'owner' => $referrer->only(['first_name', 'last_name'])], 'Referrer found');
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    private function process_referral($user, $referred_by, $account_type)
    {
        // Implement referral system if referred_by exists
        $referrer = Referral::userByReferralCode($referred_by);
        
        // Create a referral record for the user
        $referral = $user->createReferralAccount($referred_by);

        // check if device ID exists in the user table.
        if(User::where('device_id', request()->device_id)->exists()){
            return true;
        }

        if($user->hasRole('private_account')) {
            // increment the number of referrer
            // also if the count total of referred so far is in the array [10, 20, 30, 40, 50] then give user referrer bonues
        } else if ($referrer) {    // Check if the referrer exists and and credit for referring user
            $wallet = $user->getWallet('bonus');
            if ($wallet) {
                $wallet->deposit(get_settings_value($account_type.'_referal_commission', 0.1), ["description" => "Referral Bonus"], ["description" => "Referral Bonus"]);
            }
        } else {
            return true;
        }
    }
}
