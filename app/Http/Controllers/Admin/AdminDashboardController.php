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
            // available roles are: artisan, providers, co-operate_accounts, private_accounts, affiliates, suppliers, department,
            return $data = [
                'top_spending_customers' => $this->getTopSpendingUsers(role: 'customer', limit: 5),
                'top_earning_providers' => $this->getTopSpendingUsers(role: 'providers', limit: 5),

                'new_artisans' => User::role('artisan')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_artisans' => User::role('artisan')->count(),
                'new_providers' => User::role('providers')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_providers' => User::role('providers')->count(),
                'new_cooperate_accounts' => User::role('co-operate_accounts')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_cooperate_accounts' => User::role('co-operate_accounts')->count(),
                'new_private_accounts' => User::role('private_accounts')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_private_accounts' => User::role('private_accounts')->count(),
                'new_affiliates' => User::role('affiliates')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_affiliates' => User::role('affiliates')->count(),
                'new_suppliers' => User::role('suppliers')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_suppliers' => User::role('suppliers')->count(),
                'new_department' => User::role('department')->where('created_at', '>=', Carbon::now()->subDays(7))->latest()->limit(8)->get(),
                'total_department' => User::role('department')->count(),
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
            // ->groupBy('type')
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

    // private function getTopSellingServices($limit = 5)
    // {
    //     return Category::with('services')->withCount('orders')
    //         ->take($limit)
    //         ->get();
    // }

    private function getTopSellingServices($limit = 5)
    {
        return [];
        //  Category::with(['services'])
        //     ->withCount([
        //         'services as orders_count' => function ($query) {
        //             $query->whereHas('orders', function ($q) {
        //                 $q->whereNull('deleted_at');
        //             });
        //         }
        //     ])
        //     ->whereNull('deleted_at')
        //     ->orderBy('orders_count', 'desc')
        //     ->take($limit)
        //     ->get();
    }




    private function getTopSpendingUsers($role = 'co-operate_accounts', $limit = 5)
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
