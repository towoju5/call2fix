<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderModel;
use App\Models\Product;
use App\Models\Artisans;
use App\Models\ServiceRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Towoju5\Wallet\Models\Wallet;
use Carbon\Carbon;

class ChartController extends Controller
{
    public function getFilteredData($model, $filter, $hasUserColumn=true)
    {
        $query = $model->query();
        $groupBy = 'day';
        $startDate = now();
        $endDate = now();

        switch ($filter) {
            case '7d':
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                $groupBy = 'day';
                break;
            case '4w':
                $startDate = now()->subWeeks(3)->startOfWeek();
                $endDate = now()->endOfDay();
                $groupBy = 'week';
                break;
            case '3m':
                $startDate = now()->subMonths(2)->startOfMonth();
                $endDate = now()->endOfDay();
                $groupBy = 'month';
                break;
            case '6m':
                $startDate = now()->subMonths(5)->startOfMonth();
                $endDate = now()->endOfDay();
                $groupBy = 'month';
                break;
            case '1y':
                // Adjusted to start from last year's current month +1 to ensure 12-month range
                $startDate = now()->subYear()->startOfMonth()->addMonth();
                $endDate = now()->endOfDay();
                $groupBy = 'month';
                break;
            default:
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                $groupBy = 'day';
        }

        if($hasUserColumn) $query = $query->where(['user_id' => auth()->id(), '_account_type' => active_role()]);
        // Fetch and process data as before
        $data = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $allDates = [];
        $currentDate = $startDate->copy();

        if ($groupBy === 'day') {
            while ($currentDate->lte($endDate)) {
                $dayOfWeek = $currentDate->format('D');
                $allDates[$dayOfWeek] = $data[$currentDate->format('Y-m-d')] ?? 0;
                $currentDate->addDay();
            }
        } elseif ($groupBy === 'week') {
            $weekCount = 1;
            $currentWeekStart = $startDate->copy()->startOfWeek();
            while ($currentWeekStart->lte($endDate)) {
                $weekLabel = 'w' . $weekCount;
                $allDates[$weekLabel] = 0;
                $currentWeekStart->addWeek();
                $weekCount++;
            }

            foreach ($data as $date => $count) {
                $date = Carbon::createFromFormat('Y-m-d', $date);
                $weekNumber = floor($date->diffInWeeks($startDate->startOfWeek())) + 1;
                $weekLabel = 'w' . $weekNumber;
                $allDates[$weekLabel] += $count;
            }
        } elseif ($groupBy === 'month') {
            $currentDate = $startDate->copy()->startOfMonth();
            while ($currentDate->lte($endDate)) {
                $monthName = $currentDate->format('F');
                $allDates[$monthName] = 0;
                $currentDate->addMonth();
            }

            foreach ($data as $date => $count) {
                $monthName = Carbon::createFromFormat('Y-m-d', $date)->format('F');
                $allDates[$monthName] += $count;
            }
        }

        return $allDates;
    }

    public function orders()
    {
        $filter = request()->get('filter', '7d');
        $data = $this->getFilteredData(new OrderModel, $filter);
        return get_success_response($data);
    }

    public function wallets()
    {
        $filter = request()->get('filter', '7d');
        $data = $this->getFilteredData(new Wallet, $filter);
        return get_success_response($data);
    }

