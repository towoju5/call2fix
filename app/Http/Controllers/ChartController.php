<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;
use Towoju5\Wallet\Models\Wallet;

class ChartController extends Controller
{
    // Shared method for filtering and fetching data
    protected function getFilteredData($model, $filter)
    {
        $query = $model->query();
        $groupBy = 'day';
        $startDate = now();
        $endDate = now();

        // Determine the start and end date based on the filter
        switch ($filter) {
            case '7d':
                $startDate = now()->subDays(7);
                $groupBy = 'day';
                break;
            case '4w':
                $startDate = now()->subWeeks(4);
                $groupBy = 'week';
                break;
            case '3m':
                $startDate = now()->subMonths(3);
                $groupBy = 'month';
                break;
            case '6m':
                $startDate = now()->subMonths(6);
                $groupBy = 'month';
                break;
            case '1y':
                $startDate = now()->subYear();
                $groupBy = 'month'; // Group by months for a 1-year filter
                break;
            default:
                $startDate = now()->subDays(7);
                $groupBy = 'day';
        }

        // Fetch data from the model
        $data = $query->where('created_at', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Calculate the number of days between startDate and now
        $dateRange = $endDate->diffInDays($startDate);

        // If grouping by months for the 1-year filter
        if ($groupBy === 'month') {
            // Get the first and last day of each month for the past year
            $currentDate = $startDate->copy();
            $allDates = [];
            $step = 30; // Approximate days per month

            for ($i = 0; $i < 12; $i++) {
                $formattedDate = $currentDate->format('F Y'); // Format as 'Month Year' for monthly group
                $allDates[$formattedDate] = $data[$formattedDate] ?? 0; // Use 0 if date not in data
                $currentDate->addMonth(); // Move to the next month
                if ($currentDate->gt(now())) {
                    break;
                }
            }
        } else {
            // Group by day or week for other filters
            $step = max(floor($dateRange / 12), 1);
            $allDates = [];
            $currentDate = $startDate->copy();

            for ($i = 0; $i < 12; $i++) {
                $formattedDate = $currentDate->format('l'); // Format as 'Day of Week' for daily group (e.g., 'Monday')
                $allDates[$formattedDate] = $data[$formattedDate] ?? 0; // Use 0 if date not in data
                $currentDate->addDays($step);
                // Ensure we don't go beyond the current date
                if ($currentDate->gt(now())) {
                    break;
                }
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

    public function suppliers_items_sold()
    {
        $filter = request()->get('filter', '7d');
        $query = Order::where('seller_id', auth()->id());
        $groupBy = 'day';
        $startDate = now();
        $endDate = now();

        // Determine the start and end date based on the filter
        switch ($filter) {
            case '7d':
                $startDate = now()->subDays(7);
                $groupBy = 'day';
                break;
            case '4w':
                $startDate = now()->subWeeks(4);
                $groupBy = 'week';
                break;
            case '3m':
                $startDate = now()->subMonths(3);
                $groupBy = 'month';
                break;
            case '6m':
                $startDate = now()->subMonths(6);
                $groupBy = 'month';
                break;
            case '1y':
                $startDate = now()->subYear();
                $groupBy = 'month'; // Group by months for a 1-year filter
                break;
            default:
                $startDate = now()->subDays(7);
                $groupBy = 'day';
        }

        // Fetch data from the model
        $data = $query->where('created_at', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d') as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Calculate the number of days between startDate and now
        $dateRange = $endDate->diffInDays($startDate);

        // If grouping by months for the 1-year filter
        if ($groupBy === 'month') {
            // Get the first and last day of each month for the past year
            $currentDate = $startDate->copy();
            $allDates = [];
            $step = 30; // Approximate days per month

            for ($i = 0; $i < 12; $i++) {
                $formattedDate = $currentDate->format('F Y'); // Format as 'Month Year' for monthly group
                $allDates[$formattedDate] = $data[$formattedDate] ?? 0; // Use 0 if date not in data
                $currentDate->addMonth(); // Move to the next month
                if ($currentDate->gt(now())) {
                    break;
                }
            }
        } else {
            // Group by day or week for other filters
            $step = max(floor($dateRange / 12), 1);
            $allDates = [];
            $currentDate = $startDate->copy();

            for ($i = 0; $i < 12; $i++) {
                $formattedDate = $currentDate->format('l'); // Format as 'Day of Week' for daily group (e.g., 'Monday')
                $allDates[$formattedDate] = $data[$formattedDate] ?? 0; // Use 0 if date not in data
                $currentDate->addDays($step);
                // Ensure we don't go beyond the current date
                if ($currentDate->gt(now())) {
                    break;
                }
            }
        }

        return get_success_response($allDates);
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
}
