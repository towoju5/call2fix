<?php

namespace App\Http\Controllers;

use App\Notifications\CustomNotification;
use Log;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\BusinessOfficeAddress;
use App\Models\Property;
use App\Models\Negotiation;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestModel;
use App\Models\SubmittedQuotes;
use App\Models\User;
use App\Models\Artisans;
use App\Models\PaymentApportionment;
use App\Models\ArtisanCanSubmitQuote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Notifications\ReworkIssuedNotification;
use App\Notifications\PaymentStatusUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Towoju5\Wallet\Models\Wallet;
use App\Notifications\NewRequestNotification;
use App\Notifications\ServiceRequest\ServiceRequestSuccessful;
use App\Notifications\ServiceRequest\ServiceRequestNegotiated;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ServiceRequestController extends Controller
{
    protected $radiusLimitKm;
    public function __construct()
    {
        if (!Schema::hasColumn('negotiations', 'percentage_decrease')) {
            Schema::table('negotiations', function (Blueprint $table) {
                $table->string('percentage_decrease')->nullable();
                $table->string('new_item_total')->nullable();
                $table->string('new_workmanship')->nullable();
            });
        }
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
                "alternative_date" => "sometimes",
                "alternative_time" => "sometimes"
            ]);

            Log::debug("Incoming request payload is: ", ['payload' => $request->all()]);

            if ($validate->fails()) {
                return get_error_response("Validation Error", $validate->errors()->toArray());
            }

            $validatedData = $validate->validated();
            $validatedData['user_id'] = auth()->id();
            $validatedData['request_status'] = "Pending";
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
                    ->select(
                        'user_id',
                        DB::raw("
                            ST_Distance_Sphere(
                                point(longitude, latitude),
                                point(?, ?)
                            ) as distance
                        ")
                    )
                    ->setBindings([$longitude, $latitude])
                    ->having('distance', '<=', $radiusLimitMeters)
                    ->orderBy('distance')
                    ->groupBy('user_id')
                    ->where('user_id', '!=', auth()->id())
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

                $assesmentFee = get_settings_value('assessment_fee', 500);
                $isPaySuccessfull = $wallet1->withdrawal($assesmentFee * 100, [
                    "description" => "Assessment fee for Service request order."
                ]);

                if($isPaySuccessfull) {
                    $serviceRequest->update([
                        "request_status" => "Pending",
                        "assesment_fee_paid" => true
                    ]);
                }

                // Get the list of artisans to notify
                $artisans = User::whereIn('id', $serviceRequest->featured_providers_id)
                    ->whereHas('roles', function ($query) {
                        $query->where('name', 'providers');
                    })
                    ->get();

                // Notify each artisan
                foreach ($artisans as $artisan) {
                    $artisan->notify(new NewRequestNotification($serviceRequest));
                }

                // Commit transaction if everything is successful
                DB::commit();
                return get_success_response($serviceRequest, "Request created successfully", 201);
            } catch (\Exception $e) {
                Log::debug("Transaction failed: ", ['error' => $e->getMessage(), 'trace' => $e->getTrace()]);
                DB::rollBack();
                return get_error_response("Transaction failed", ['error' => $e->getMessage()]);
            }
        }

        return get_error_response("Only one service request can be placed per minute", ['error' => "Only one service request can be placed per minute"]);
    }
    

    public function show($serviceRequest)
    {
        try {
            $serviceRequest = ServiceRequestModel::with('reworkMessages', 'service_provider', 'invited_artisan')->whereId($serviceRequest)->first();
            return get_success_response($serviceRequest);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
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
            "alternative_date" => "sometimes",
            "alternative_time" => "sometimes"
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
            $authId = auth()->id();
            $serviceRequest = ServiceRequestModel::find($requestId);

            if (!$serviceRequest) {
                return get_error_response("Service request not found", ["error" => "Service request not found"]);
            }

            $finalStatus = $request->status;

            if ($finalStatus !== 'Closed') {
                $serviceRequest->update(['request_status' => $finalStatus]);
                return get_success_response($serviceRequest, "Service Request status updated successfully");
            }

            // Begin full transaction for status = Closed
            DB::beginTransaction();

            $serviceRequest->update(['request_status' => $finalStatus]);

            $apportionment = $this->aportionment($serviceRequest);
            if($apportionment['error']) {
                return get_error_response($apportionment['error'], ['error' => $apportionment['error']]);
            }

            PaymentApportionment::updateOrCreate([
                'service_request_id' => $serviceRequest->id,
            ], [
                'subtotal' => $apportionment['subtotal'],
                'service_provider_earnings' => $apportionment['service_provider_earnings'],
                'call2fix_management_fee' => $apportionment['call2fix_management_fee'],
                'call2fix_earnings' => $apportionment['call2fix_earnings'],
                'warranty_retention' => $apportionment['warranty_retention'],
                'artisan_earnings' => $apportionment['artisan_earnings'],
            ]);

            // Credit Provider
            $provider = User::find($serviceRequest->approved_providers_id);
            if (!$provider) {
                DB::rollBack();
                return get_error_response('Provider not found');
            }

            $providerDeposit = $provider->getWallet('ngn')->deposit(
                $apportionment['service_provider_earnings'] * 100,
                ["description" => $serviceRequest->problem_title]
            );

            if (!$providerDeposit) {
                DB::rollBack();
                return get_error_response('Failed to deposit into provider wallet');
            }

            $provider->notify(new CustomNotification(
                'Wallet Credited',
                "Your wallet has been credited with ₦" . number_format($apportionment['service_provider_earnings'], 2)
            ));

            // Credit Artisan (optional)
            if ($serviceRequest->approved_artisan_id) {
                $artisan = User::find($serviceRequest->approved_artisan_id);
                if (!$artisan) {
                    DB::rollBack();
                    return get_error_response('Artisan not found');
                }

                $artisanDeposit = $artisan->getWallet('ngn')->deposit(
                    $apportionment['artisan_earnings'] * 100,
                    ["description" => $serviceRequest->problem_title]
                );

                if (!$artisanDeposit) {
                    DB::rollBack();
                    return get_error_response('Failed to deposit into artisan wallet');
                }

                $artisan->notify(new CustomNotification(
                    'Wallet Credited',
                    "Your wallet has been credited with ₦" . number_format($apportionment['artisan_earnings'], 2)
                ));
            }

            // Optionally Credit Call2Fix (commented out intentionally)

            DB::commit();
            return get_success_response($serviceRequest, "Service Request closed successfully");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return get_error_response("Invalid status provided", $e->errors(), 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Status update failed", ['error' => $e->getMessage()]);
            return get_error_response("An error occurred while updating the status: " . $e->getMessage(), ['error' => $e->getMessage()], 500);
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

    
            // Check if 'read_by' column exists, if not, add it (This should be done in a migration)
            if (!Schema::hasColumn('submitted_quotes', 'status')) {
                Schema::table('submitted_quotes', function (Blueprint $table) {
                    $table->string('status')->nullable();
                });
            }
            
            $requests->each(function ($request) use ($quoteId) {
                $request->status = ($request->id == $quoteId) ? "accepted" : "rejected";
                $request->save();
            });
            $quote = SubmittedQuotes::whereId($quoteId)->where('request_id', $requestId)->first();
            $amountDue = $quote->total_charges;

            if(!empty($quote->negotiations)) {
                $negotiations = $quote->negotiations;
                foreach ($negotiations as $negotiation) {
                    if(strtolower($negotiation->status) == "accepted"){
                        $amountDue = $negotiation->price;
                    }
                }
            }

            $acceptedRequest = $requests->firstWhere('id', $quoteId);
            if ($acceptedRequest) {
                $service_request = ServiceRequest::whereId($requestId)->first();
                // retrieve the assigned artisan and add to the service request
                $artisan = ArtisanCanSubmitQuote::where([
                    "request_id" => $requestId,
                    "service_provider_id" => $quote->provider_id,
                ])->latest()->first();

                if ($service_request) {
                    $service_request->update([
                        "total_cost" => $amountDue,
                        "request_status" => "Quote Accepted",
                        "approved_providers_id" => $quote->provider_id,
                        "approved_artisan_id" => $artisan->artisan_id ?? null
                    ]);

                    $quote->update([
                        "artisan_id" => $artisan->artisan_id ?? null
                    ]);

                    if ($service_request->save()) {
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
                'price' => 'required|numeric|min:0',
            ]);

            if ($validate->fails()) {
                return get_error_response("Validation failed", $validate->errors(), 422);
            }

            $quote = DB::table('submitted_quotes')->where(['request_id' => $requestId, 'id' => $quoteId])->first();
            if (!$quote) {
                return get_error_response("Quote not found", ["error" => "Quote not found!"], 404);
            }
            Log::info("Quote retrieved");

            $neg = Negotiation::where('request_id', $requestId)->where('status', "accepted")->first();
            if($neg) {
                return get_error_response("Qoute already accepted", ['error' => "Qoute already accepted"], 400);
            }
            Log::info("neg retrieved");


            // $quoteTotal = (float) collect($quote->items)->sum(function ($item) {
            //     return (float) $item['itemTotalPrice'];
            // });

            $items = json_decode($quote->items, true);
            $quoteTotal = collect($items)->sum(fn($item) => (float) $item['itemTotalPrice']);


            Log::info("Quote total");
            $negotiation = Negotiation::create([
                'submitted_quote_id' => $quoteId,
                'request_id' => $requestId,
                'provider_id' => $quote->provider_id,
                'price' => number_format($request->price, 4, '.', ''),
                'status' => 'pending',
                'percentage_decrease' => $request->percentage_decrease ?? 0,
                'new_item_total' => $this->removePercentage($quoteTotal, $request->percentage_decrease) ?? $quoteTotal,
                'new_workmanship' => $this->removePercentage($quote->workmanship, $request->percentage_decrease) ?? $quote->workmanship,
            ]);

            $serviceRequest = ServiceRequest::whereId($requestId)->first();
            
            Log::info("service request retrieved");
            $serviceRequest->update([
                "total_cost" => $request->price,
                "formatted_price" => number_format($request->price, 4, '.', ''),
            ]);

            if($serviceRequest) {
                $user = User::whereId($serviceRequest->user_id)->first();
                $provider = User::whereId($request->provider_id)->first();
                if($provider) {
                    $provider->notify(new CustomNotification("Quote Negotiated by customer", "Quote Negotiated by customer."));
                    // $user->notify(new ServiceRequestNegotiated($serviceRequest));
                }
            }
            Log::info("Final log");

            return get_success_response($negotiation, "Price negotiation submitted successfully");
        } catch (\Throwable $th) {
            Log::info("Error on Negotiation: ", ['error' => $th->getMessage(), 'trace' => $th->getTrace()]);
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
            Log::info("Error on Negotiation: ", ['error' => $th->getMessage()]);
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

            $neg = Negotiation::where('request_id', $negotiation->request_id)->where('status', "accepted")->first();
            if($neg) {
                return get_error_response("Qoute already accepted", ['error' => "Qoute already accepted"], 400);
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

            $amountDue = $quote->total_charges;

            if(!empty($quote->negotiations)) {
                $negotiations = $quote->negotiations;
                foreach ($negotiations as $negotiation) {
                    if(strtolower($negotiation->status) == "accepted"){
                        $amountDue = $negotiation->price;
                    }
                }
            }

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
                $finalStatus = ucfirst($request->status);
                $customer->notify(new CustomNotification("Negotiation {$finalStatus} by Provider", "Negotiation {$finalStatus} by Provider."));

                // Retrieve the service request
                $serviceRequest = ServiceRequestModel::findOrFail($negotiation->request_id);

                if ($serviceRequest->request_status == "Payment Confirmed") {
                    DB::rollBack();
                    return get_error_response("Payment already processed", ["error" => "Payment already processed"], 409);
                }

                // Update request status to "Payment Confirmed"
                $serviceRequest->request_status = "Payment Confirmed";

                $serviceRequest->total_cost = $amountDue;
                // $customer->notify(new PaymentStatusUpdated('Payment Confirmed', $negotiation));

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
            return get_error_response("Column '$column' does not exist in table '$table' or is not an ENUM type.");
        }

        $type = $result[0]->Type; // Safely access the first result

        preg_match('/enum\((.*)\)/', $type, $matches);

        if (!isset($matches[1])) {
            return get_error_response("The column '$column' is not of ENUM type.");
        }

        $enumValues = array_map(function ($value) {
            return trim($value, "'");
        }, explode(',', $matches[1]));

        return $enumValues;
    }

    public function makePayment(Request $request, $requestId, $walletType = 'ngn')
    {
        try {
            // retrieved the service request firstly
            $request->validate([
                "artisan_id" => "required"
            ]);

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
            $transaction[] = $wallet->withdrawal($total_cost * 100,  ['description' => "Service request payment - {$serviceRequest->id}", "narration" => $request->narration ?? null]);

            if ($transaction && $wallet) {
                $serviceRequest->update([
                    "total_cost" => $total_cost,
                    "approved_artisan_id" => $request->artisan_id,
                    "request_status" => "Payment Confirmed"
                ]);

                $pro = $serviceRequest->approved_providers_id;
                if(empty($pro)) {
                    // check if artisan is set
                    $artisan = Artisans::where('artisan_id', $request->artisan_id)->first();
                    if($artisan) {
                        $serviceRequest->update([
                            'approved_providers_id' => $artisan->service_provider_id
                        ]);
                        $pro = $artisan->service_provider_id;
                    }
                }
                $provider = User::find($pro);
                // $provider->notify(new CustomNotification("Payment confirmed", "Payment confirmed."));
                // return success data with the transaction and service request data  
                return get_success_response([
                    'transaction' => $transaction,
                    'service_request' => $serviceRequest
                ], 'Payment successful');
            }
        } catch (\Throwable $th) {
            Log::debug("Transaction failed_1: ", ['error' => $th->getMessage()]);
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    private function aportionment(ServiceRequestModel $serviceRequest)
    {
        $submittedQuote = SubmittedQuotes::where('request_id', $serviceRequest->id)->where('status', "accepted")->first();
        if(!$submittedQuote) {
            return ['error' => 'Quote not found'];
        }

        $neg = Negotiation::where('request_id', $serviceRequest->id)->where('status', "accepted")->first();
        if(!$neg) {
            return ['error' => 'Negotiation not found'];
        }

        $quoteTotal = $neg->new_item_total + $neg->new_workmanship;
        
        $call2FixFee = $quoteTotal * 0.10;
        $warrantyRetention = $quoteTotal * 0.10;
        $distributable = $quoteTotal * 0.80;
        
        // Retrieve Artisan and Payment Logic
        $artisan = Artisans::where('id', $submittedQuote->approved_artisan_id)->first();
        
        $artisanShare = 0;
        if ($artisan) {
            $paymentMethod = $artisan->payment_method; // "fixed" or "percentage"
            $paymentValue = (float) $artisan->payment_value;
        
            if ($paymentMethod === 'fixed') {
                $artisanShare = $paymentValue;
            } elseif ($paymentMethod === 'percentage') {
                $artisanShare = $distributable * ($paymentValue / 100);
            }
        }
        
        // Final Distribution
        $providerShare = $distributable - $artisanShare;
        $apportionments = [
            'subtotal' => $quoteTotal,
            'service_provider_earnings' => $providerShare,
            'call2fix_management_fee' => $submittedQuote->administrative_fee,
            'call2fix_earnings' => $call2FixFee,
            'warranty_retention' => $warrantyRetention,
            'artisan_earnings' => $artisanShare,
        ];
        Log::debug("Hello world: ", ['apportionments' => $apportionments]);
        return $apportionments;
    }

    private function removePercentage($amount, $percentage)
    {
        $deduction = ($percentage / 100) * $amount;
        return $amount - $deduction;
    }

}