    public function wallet_transactions()
    {
        $filter = request()->get('filter', '7d');
        $query = new \Towoju5\Wallet\Models\WalletTransaction();
        $data = $this->getFilteredData($query, $filter, false);
        $startDate = now();
        $endDate = now();
    
        // Determine the start and end date based on the filter
        switch ($filter) {
            case '7d':
                $startDate = now()->subDays(6); // Start 6 days ago, including today
                break;
            case '4w':
                $startDate = now()->subWeeks(3)->startOfWeek(); // Start from the beginning of 3 weeks ago
                break;
            case '3m':
                $startDate = now()->subMonths(2)->startOfMonth(); // Start from the beginning of 2 months ago
                break;
            case '1y':
                $startDate = now()->subYear()->startOfYear(); // Start from the beginning of the year one year ago
                break;
            default:
                $startDate = now()->subDays(6); // Default to 7 days, including today
        }

        $data = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
        // Ensure all days of the week are present in the result with default 0 for missing days
                $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $allDays = array_fill_keys($daysOfWeek, 0);
                foreach ($data as $day => $count) {
                    $allDays[$day] = $count;
                }
            
                // Return the result in the correct order
                $orderedResult = [];
                foreach ($daysOfWeek as $day) {
                    $orderedResult[$day] = $allDays[$day];
                }
            
        return get_success_response($orderedResult);
        // return get_success_response($data);
    }

    public function service_requests()
    {
        $filter = request()->get('filter', '7d');
        $data = $this->getFilteredData(new ServiceRequest, $filter);
        return get_success_response($data);
    }
    
    public function suppliers_items_sold()
    {
        $filter = request()->get('filter', '7d');
        $query = Order::where('seller_id', auth()->id());
        $startDate = now();
        $endDate = now();
    
        // Determine the start and end date based on the filter
        switch ($filter) {
            case '7d':
                $startDate = now()->subDays(6); // Start 6 days ago, including today
                break;
            case '4w':
                $startDate = now()->subWeeks(3)->startOfWeek(); // Start from the beginning of 3 weeks ago
                break;
            case '3m':
                $startDate = now()->subMonths(2)->startOfMonth(); // Start from the beginning of 2 months ago
                break;
            case '1y':
                $startDate = now()->subYear()->startOfYear(); // Start from the beginning of the year one year ago
                break;
            default:
                $startDate = now()->subDays(6); // Default to 7 days, including today
        }
    
        // Fetch data from the model grouped by day of the week
        $data = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DAYNAME(created_at) as day_of_week, COUNT(*) as count")
            ->groupBy('day_of_week')
            ->get()
            ->pluck('count', 'day_of_week')
            ->toArray();
    
        // Ensure all days of the week are present in the result with default 0 for missing days
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $allDays = array_fill_keys($daysOfWeek, 0);
        foreach ($data as $day => $count) {
            $allDays[$day] = $count;
        }
    
        // Return the result in the correct order
        $orderedResult = [];
        foreach ($daysOfWeek as $day) {
            $orderedResult[$day] = $allDays[$day];
        }
    
        return get_success_response($orderedResult);
    }
    

    public function products_count()
    {
        $totalProducts = Product::where('seller_id', auth()->id())->count();
        $buyable = Product::where('is_leasable', false)->where('seller_id', auth()->id())->count();
        $rentable = Product::where('is_leasable', true)->where('seller_id', auth()->id())->count();

        return get_success_response([
            'total_products' => $totalProducts,
            'buyable' => $buyable,
            'rentable' => $rentable,
        ]);
    }

    public function service_request_counts()
    {
        $query = ServiceRequest::where('_account_type', active_role())->where(function ($q) {
            $q->where('user_id', auth()->id())
                ->orWhere('approved_providers_id', auth()->id())
                ->orWhere('approved_artisan_id', auth()->id());
        });
    
        // Count for service requests where the logged-in user is associated
        $totalServiceRequests = $query->count();
        $completedServiceRequests = $query->where('request_status', 'Completed')->count();
    
        // Calculate the percentage of completed requests
        $percentageCompleted = $totalServiceRequests > 0 
            ? ($completedServiceRequests / $totalServiceRequests) * 100 
            : 0;
            
        $artisans = Artisans::where('service_provider_id', auth()->id())->latest()->count();
    
        return get_success_response([
            'total_service_requests' => $totalServiceRequests,
            'completed_service_requests' => $completedServiceRequests,
            'percentage_completed' => round($percentageCompleted, 2),
            'total_artisans' => $artisans ?? 0
        ]);
    }
}