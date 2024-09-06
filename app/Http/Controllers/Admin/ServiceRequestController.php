<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Validator;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $serviceRequests = ServiceRequest::paginate(get_settings_value('per_page') ?? 10);
        return view('admin.service-requests.index', compact('serviceRequests'));
    }

    public function create()
    {
        return view('admin.service-requests.create');
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
        ]);

        if ($validate->fails()) {
            return back()->withErrors($validate->errors())->withInput();
        }

        $serviceRequest = ServiceRequest::create($validate->validated());

        return redirect()->route('admin.service-requests.index')->with('success', 'Service request created successfully.');
    }

    public function show(ServiceRequest $serviceRequest)
    {
        return view('admin.service-requests.show', compact('serviceRequest'));
    }

    public function edit(ServiceRequest $serviceRequest)
    {
        return view('admin.service-requests.edit', compact('serviceRequest'));
    }

    public function update(Request $request, ServiceRequest $serviceRequest)
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
        ]);

        if ($validate->fails()) {
            return get_error_response("Validation Error", $validate->errors()->toArray());
        }

        $serviceRequest->update($validate->validated());

        return redirect()->route('admin.service-requests.index')->with('success', 'Service request updated successfully.');
    }

    public function destroy(ServiceRequest $serviceRequest)
    {
        $serviceRequest->delete();
        return redirect()->route('admin.service-requests.index')->with('success', 'Service request deleted successfully.');
    }

    public function createOnBehalfOfCustomer()
    {
        $customers = User::role('customer')->get();
        return view('admin.service-requests.create-on-behalf', compact('customers'));
    }

    public function storeOnBehalfOfCustomer(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
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
        ]);

        if ($validate->fails()) {
            return get_error_response("Validation Error", $validate->errors()->toArray());
        }

        $serviceRequest = ServiceRequest::create($validate->validated());

        return redirect()->route('admin.service-requests.index')->with('success', 'Service request created on behalf of customer successfully.');
    }
}
