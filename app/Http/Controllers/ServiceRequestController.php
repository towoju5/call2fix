<?php

namespace App\Http\Controllers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\BusinessOfficeAddress;
use App\Models\Property;
use App\Models\Negotiation;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestModel;
use App\Models\SubmittedQuotes;
use App\Models\User;
use App\Models\ArtisanCanSubmitQuote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Notifications\ReworkIssuedNotification;
use App\Notifications\PaymentStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Towoju5\Wallet\Models\Wallet;
use App\Notifications\ServiceRequest\ServiceRequestSuccessful;

class ServiceRequestController extends Controller
{
    protected $radiusLimitKm;
    public function __construct()
    {
        $this->radiusLimitKm = get_settings_value('max_provider_radius') ?? 30;
    }

    public function index()
    {
        try {
            $serviceRequests = ServiceRequestModel::with('reworkMessages', 'service_provider', 'invited_artisan')->whereUserId(auth()->id())->latest()->get();
            return get_success_response($serviceRequests);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage());
        }
    }

    public function serviceProviderRequest()
    {
        $serviceRequests = ServiceRequestModel::with('reworkMessages', 'service_provider', 'invited_artisan')->whereJsonContains('featured_providers_id', [auth()->id()])->latest()->get();
        return get_success_response($serviceRequests);
    }

    public function store(Request $request)
    {
        $key = 'rate_limit_' . ($request->user()?->id ?: $request->ip()); // Unique key per user or IP
        $maxAttempts = 1; // Limit: 1 requests
        $decayMinutes = 1; // Time frame: 1 minute
    
        if (!RateLimiter::tooManyAttempts($key, $maxAttempts)) {
    
            // Record the attempt
            RateLimiter::hit($key, $decayMinutes * 60); // Convert minutes to seconds
    
            $validate = Validator::make($request->all(), [
                'property_id' => 'required|exists:properties,id',
                'service_category_id' => 'nullable|exists:categories,id',
                'service_id' => 'nullable|exists:services,id',
                'problem_title' => 'required|string|max:255',
                'problem_description' => 'required|string',
                'inspection_time' => 'required',
                'inspection_date' => 'required|date',
                'problem_images' => 'nullable|array|max:5',
                'use_featured_providers' => 'boolean',
                'featured_providers_id' => 'nullable|array',
                'department_id' => 'nullable|exists:departments,id',
            ]);
    
            if ($validate->fails()) {
                return get_error_response("Validation Error", $validate->errors()->toArray());
            }
    
            $validatedData = $validate->validated();
            $validatedData['user_id'] = auth()->id();
            $validatedData['problem_images'] = $request->problem_images;
            $alphameadAccount = get_settings_value('alphamaed_service_account_id', 'a599fd50-15b4-4db5-a839-9e722aea226d');
    
            if ($request->use_featured_providers) {
                $validatedData['featured_providers_id'] = $request->featured_providers_id;
            } else {
                $propertyId = $request->property_id;
                $property = Property::findOrFail($propertyId);
                $radiusLimitMeters = $this->radiusLimitKm * 1000;
    
                $latitude = $property->latitude;
                $longitude = $property->longitude;
    
                // Get nearby providers
                $providers = BusinessOfficeAddress::query()
                    // select(
                    //     'user_id',
                    //     DB::raw("
                    //         ST_Distance_Sphere(
                    //             point(longitude, latitude),
                    //             point(?, ?)
                    //         ) as distance
                    //     ")
                    // )
                    // ->setBindings([$longitude, $latitude])
                    // ->having('distance', '<=', $radiusLimitMeters)
                    // ->orderBy('distance')
                    ->groupBy('user_id')
                    ->take(5)
                    ->pluck('user_id')
                    ->toArray(); // Convert collection to array
    
                // Ensure only provider users
                $distinctProviders = User::whereIn('id', $providers)
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'providers');
                    })
                    ->pluck('id')
                    ->toArray();
    
                // Ensure Alphamead is always included and unique
                if (!in_array($alphameadAccount, $distinctProviders)) {
                    $distinctProviders[] = $alphameadAccount;
                }
    
                if (empty($distinctProviders)) {
                    return get_error_response('No provider found!', ['error' => 'No service provider found nearby']);
                }
    
                $validatedData['featured_providers_id'] = $distinctProviders;
            }
    
            DB::beginTransaction();
            try {
                $serviceRequest = ServiceRequest::create($validatedData);
    
                // charge user for assessment fees
                $user = auth()->user();
    
                // Validate default currency and role
                $currency = get_default_currency($user->id);
                $role = $user->current_role;
    
                // Locate wallet
                $wallet = Wallet::where(['user_id' => $user->id, 'currency' => $currency, 'role' => $role])->first();
                $wallet1 = $user->getWallet($currency ?? 'ngn');
    
                $wallet1->withdrawal(floatval(get_settings_value('assessment_fee', 500) * 100), [
                    "description" => "Assessment fee for Service request order."
                ]);
    
                // Commit transaction if everything is successful
                DB::commit();
                $user->notify(new ServiceRequestSuccessful($serviceRequest));
                return get_success_response($serviceRequest, "Request created successfully", 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return get_error_response("Transaction failed", ['error' => $e->getMessage()]);
            }
        }
    
        return get_error_response("Only one service request can be placed per minute", ['error' => "Only one service request can be placed per minute"]);
    }
    

    public function show(ServiceRequestModel $serviceRequest)
    {
        try {
            return get_success_response($serviceRequest->with('reworkMessages'));
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage());
        }
    }

    public function update(Request $request, ServiceRequestModel $serviceRequest)
    {
        $validatedData = $request->validate([
            'property_id' => 'sometimes|exists:properties,id',
            'service_category_id' => 'nullable|exists:categories,id',
            'service_id' => 'nullable|exists:services,id',
            'problem_title' => 'sometimes|string|max:255',
            'problem_description' => 'sometimes|string',
            'inspection_time' => 'sometimes|date_format:H:i',
            'inspection_date' => 'sometimes|date',
            'problem_images' => 'sometimes|array',
            'use_featured_providers' => 'sometimes|boolean',
            'featured_providers_id' => 'nullable|array',
        ]);

        if (isset($validatedData['problem_images'])) {
            $validatedData['problem_images'] = json_encode($request->problem_images);
        }
        if (isset($validatedData['featured_providers_id'])) {
            $validatedData['featured_providers_id'] = json_encode($request->featured_providers_id);
        }

        $updated = $serviceRequest->update($validatedData);
        if ($updated) {
            return get_success_response($serviceRequest, "Request updated successfully", 200);
        }
    }

    public function destroy(ServiceRequest $serviceRequest)
    {
        try {
            $serviceRequest->delete();
            return get_success_response(null, "Request deleted successfully", 204);
        } catch (\Exception $e) {
            return get_error_response($e->getMessage());
        }
    }

    public function getFeaturedProviders($serviceRequest)
    {
        $serviceRequest = ServiceRequestModel::find($serviceRequest);

        if (!$serviceRequest) {
            return get_error_response("Service Request Not Found", ["error" => "Service Request Not Found"], 404);
        }

        $featuredProviderIds = $serviceRequest->featured_providers_id;

        if (empty($featuredProviderIds)) {
            return get_success_response([], "No featured providers for this Service Request");
        }

        try {
            $featuredProviders = User::whereIn('id', $featuredProviderIds)->get();

            if ($featuredProviders->isEmpty()) {
                return get_success_response([], "No active featured providers found for this Service Request");
            }

            return get_success_response($featuredProviders, "Service Request Featured Providers fetched successfully");
        } catch (\Exception $e) {
            return get_error_response("An error occurred while fetching featured providers", [], 500);
        }
    }


    public function updateStatus(Request $request, $requestId)
    {
        try {
            // Fetch the service request with auth checks
            $authId = auth()->id();
            $serviceRequest = ServiceRequestModel::whereId($requestId)->first();
            //     where(function ($query) use ($authId) {
            //     $query->where('user_id', $authId)
            //           ->orWhere('approved_providers_id', $authId)
            //           ->orWhereIn('featured_providers_id', $authId)
            //           ->orWhere('approved_artisan_id', $authId);
            // })->

            if (!$serviceRequest) {
                return get_error_response("Service request not found", ["error" => "Service request not found"]);
            }

            // Fetch enum values directly from the database schema
            $table = $serviceRequest->getTable();
            $column = 'request_status';
            $validStatuses = $this->getEnumValues($table, $column);

            // Validate the status input
            $validatedData = $request->validate([
                'status' => ['required', function ($attribute, $value, $fail) use ($validStatuses) {
                    // Match input to enum values case-insensitively
                    if (!in_array(strtolower($value), array_map('strtolower', $validStatuses))) {
                        $fail("The selected $attribute is invalid.");
                    }
                }],
            ]);

            // Convert to the correct case as stored in the enum
            $inputStatus = strtolower($validatedData['status']);
            $finalStatus = $validStatuses[array_search($inputStatus, array_map('strtolower', $validStatuses))];

            // Update the status in the database
            $update = $serviceRequest->update(['request_status' => $finalStatus]);

            if ($update && $finalStatus === 'Close Request') {
                // Get provider and artisan details
                $provider = User::find($serviceRequest->approved_providers_id);
                $artisan = User::find($serviceRequest->approved_artisan_id);

                if (!$provider || !$artisan) {
                    return get_error_response("Provider or artisan not found", ["error" => "Provider or artisan missing"]);
                }

                // Calculate artisan earnings
                $providerEarnings = floatval($serviceRequest->amount);
                $artisanPercentage = floatval($artisan->payment_plan); // Fixed or percentage
                $artisanEarning = floatval($artisan->payment_amount);

                if (strtolower($artisan->payment_plan) === 'percentage') {
                    $artisanEarning = ($providerEarnings / 100) * $artisanPercentage;
                }

                // Credit wallets
                $provider->getWallet('ngn')->deposit(
                    $providerEarnings - $artisanEarning,
                    ["description" => $serviceRequest->problem_title]
                );

                $artisan->getWallet('ngn')->deposit(
                    $artisanEarning,
                    ["description" => $serviceRequest->problem_title]
                );

                return get_success_response($serviceRequest, "Service Request status updated successfully");
            }

            return get_success_response($serviceRequest, "Service Request status updated successfully");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return get_error_response("Invalid status provided", $e->errors(), 422);
        } catch (\Exception $e) {
            return get_error_response("An error occurred while updating the status", ['error' => $e->getMessage()], 500);
        }
    }

    public function inspectionRequest(Request $request, $requestId)
    {
        $validate = Validator::make($request->all(), []);

        $request = ServiceRequest::whereId($requestId)->first();
        if (!$request) {
            return get_error_response("Service Request not found");
        }
    }

    public function acceptQuote($quoteId, $requestId)
    {
        try {
            $requests = SubmittedQuotes::whereRequestId($requestId)->get();

            if (!$requests or $requests->isEmpty()) {
                return get_error_response("Quote not found", ["error" => "Quote not found!"], 404);
            }

            $service_request = ServiceRequest::whereId($requestId)->with('user')->first();
            $service_requester = $service_request->user;

            $requests->each(function ($request) use ($quoteId) {
                $request->status = ($request->id == $quoteId) ? "accepted" : "rejected";
                $request->save();
            });

            $acceptedRequest = $requests->firstWhere('id', $quoteId);
            if ($acceptedRequest) {
                $service_request = ServiceRequest::whereId($requestId)->first();
                // retrieve the assigned artisan and add to the service request
                $artisan = ArtisanCanSubmitQuote::where([
                    "request_id" => $requestId,
                    "service_provider_id" => $service_request->approved_providers_id,
                ])->latest()->first();
                if ($service_request) {
                    $service_request->request_status = "Quote Accepted";
                    $service_request->approved_providers_id = $acceptedRequest->provider_id;
                    $service_request->approved_artisan_id = $artisan->artisan_id ?? null;

                    if ($service_request->save() && $service_requester->withdraw($acceptedRequest->total_charges) && $service_request->save()) {
                        // $service_request->save();
                        return get_success_response($acceptedRequest, "Request approved successfully");
                    }
                }
            }

            return get_error_response("Failed to save", ["error" => "Failed to save the accepted quote"], 500);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function rejectQuote($quoteId, $requestId)
    {
        try {
            $request = SubmittedQuotes::whereRequestId($requestId)->whereId($quoteId)->first();
            if (!$request) {
                return get_error_response("Quote not found", ["error" => "Quote not found!"], 404);
            }

            $request->status = "rejected";
            if ($request->save()) {
                return get_success_response($request, "Request rejected successfully");
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function submittedQuotes($requestId)
    {
        try {
            $requests = SubmittedQuotes::whereRequestId($requestId)->get();
            if (!$requests) {
                return get_error_response("No Quotes found for request", ["error" => "No Quotes found for request!"], 404);
            }

            return get_success_response($requests, "Quotes fetched successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function submittedQuote($requestId, $providerId)
    {
        try {
            $request = SubmittedQuotes::whereRequestId($requestId)->whereProviderId($providerId)->first();
            if (!$request) {
                return get_error_response("Quote not found", ["error" => "Quote not found!"], 404);
            }

            return get_success_response($request, "Quote retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function negotiateQuote(Request $request, $requestId, $quoteId)
    {
        try {
            $validate = Validator::make($request->all(), [
                'price' => 'required|numeric|min:0'
            ]);

            if ($validate->fails()) {
                return get_error_response("Validation failed", $validate->errors(), 422);
            }

            $quote = DB::table('submitted_quotes')->where(['request_id' => $requestId, 'id' => $quoteId])->first();
            if (!$quote) {
                return get_error_response("Quote not found", ["error" => "Quote not found!"], 404);
            }

            // $negotiation = [];

            $negotiation = Negotiation::create([
                'submitted_quote_id' => $quoteId,
                'request_id' => $requestId,
                'provider_id' => $quote->provider_id,
                'price' => number_format($request->price, 4, '.', ''),
                'status' => 'pending'
            ]);

            return get_success_response($negotiation, "Price negotiation submitted successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function getNegotiation($requestId)
    {
        try {
            $negotiation = Negotiation::where(['request_id' => $requestId])->get();
            if (!$negotiation) {
                return get_error_response("Negotiation not found", ["error" => "Negotiation not found!"], 404);
            }

            return get_success_response($negotiation, "Price negotiation retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }

    public function acceptNegotiatedPrice(Request $request, $negotiationId)
    {
        DB::beginTransaction();

        try {
            // Validate the status field
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:accepted,rejected'
            ]);

            if ($validator->fails()) {
                return get_error_response("Validation failed", $validator->errors()->toArray(), 422);
            }

            // Fetch the negotiation by ID
            $negotiation = Negotiation::find($negotiationId);

            if (!$negotiation) {
                return get_error_response("Negotiation not found", ["error" => "Negotiation not found"], 404);
            }

            // Update the negotiation status
            $negotiation->status = $request->status;
            $negotiation->save();

            // Retrieve the corresponding quote
            $quote = SubmittedQuotes::where([
                'request_id' => $negotiation->request_id,
                'provider_id' => $negotiation->provider_id,
                'id' => $negotiation->submitted_quote_id
            ])->first();

            if ($quote) {
                // Update quote price
                DB::table('submitted_quotes')->where('id', $quote->id)
                    ->update([
                        'old_price' => $quote->total_charges,
                        'total_charges' => number_format($negotiation->price, 4, '.', '')
                    ]);

                // Retrieve both provider and customer in one query
                $users = User::whereIn('id', [$negotiation->provider_id, $quote->serviceRequest->user->id])->get()->keyBy('id');
                $provider = $users->get($negotiation->provider_id);
                $customer = $users->get($quote->serviceRequest->user->id);

                if (!$provider || !$customer) {
                    DB::rollBack();
                    return get_error_response("Provider or Customer not found", ["error" => "User not found"], 404);
                }

                // Process the customer's wallet
                // $customer_wallet = $customer->getWallet('ngn');
                // if ($customer_wallet) {
                //     try {
                //         $customer_wallet->withdrawal($negotiation->price, ["description" => "Payment for service request."]);
                //     } catch (\Throwable $th) {
                //         DB::rollBack();
                //         return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
                //     }
                // }

                // Retrieve the service request
                $serviceRequest = ServiceRequestModel::findOrFail($negotiation->request_id);

                if ($serviceRequest->request_status == "Payment Confirmed") {
                    DB::rollBack();
                    return get_error_response("Payment already processed", ["error" => "Payment already processed"], 409);
                }

                // Update request status to "Payment Confirmed"
                $serviceRequest->request_status = "Payment Confirmed";
                $customer->notify(new PaymentStatusUpdated('Payment Confirmed', $negotiation));

                if (!$serviceRequest->save()) {
                    DB::rollBack();
                    return get_error_response("Unable to complete request, please contact support", ["error" => "Unable to complete request"], 400);
                }
            }

            DB::commit();

            return get_success_response($negotiation, "Negotiated price accepted successfully");
        } catch (\Throwable $th) {
            DB::rollBack();
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()]);
        }
    }


    /**
     * Issue a rework for a service request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function issueRework(Request $request, $requestId)
    {
        try {
            $validate = Validator::make($request->all(), [
                "media_files" => "required|array",
                "media_files.*" => "url",
                "message" => "required|string"
            ]);

            if ($validate->fails()) {
                return get_error_response("Validation failed", $validate->errors(), 422);
            }

            $serviceRequest = ServiceRequest::findOrFail($requestId);

            $serviceRequest->request_status = "Rework issued";

            if ($serviceRequest->save()) {
                // get service provider and notify him
                $provider = User::find($serviceRequest->approved_providers_id);
                if ($provider) {
                    $provider->notify(new ReworkIssuedNotification($serviceRequest));
                }

                $serviceRequest->reworkMessages()->create([
                    'message' => $request->message,
                    'images' => $request->media_files,
                    'user_id' => auth()->id()
                ]);

                return get_success_response($serviceRequest, "Rework issued successfully");
            } else {
                return get_error_response("Failed to save rework status", [], 500);
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ["error" => $th->getMessage()], 500);
        }
    }

    public function serviceProviders()
    {
        try {
            $providers = User::role('providers')->get();
            return get_success_response($providers, 'Service Providers fetched successfully');
        } catch (\Throwable $th) {
            return get_error_response('An error occurred while fetching service providers', ['error' => $th->getMessage()], 500);
        }
    }

    public function markRequestAsCompleted(Request $request)
    {
        //
    }

    /**
     * Retrieve ENUM values for a column from the database schema.
     */
    private function getEnumValues($table, $column)
    {
        $query = "SHOW COLUMNS FROM `$table` WHERE Field = ?";
        $result = \DB::select($query, [$column]);

        // Check if the result is empty
        if (empty($result)) {
            throw new \Exception("Column '$column' does not exist in table '$table' or is not an ENUM type.");
        }

        $type = $result[0]->Type; // Safely access the first result

        preg_match('/enum\((.*)\)/', $type, $matches);

        if (!isset($matches[1])) {
            throw new \Exception("The column '$column' is not of ENUM type.");
        }

        $enumValues = array_map(function ($value) {
            return trim($value, "'");
        }, explode(',', $matches[1]));

        return $enumValues;
    }

    public function makePayment($requestId, $walletType = 'ngn')
    {
        try {
            // retrieved the service request firstly
            $serviceRequest = ServiceRequestModel::whereId($requestId)->first();
            if (!$serviceRequest) {
                return get_error_response("Service request not found", ["error" => "Service request not found"], 404);
            }
            // check if the service request is already paid for
            if ($serviceRequest->request_status == "Payment Confirmed") {
                return get_error_response("Service request already paid for", ["error" => "Service request already paid for"], 400);
            }

            // get the total_cost and get the customer defaults wallets and debit the customer
            $total_cost = $serviceRequest->total_cost;

            // get object of the customer that placed the order
            $customer = $serviceRequest->user;

            // get the customer's wallet
            $wallet = $customer->getWallet($walletType);
            $transaction[] = $wallet->withdrawal($total_cost,  ['description' => "Service request payment - {$serviceRequest->id}", "narration" => $request->narration ?? null]);

            if ($transaction && $wallet) {
                // return success data with the transaction and service request data
                return get_success_response([
                    'transaction' => $transaction,
                    'service_request' => $serviceRequest
                ], 'Payment successful');
            }
        } catch (\Throwable $th) {
            return get_error_response(['error' => $th->getMessage()]);
        }
    }
}
