<?php

namespace App\Http\Controllers;

use App\Mail\NewSubAccountMail;
use App\Models\SubAccounts;
use App\Models\User;
use Illuminate\Http\Request;
use Mail;
use Str;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubAccountsController extends Controller
{
    public $sub;

    public function __construct()
    {
        if (!Schema::hasColumn('users', 'description')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('description')->nullable();
            });
        }
        $this->sub = new SubAccounts();
    }

    // public function getSubAccounts(Request $request)
    // {
    //     try {
    //         $accounts = User::where([
    //             "parent_account_id" => auth()->id(),
    //             "main_account_role" => $request->input('current_role', $request->user()->current_role)
    //         ])->with('wallets')->limit(10)->get();
    //         return get_success_response($accounts, "Sub accounts retrieved successfully");
    //     } catch (\Throwable $th) {
    //         return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
    //     }
    // }
    public function getSubAccounts(Request $request)
    {
        try {
            $accounts = User::where([
                "parent_account_id" => auth()->id(),
                "main_account_role" => $request->input('current_role', $request->user()->current_role)
            ])->limit(per_page(10))->get();

            // Attach wallets manually
            $accounts->each(function ($account) {
                $account->wallets = $account->my_wallets();
            });

            return get_success_response($accounts, "Sub accounts retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }


    public function fetchSubAccount($subAccountId)
    {
        try {
            $account = $this->sub->fetchAccount(auth()->user()->role, $subAccountId);

            if (!$account) {
                return get_error_response("Sub account not found", ['error' => "Sub account not found!"], 404);
            }

            return get_success_response($account, "Sub account retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function addSubAccount(Request $request)
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
            $data['department_description'] = $request->description;
            unset($data['name']);
            unset($data['description']);
            $data['first_name'] = $name[0];
            $data['parent_account_id'] = auth()->id();
            $data['last_name'] = isset($name[1]) ? implode(' ', array_slice($name, 1)) : $name[0];
            $password = "!".Str::random(12);
            $data['password'] = bcrypt($password);
            $data['phone'] = $request->phone;
            $data['username'] = explode("@", $request->email)[0].rand(1, 299);
            $data['main_account_role'] = $user->current_role;
            $data['sub_account_type'] = $user->sub_account_type;

            $subAccount = User::create($data);
            Mail::to($subAccount->email)->send(new NewSubAccountMail($subAccount, $password));

            return get_success_response($subAccount, "Sub account added successfully and password sent via email");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function deleteSubAccount($subAccountId)
    {
        try {
            $user = auth()->user();
            $account = User::where([
                'parent_account_id' => $user->id,
                'id' => $subAccountId,
                'main_account_role' => $user->current_role
            ])->first(); //$this->sub->fetchAccount(auth()->user()->role, $subAccountId);

            if (!$account) {
                return get_error_response("Sub account not found", ['error' => "Sub account not found!"], 404);
            }

            if ($account->delete()) {
                return get_success_response(null, "Sub account deleted successfully");
            }
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function fundSubAccount(Request $request, $subAccountId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return get_error_response("Validation failed", $validator->errors(), 422);
        }

        try {
            $result = $this->sub->fundSubAccount(auth()->id(), $subAccountId, $request->amount);
            if ($result) {
                return get_success_response(null, "Sub account funded successfully");
            }

            return get_error_response("Funding failed", ['error' => "Funding failed"], 500);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function transferFromSubAccount(Request $request, $subAccountId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'recipient_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return get_error_response("Validation failed", $validator->errors(), 422);
        }

        try {
            $result = $this->sub->transferFromSubAccount($subAccountId, $request->recipient_id, $request->amount);
            if ($result) {
                return get_success_response(null, "Funds transferred successfully");
            }

            return get_error_response("Transfer failed", ['error' => "Transfer failed"], 500);
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }

    public function getSubAccountBalance($subAccountId)
    {
        try {
            $balance = $this->sub->getSubAccountBalance($subAccountId);
            return get_success_response(['balance' => $balance], "Balance retrieved successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }
    
    public function loginSubAccount($subAccountId)
    {
        try {
            $account = $this->sub->loginResponse($subAccountId);
            return get_success_response($account, "Account authenticated successfully");
        } catch (\Throwable $th) {
            return get_error_response($th->getMessage(), ['error' => $th->getMessage()]);
        }
    }
}
