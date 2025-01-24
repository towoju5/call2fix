<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
   public function index(Request $request)
    {
        try {
            // return $this->getOrderTypesChartData();
            $data = [
                'user_count' => User::count(),
                'top_spending_customers' => $this->getTopSpendingUsers(['co-operate_accounts', 'private_accounts'], 5),
                // 'top_spending_customers' => $this->getTopSpendingUsers('co-operate_accounts', 5),
                'top_earning_providers' => $this->getTopSpendingUsers(['providers'], 5),
                'top_earning_suppliers' => $this->getTopSpendingUsers(['suppliers'], 5),
                
                'new_artisans' => User::role('artisan')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_artisans' => User::role('artisan')->count(),
                
                'new_providers' => User::role('providers')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_providers' => User::role('providers')->count(),
                
                'new_cooperate_accounts' => User::role('co-operate_accounts')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_cooperate_accounts' => User::role('co-operate_accounts')->count(),
                
                'new_private_accounts' => User::role('private_accounts')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_private_accounts' => User::role('private_accounts')->count(),
                
                'new_affiliates' => User::role('affiliates')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_affiliates' => User::role('affiliates')->count(),
                
                'new_suppliers' => User::role('suppliers')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_suppliers' => User::role('suppliers')->count(),
                
                'new_department' => User::role('department')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_department' => User::role('department')->count(),
                
                'new_merchants' => User::role(['suppliers', 'providers'])->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_merchants' => User::role(['suppliers', 'providers'])->count(),
                
                'new_service_requests' => User::role(['suppliers', 'providers'])->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->get(),
                'total_service_requests' => User::role(['suppliers', 'providers'])->count(),
                
                'pending_service_resquests' => ServiceRequest::where('request_status', 'pending')->count(),
                'category_by_orders' => $this->categoryByOrders(),
                'top_services_by_orders' => $this->getTopServices(),
                'category_count' => Category::count(),
                'service_count' => Service::count(),
                'profits' => $this->getProfits(),
                'total_sales' => $this->getSales(),
                'order_types_chart' => [], //$this->getOrderTypesChartData(), // Ensure labels and values are defined in the method
                'profit_chart' => [], //$this->getProfitChartData(),
                'top_selling_services' => [], //$this->getTopSellingServices(), // Retrieve top-selling services

                'new_customers' => User::role('private_accounts')->latest()->get(), // Recent customers
            ];
    
            return view('admin.dashboard', $data);
        } catch (\Exception $e) {
            return $e->getMessage();
            \Log::error('Error in AdminDashboardController index method: ' . $e->getMessage());
            // return view('admin.error', ['message' => 'An error occurred while loading the dashboard.']);
        }
    }


    public function getTopServices()
    {
        $request = request();
        $categoryId = $request->get('category_id'); // Optional filter by category
        $limit = $request->get('limit', 10); // Default to top 10 services
        $direction = $request->get('direction', 'desc'); // Default to descending order

        // Fetch top services
        $topServices = Service::topServices($categoryId, $limit, $direction)->get();

        return $topServices;
    }

    private function getProfits()
    {
        $now = Carbon::now();
        return [
            'today' => Order::whereDate('created_at', $now->today())->sum('total_price'),
            'this_week' => Order::whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()])->sum('total_price'),
            'this_month' => Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('total_price'),
            'this_year' => Order::whereYear('created_at', $now->year)->sum('total_price'),
        ];
    }

    private function getSales()
    {
        $now = Carbon::now();
        return [
            'today' => Order::whereDate('created_at', $now->today())->sum('total_price'),
            'this_week' => Order::whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()])->sum('total_price'),
            'this_month' => Order::whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->sum('total_price'),
            'this_year' => Order::whereYear('created_at', $now->year)->sum('total_price'),
        ];
    }

    private function getOrderTypesChartData()
    {
        // return [];
        return $data = Order::select('delivery_type', DB::raw('count(*) as count'))
            ->groupBy('delivery_type')
            ->get()
            ->pluck('count', 'delivery_type')
            ->toArray();
    
        // return [
        //     'labels' => array_keys($data),
        //     'values' => array_values($data),
        // ];
    }


    private function getProfitChartData()
    {
        return Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as total')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();
    }

    private function getTopSellingServices($limit = 5)
    {
        return Category::with('services')->withCount('orders')
            ->take($limit)
            ->get();
    }

    
    private function getTopSpendingUsers($role = 'co-operate_accounts', $limit = 5)
    {
        // Query to get top spending users with transaction count and summed transaction amount
        $top_spending_customers = User::withCount('transactions')->get()
            ->map(function ($user) {
                $transaction_sum = $user->transactions->sum('transaction_amount');
                $user->total_spent = $transaction_sum;
                return $user;
            })
            ->sortByDesc('total_spent')->take($limit);
    
        return $top_spending_customers;
    }

    public function categoryByOrders()
    {
        $request = request();
        $direction = $request->get('direction', 'desc'); // Default to 'desc'
        $categories = Category::withOrderCounts($direction)->get();
        return $categories;
    }

    // private function getTopSellingServices($limit = 5)
    // {
    //     return Service::with('category')
    //         ->select('categories.*', DB::raw('COUNT(orders.id) as order_count'))
    //         ->join('orders', 'categories.id', '=', 'orders.category_id')
    //         ->groupBy('categories.id')
    //         ->orderBy('order_count', 'desc')
    //         ->take($limit)
    //         ->get();
    // }
    
    // private function getTopSpendingUsers(array $roles = ['co-operate_accounts', 'private_accounts', 'department'], $limit = 5)
    // {
    //     $result = DB::table('transaction_records')
    //         ->join('model_has_roles', 'transaction_records.user_id', '=', 'model_has_roles.model_id')
    //         ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
    //         ->select('transaction_records.user_id', DB::raw('SUM(transaction_amount) as total_spending'))
    //         ->whereIn('roles.name', $roles) // Check if the role is in the specified array
    //         ->groupBy('transaction_records.user_id')
    //         ->orderBy('total_spending', 'desc')
    //         ->take($limit)
    //         ->get();
    
    //     return $result;
    // }

}
