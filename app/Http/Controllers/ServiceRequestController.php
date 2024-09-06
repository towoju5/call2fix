<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $serviceRequests = ServiceRequest::where('user_id', auth()->id())->get();
        return response()->json($serviceRequests);
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

        if($validate->fails()) {
            return get_error_response("Validation Error", $validate->errors()->toArray());
        }

        $validatedData = $validate->validated();

        $validatedData['user_id'] = auth()->id();
        $validatedData['problem_images'] = $request->problem_images;

        if($request->use_featured_providers) {
            $validatedData['featured_providers_id'] = $request->featured_providers_id;
        } else {
            $validatedData['featured_providers_id'] = null;
        }

        $serviceRequest = ServiceRequest::create($validatedData);
        return response()->json($serviceRequest, 201);
    }

    public function show(ServiceRequest $serviceRequest)
    {
        try {
            return get_success_response($serviceRequest);
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

        $serviceRequest->update($validatedData);
        return response()->json($serviceRequest);
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

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        try {
            $validStatuses = [
                "Draft", "Pending", "Processing", "Bidding In Progress", "Quote Accepted",
                "Awaiting Payment", "Payment Confirmed", "On Hold", "Work In Progress",
                "Cancelled", "Completed", "Overdue", "Closed", "Rejected"
            ];
    
            $validatedData = $request->validate([
                'status' => ['required', Rule::in($validStatuses)],
            ]);
    
            $serviceRequest->update(['status' => $validatedData['status']]);
    
            return get_success_response($serviceRequest, "Service Request status updated successfully");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return get_error_response("Invalid status provided", $e->errors(), 422);
        } catch (\Exception $e) {
            return get_error_response("An error occurred while updating the status", ['error' => $e->getMessage()], 500);
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
}
