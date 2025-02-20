<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Artisans;
use App\Models\ServiceRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Towoju5\Wallet\Models\Wallet;

class ChartController extends Controller
{
    // Shared method for filtering and fetching data
    public function getFilteredData($model, $filter)
    {
        $query = $model->query();
        $groupBy = 'day';
        $startDate = now();
        $endDate = now();
    
        // Determine the start and end date based on the filter
        switch ($filter) {
            case '7d':
                $startDate = now()->subDays(6); // Start from 6 days ago to include today
                $groupBy = 'day';
                break;
            case '4w':
                $startDate = now()->subWeeks(3)->startOfWeek(); // Start 3 weeks ago, including this week
                $groupBy = 'week';
                break;
            case '3m':
                $startDate = now()->subMonths(2)->startOfMonth(); // Start 2 months ago, including this month
                $groupBy = 'month';
                break;
            case '6m':
                $startDate = now()->subMonths(5)->startOfMonth(); // Start 5 months ago
                $groupBy = 'month';
                break;
            case '1y':
                $startDate = now()->subYear()->startOfMonth()->addMonth(); // Start from Dec last year
                $groupBy = 'month';
                break;
            default:
                $startDate = now()->subDays(6);
                $groupBy = 'day';
        }
    
        // Fetch data from the model
        $data = $query->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    
        // Prepare a date range with zero counts for missing dates
        $allDates = [];
        $currentDate = $startDate->copy();
    
        if ($groupBy === 'day') {
            // Loop through the days and label with short day names (Mon, Tue, etc.)
            while ($currentDate->lte($endDate)) {
                $dayOfWeek = $currentDate->format('D'); // Get the short form of day (Mon, Tue, Wed...)
                $allDates[$dayOfWeek] = $data[$currentDate->format('Y-m-d')] ?? 0;
                $currentDate->addDay();
            }
        } elseif ($groupBy === 'week') {
            // Group by weeks and label as w1, w2, w3, etc.
            $weekCount = 1; // Initialize week count as 1
            while ($currentDate->lte($endDate)) {
                $weekLabel = 'w' . $weekCount; // Format week as w1, w2, w3, etc.
                $allDates[$weekLabel] = 0; // Initialize with 0
                $currentDate->addWeek(); // Move to next week
                $weekCount++; // Increment week count
            }
    
            // Add counts from data to the correct week labels
            foreach ($data as $date => $count) {
                // Calculate the week number of the current date
                $weekNumber = now()->create($date)->weekOfYear - now()->subWeeks(3)->startOfWeek()->weekOfYear + 1;
                $weekLabel = 'w' . $weekNumber; // Format week as w1, w2, etc.
                $allDates[$weekLabel] = ($allDates[$weekLabel] ?? 0) + $count;
            }
        } elseif ($groupBy === 'month') {
            // Group by months and return month names (Jan, Feb, Mar, etc.)
            while ($currentDate->lte($endDate)) {
                $monthName = $currentDate->format('F'); // Get the full month name (January, February, etc.)
                $allDates[$monthName] = 0; // Initialize with 0
                $currentDate->addMonth();
            }
            
            // Add counts from data to the correct month labels
            foreach ($data as $date => $count) {
                $monthName = now()->create($date)->format('F'); // Get the month name
                $allDates[$monthName] = ($allDates[$monthName] ?? 0) + $count;
            }
        }
    
        return $allDates;
    }


    public function orders()
    {
        $filter = request()->get('filter', '7d');
        $data = $this->getFilteredData(new WalletTransaction, $filter);
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
        $data = $this->getFilteredData(new \Towoju5\Wallet\Models\WalletTransaction(), $filter);
        return get_success_response($data);
    }

    public function service_requests()
    {
        $filter = request()->get('filter', '7d');
        $data = $this->getFilteredData(new ServiceRequest, $filter);
        return get_success_response($data);
    }

    // public function suppliers_items_sold()
    // {
    //     $filter = request()->get('filter', '7d');
    //     $query = Order::where('seller_id', auth()->id());
    //     $data = $this->getFilteredData($query, $filter);
    //     return get_success_response($data);
    // }
    
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

    // public function service_request_counts()
    // {
    //     $query = ServiceRequest::where(function ($q) {
    //         $q->where('user_id', auth()->id())
    //             ->orWhere('approved_providers_id', auth()->id())
    //             ->orWhere('approved_artisan_id', auth()->id());
    //     });

    //     // Count for service requests where the logged-in user is associated
    //     $totalServiceRequests = $query->count();
    //     $completedServiceRequests = $query->where('request_status', 'Completed')->count();

    //     return get_success_response([
    //         'total_service_requests' => $totalServiceRequests,
    //         'completed_service_requests' => $completedServiceRequests,
    //     ]);
    // }
    
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
