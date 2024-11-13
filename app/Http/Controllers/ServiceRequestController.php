<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\ServiceRequest;
use App\Models\SubmittedQuotes;
use App\Models\User;
use DB, App\Models\ArtisanCanSubmitQuote;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\Notifications\ReworkIssuedNotification;

class ServiceRequestController extends Controller
{
    protected $radiusLimitKm;
    public function __construct()
    {
        $this->radiusLimitKm = get_settings_value('max_provider_radius') ?? 30;
    }

    public function index()
    {
        $serviceRequests = ServiceRequest::with('reworkMessages', 'service_provider')->whereUserId(auth()->id())->get();
        return get_success_response($serviceRequests);
    }

    public function serviceProviderRequest()
    {
        $serviceRequests = ServiceRequest::with('reworkMessages')->whereJsonContains('featured_providers_id', [auth()->id()])->get();
        return get_success_response($serviceRequests);
    }

    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'service_category_id' => 'nullable|exists:categories,id',
            'service_id' => 'nullable|exists:services,id',
            'problem_title' => 'required|string|max:255',
            'problem_description' => 'required|string',
            'inspection_time' => 'required|date_format:H:i',
            'inspection_date' => 'required|date',
            'problem_images' => 'nullable|array',
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

        if ($request->use_featured_providers) {
            $validatedData['featured_providers_id'] = $request->featured_providers_id;
        } else {
            $propertyId = $request->property_id;
            // Get the property details
            $property = Property::findOrFail($propertyId);
            // Get the radius limit in kilometers from settings and convert to meters
            $radiusLimitMeters = $this->radiusLimitKm * 1000; // Convert km to meters
            $providers = User::where('account_type', 'providers')
                // ->select(DB::raw('*, ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) as distance'))
                // ->whereRaw('ST_Distance_Sphere(point(longitude, latitude), point(?, ?)) <= ?', [
                //     $property->longitude,
                //     $property->latitude,
                //     $property->longitude,
                //     $property->latitude,
                //     $radiusLimitMeters
                // ])
                ->whereNot('id', auth()->id())
                ->inRandomOrder()
                ->take(5)
                // ->orderBy('distance')
                ->pluck('id');

            if (empty($providers) || count($providers) < 1) {
                return get_error_response('No provider found!', ['error' => 'No service provider found nearby']);
            }

            $validatedData['featured_providers_id'] = $providers;
        }

        $serviceRequest = ServiceRequest::create($validatedData);
        if ($serviceRequest) {
            return get_success_response($serviceRequest, "Request created successfully", 201);
        }
    }

    public function show(ServiceRequest $serviceRequest)
    {
        try {
            return get_success_response($serviceRequest->with('reworkMessages'));
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage());
        }
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
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
        $serviceRequest = ServiceRequest::find($serviceRequest);

        if (!$serviceRequest) {
            return get_error_response("Service Request Not Found", [], 404);
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
            $validStatuses = [
                "Draft",
                "Pending",
                "Processing",
                "Bidding In Progress",
                "Quote Accepted",
                "Awaiting Payment",
                "Payment Confirmed",
                "On Hold",
                "Work In Progress",
                "Cancelled",
                "Completed",
                "Overdue",
                "Closed",
                "Rejected"
            ];

            $serviceRequest = ServiceRequest::where("user_id", auth()->id())->whereId($requestId)->first();

            if (!$serviceRequest) {
                return get_error_response("Service request not found", ["error" => "Service request not found"]);
            }

            $validatedData = $request->validate([
                'status' => ['required', Rule::in($validStatuses)],
            ]);

            $update = $serviceRequest->update(['status' => $validatedData['status']]);

            if ($update && $validatedData['status'] == 'Completed') {
                // get provider  and artisan
                $provider = User::whereId($serviceRequest->provider_id)->first();
                $artisan_id = ArtisanCanSubmitQuote::where(["artisan_id" => auth()->id(), "request_id" => $request->request_id])->first();
                $artisan = User::whereId($artisan_id)->first();
                // get artisan percentage
                $providerEarnings = $serviceRequest->amount;
                $artisanPercentage = $artisan->payment_plan; // can be fixed or percentage
                $artisanEarning = $artisan->payment_amount;

                if (strtolower($artisan->payment_plan) === 'percentage') {
                    // calculate percentage of the earned amount
                    $artisanEarning = ($providerEarnings / 100) * $artisanPercentage;
                }

                // credit the provider's and artisan's wallet
                $provider->getWallet('ngn')->deposit(floatval($providerEarnings - $artisanEarning), ["description" => $serviceRequest->problem_title]);
                $artisan->getWallet('ngn')->deposit($artisanEarning, ["description" => $serviceRequest->problem_title]);
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
}
