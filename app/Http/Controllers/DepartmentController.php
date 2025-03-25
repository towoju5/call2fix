<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Order;
use App\Models\ServiceRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            $departments = Department::get();
            return get_success_response($departments, "Departments retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage());
        }
    }


    public function store(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'department_name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return get_error_response($validator->errors()->first(), $validator->errors()->toArray(), 422);
            }

            $user = auth()->user();
            
            if (!$user) {
                return get_error_response('User not authenticated', [], 401);
            }

            $department = $user->createDepartment($request->department_name);

            if (!$department) {
                return get_error_response('Failed to create department', [], 500);
            }

            return get_success_response($department, 'Department created successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while creating the department: ' . $e->getMessage(), [], 500);
        }
    }
    public function orders($departmentId)
    {
        try {
            $user = auth()->user();
            $department = Order::whereUserId($departmentId)->paginate(10);
            return get_success_response($department, 'Department orders retrieved successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching the department.', 500);
        }
    }

    public function ServiceRequests($departmentId)
    {
        try {
            $department = ServiceRequest::whereUserId($departmentId)->paginate(10);
            return get_success_response($department, 'Department orders retrieved successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching the department.', 500);
        }
    }

    public function walletHistory($departmentId)
    {
        try {
            $department = Wallet::whereDepartmentId($departmentId)->where('is_department', true)->first();
            if (!$department) {
                return get_error_response('Department wallet not found', ['error' => 'Department wallet not found!'], 404);
            }
            $transactions = WalletTransaction::where('department_id', $department->id)->paginate(10);
            $deposits = WalletTransaction::where('department_id', $department->id)->where('type', 'deposit')->sum('amount');
            $spent = WalletTransaction::where('department_id', $department->id)->where('type', 'withdrawal')->sum('amount');
            return get_success_response([
                'total_deposit' => $deposits, 
                'total_spent' => $spent, 
                'wallet_history' => $department], 
                'Department orders retrieved successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching', ['error' => $e->getMessage()]);
        }
    }
}
