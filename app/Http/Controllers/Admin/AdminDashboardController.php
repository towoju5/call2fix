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
            $data = [
                'top_spending_customers' => $this->getTopSpendingUsers(role : 'customer', limit: 5),
                'top_earning_providers' => $this->getTopSpendingUsers(role : 'providers', limit: 5),
                // customers 
                'new_customers' => User::role('customer')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_customers' => User::role('customer')->count(),
                // providers
                'new_providers' => User::role('provider')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_providers' => User::role('provider')->count(),
                // artisans
                'new_artisans' => User::role('artisan')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_artisans' => User::role('artisan')->count(), 
        
                'pending_service_resquests' => ServiceRequest::where('request_status', 'pending')->count(),
        
                'category_count' => Category::count(),
                'service_count' => Service::count(),
                'profits' => $this->getProfits(),
                'total_sales' => $this->getSales(),
                'order_types_chart' => $this->getOrderTypesChartData(),
                'profit_chart' => $this->getProfitChartData(),
                'top_selling_services' => $this->getTopSellingServices(),
            ];

            return view('admin.dashboard', $data);
        } catch (\Exception $e) {
            // Handle the exception here
            // For example, you could log the error and return an error view
            \Log::error('Error in AdminDashboardController index method: ' . $e->getMessage());
            return view('admin.error', ['message' => 'An error occurred while loading the dashboard.']);
        }
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
        return Order::select(DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count')
            ->toArray();
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


    private function getTopSpendingUsers($role = 'customer', $limit = 5)
    {
        $result = DB::table('transaction_records')
            ->join('model_has_roles', 'transaction_records.user_id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('transaction_records.user_id', DB::raw('SUM(transaction_amount) as total_spending'))
            ->where('roles.name', $role)
            ->groupBy('transaction_records.user_id')
            ->orderBy('total_spending', 'desc')
            ->take($limit)
            ->get();

        return $result;
    }
}
