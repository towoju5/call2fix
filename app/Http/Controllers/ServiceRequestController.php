<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $serviceRequests = ServiceRequest::where('user_id', auth()->id())->get();
        return response()->json($serviceRequests);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'service_id' => 'nullable|exists:services,id',
            'problem_title' => 'required|string|max:255',
            'problem_description' => 'required|string',
            'inspection_time' => 'required|date_format:H:i',
            'inspection_date' => 'required|date',
            'problem_images' => 'required|array',
            'use_featured_providers' => 'boolean',
            'featured_providers_id' => 'nullable|array',
        ]);

        $validatedData['user_id'] = auth()->id();
        $validatedData['problem_images'] = json_encode($request->problem_images);
        $validatedData['featured_providers_id'] = json_encode($request->featured_providers_id);

        $serviceRequest = ServiceRequest::create($validatedData);
        return response()->json($serviceRequest, 201);
    }

    public function show(ServiceRequest $serviceRequest)
    {
        return response()->json($serviceRequest);
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
    {

        $validatedData = $request->validate([
            'property_id' => 'sometimes|exists:properties,id',
            'service_category_id' => 'nullable|exists:service_categories,id',
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
        $serviceRequest->delete();
        return response()->json(null, 204);
    }

    public function getFeaturedProviders(ServiceRequest $serviceRequest)
    {
        $featuredProviderIds = json_decode($serviceRequest->featured_providers_id);
        $featuredProviders = User::whereIn('id', $featuredProviderIds)->get();
        return response()->json($featuredProviders);
    }
}
