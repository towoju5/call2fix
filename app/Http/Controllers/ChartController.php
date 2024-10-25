<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServiceRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;

class ChartController extends Controller
{
    public function orders()
    {
        $filter = request()->get('filter', '7');
        $query = WalletTransaction::query();

        switch ($filter) {
            case '7d':
                $query->where('created_at', '>=', now()->subDays(7));
                $groupBy = 'day';
                break;
            case '4w':
                $query->where('created_at', '>=', now()->subWeeks(4));
                $groupBy = 'week';
                break;
            case '3m':
                $query->where('created_at', '>=', now()->subMonths(3));
                $groupBy = 'month';
                break;
            case '6m':
                $query->where('created_at', '>=', now()->subMonths(6));
                $groupBy = 'month';
                break;
            case '1y':
                $query->where('created_at', '>=', now()->subYear());
                $groupBy = 'month';
                break;
            default:
                $query->where('created_at', '>=', now()->subDays(7));
                $groupBy = 'day';
        }

        $chart = [
            'chart_title' => 'Wallet Transactions',
            'report_type' => 'group_by_date',
            'model' => $query,
            'group_by_field' => 'created_at',
            'group_by_period' => $groupBy,
            'chart_type' => 'bar',
        ];

        $chart1 = new LaravelChart($chart);

        return [
            'chart' => $chart1->getDatasets(),
        ];

    }

    public function service_requests()
    {
        $filter = request()->get('filter', '7');
        $query = ServiceRequest::query();

        switch ($filter) {
            case '7d':
                $query->where('created_at', '>=', now()->subDays(7));
                $groupBy = 'day';
                break;
            case '4w':
                $query->where('created_at', '>=', now()->subWeeks(4));
                $groupBy = 'week';
                break;
            case '3m':
                $query->where('created_at', '>=', now()->subMonths(3));
                $groupBy = 'month';
                break;
            case '6m':
                $query->where('created_at', '>=', now()->subMonths(6));
                $groupBy = 'month';
                break;
            case '1y':
                $query->where('created_at', '>=', now()->subYear());
                $groupBy = 'month';
                break;
            default:
                $query->where('created_at', '>=', now()->subDays(7));
                $groupBy = 'day';
        }

        $chart = [
            'chart_title' => 'Wallet Transactions',
            'report_type' => 'group_by_date',
            'model' => $query,
            'group_by_field' => 'created_at',
            'group_by_period' => $groupBy,
            'chart_type' => 'bar',
        ];

        $chart1 = new LaravelChart($chart);

        return [
            'chart' => $chart1->getDatasets(),
        ];

    }

    public function suppliers_items_sold()
    {
        $filter = request()->get('filter', '7');
        $query = Order::where('seller_id', auth()->id());
        switch ($filter) {
            case '7d':
                $query->where('created_at', '>=', now()->subDays(7));
                $groupBy = 'day';
                break;
            case '4w':
                $query->where('created_at', '>=', now()->subWeeks(4));
                $groupBy = 'week';
                break;
            case '3m':
                $query->where('created_at', '>=', now()->subMonths(3));
                $groupBy = 'month';
                break;
            case '6m':
                $query->where('created_at', '>=', now()->subMonths(6));
                $groupBy = 'month';
                break;
            case '1y':
                $query->where('created_at', '>=', now()->subYear());
                $groupBy = 'month';
                break;
            default:
                $query->where('created_at', '>=', now()->subDays(7));
                $groupBy = 'day';
        }

        $chart = [
            'chart_title' => 'Wallet Transactions',
            'report_type' => 'group_by_date',
            'model' => $query,
            'group_by_field' => 'created_at',
            'group_by_period' => $groupBy,
            'chart_type' => 'bar',
        ];

        $chart1 = new LaravelChart($chart);

        return [
            'chart' => $chart1->getDatasets(),
        ];
    }
}
