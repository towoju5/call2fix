<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TransactionRecords;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ServiceRequest;
use App\Models\Product;
use App\Models\Order;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Validator;
use Towoju5\Wallet\Models\Wallet;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')//->whereNull('parent_account_id')
            ->when(request('roles'), function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->whereIn('name', is_array(request('roles')) ? request('roles') : [request('roles')]);
                });
            })
            ->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        // $wallets = \Towoju5\Wallet\Models\Wallet::all();
        // return response()->json($wallets);
        $transactions = $user->transactions()->latest()->take(10)->get();
        $serviceRequests = $user->serviceRequests()->latest()->take(10)->get();
        $products = $user->products()->latest()->take(10)->get();
        $orders = $user->orders()->latest()->take(10)->get();
        $wallets = $user->my_wallets();
        $properties = $user->properties()->latest()->take(10)->get();
        // $artisans = $user->artisans()->latest()->take(10)->get();
        $bankAccount = $user->bankAccount()->latest()->take(10)->get();
        $business_info = $user->business_info()->latest()->take(10)->get();
        
        $my_wallet = Wallet::where([
            'user_id' => $user->id,
        ])->get();

        return view('admin.users.show', compact('user', 'my_wallet', 'transactions', 'serviceRequests', 'products', 'orders', 'wallets', 'properties', 'bankAccount', 'business_info'));
    }


    public function topUpWallet(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $user->deposit($request->amount);

        return redirect()->back()->with('success', 'Wallet topped up successfully.');
    }

    public function debitWallet(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $user->withdraw($request->amount, 'ngn');
            return redirect()->back()->with('success', 'Wallet debited successfully.');
        } catch (InsufficientFunds $exception) {
            return redirect()->back()->with('error', 'Insufficient funds in the wallet.');
        }
    }


    public function ban(User $user)
    {
        $user->update(['is_banned' => true]);
        return redirect()->back()->with('success', 'User banned successfully.');
    }

    public function unban(User $user)
    {
        $user->update(['is_banned' => false]);
        return redirect()->back()->with('success', 'User unbanned successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
        ]);

        Excel::import(new User(), $request->file('file'));

        return redirect()->back()->with('success', 'Users imported successfully.');
    }

    public function getTransactions(User $user)
    {
        $transactions = $user->transactions()->paginate(15);
        return response()->json($transactions);
    }

    public function getServiceRequests(User $user)
    {
        $serviceRequests = $user->serviceRequests()->paginate(15);
        return response()->json($serviceRequests);
    }

    public function getProducts(User $user)
    {
        $products = $user->products()->paginate(15);
        return response()->json($products);
    }

    public function getOrders(User $user)
    {
        $orders = $user->orders()->paginate(15);
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required_without:phone|string|email|max:255|unique:users',
            'phone' => 'required_without:email|string|max:20|unique:users',
            'account_type' => 'required|string|in:artisan,suppliers,providers,affiliate,private_account,corporate_account',
            'device_id' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
            'username' => 'required|string|max:255|unique:users',
            'profile_picture' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }


        $user = User::create($validator->validated());

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'lastt_name' => 'required|string|max:255',
            'email' => 'required_without:phone|string|email|max:255|unique:users',
            'phone' => 'required_without:email|string|max:20|unique:users',
            'account_type' => 'required|string|in:artisan,suppliers,providers,affiliate,private_account,corporate_account',
            'device_id' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'username' => 'required|string|max:255|unique:users',
            'profile_picture' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return get_error_response($validator->errors());
        }


        $user->update($validator->validated());

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
