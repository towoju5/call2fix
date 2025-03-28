<?php

namespace App\Http\Controllers;

use App\Mail\NewSubAccountMail;
use App\Models\Department;
use App\Models\Order;
use App\Models\ServiceRequest;
// use App\Models\Wallet;
use Towoju5\Wallet\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        try {
            $user = auth()->user();
            $departments = User::where(['parent_account_id' => $user->id, 'sub_account_type' => 'department'])->get();
            return get_success_response($departments, "Departments retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage());
        }
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string|regex:/^\+[1-9]\d{1,14}$/|max:20|unique:users',
            'email' => 'required|email|unique:users,email',
            'sub_account_type' => 'required|string',
            "description" => "required_if:sub_account_type,department"
        ]);

        if ($validator->fails()) {
            return get_error_response("Validation failed", $validator->errors(), 422);
        }

        try {
            $name = explode(" ", $request->name);
            $data = $request->all();
            $user = $request->user();
            unset($data['name']);
            $data['first_name'] = $name[0];
            $data['parent_account_id'] = auth()->id();
            $data['last_name'] = isset($name[1]) ? implode(' ', array_slice($name, 1)) : $name[0];
            $password = Str::random(12);
            $data['password'] = bcrypt($password);
            $data['phone'] = $request->phone;
            $data['username'] = explode("@", $request->email)[0].rand(1, 299);
            $data['main_account_role'] = $user->current_role;
            $data['sub_account_type'] = $user->sub_account_type;
            $data['department_description'] = $request->description;

            $subAccount = User::create($data);
            Mail::to($subAccount->email)->send(new NewSubAccountMail($subAccount, $password));

            return get_success_response($subAccount, "Sub account added successfully and password sent via email");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
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
            $department = ServiceRequest::whereUserId($departmentId)->paginate(per_page());
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
            $history = Wallet::whereUserId($departmentId)->get();
            $transactions = WalletTransaction::where('user_id', $department->id)->paginate(10);
            $deposits = WalletTransaction::where('user_id', $department->id)->where('type', 'deposit')->sum('amount');
            $spent = WalletTransaction::where('user_id', $department->id)->where('type', 'withdrawal')->sum('amount');
            return get_success_response([
                'total_deposit' => $deposits, 
                'total_spent' => $spent, 
                'wallet_history' => $department,
                'history' => $history
            ], 
                'Department orders retrieved successfully.');
        } catch (\Exception $e) {
            return get_error_response('An error occurred while fetching', ['error' => $e->getMessage()]);
        }
    }
}
