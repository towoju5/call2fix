<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return get_error_response($validator->errors()->first(), 422);
            }

            $user = auth()->user();
            $department = $user->createDepartment($request->input('name'));

            return get_success_response('Department created successfully.', [
                'department' => $department,
            ]);
        } catch (\Exception $e) {
            return get_error_response('An error occurred while creating the department.', 500);
        }
    }

    public function orders($departmentId)
    {
        try {
            $user = auth()->user();
            $department = Order::whereUserId($departmentId)->paginate(10);
            return get_success_response($department,'Department orders retrieved successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching the department.', 500);
        }
    }

    public function ServiceRequests($departmentId)
    {
        try {
            $department = ServiceRequest::whereUserId($departmentId)->paginate(10);
            return get_success_response($department,'Department orders retrieved successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching the department.', 500);
        }
    }
}
