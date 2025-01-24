@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('content')
    <div class="container-fluid">
        <!-- Topbar -->
        <div class="d-flex justify-content-between align-items-center my-3">
            <h1 class="h3">Admin Dashboard</h1>
        </div>

        <!-- Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}" class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Users</h5>
                        <h3>{{ number_format($user_count) }}</h3>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}?roles=artisan" class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Artisans</h5>
                        <h3>{{ number_format($total_artisans) }}</h3>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}?roles=providers" class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Providers</h5>
                        <h3>{{ number_format($total_providers) }}</h3>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}?roles=co-operate_accounts" class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Coporate Account</h5>
                        <h3>{{ number_format($total_cooperate_accounts) }}</h3>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}?roles=total_suppliers" class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Suppliers</h5>
                        <h3>{{ number_format($total_suppliers) }}</h3>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}?roles=private_accounts" class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Private Account</h5>
                        <h3>{{ number_format($total_private_accounts) }}</h3>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}?roles=affiliates" class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Affiliates</h5>
                        <h3>{{ number_format($total_affiliates) }}</h3>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.users.index') }}?roles=department" class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Department</h5>
                        <h3>{{ number_format($total_department) }}</h3>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Service Request</h5>
                        <h3>{{ number_format($total_service_requests) }}</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Merchants <small>(Providers & Suppliers)</small></h5>
                        <h3>{{ number_format($total_merchants) }}</h3>
                    </div>
                </div>
            </div>


            @foreach ($profits as $type => $profit)
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">Sales Profit {{ ucwords(str_replace('_', ' ', $type)) }}</h5>
                            <h3>{{ $profit }}</h3>
                        </div>
                    </div>
                </div>
            @endforeach
            @foreach ($total_sales as $type => $sales)
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">Total Sales {{ ucwords(str_replace('_', ' ', $type)) }}</h5>
                            <h3>{{ $sales }}</h3>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <h5 class="card-title">Service Count</h5>
                        <h3>{{ $service_count }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Profit Over Time</h5>
                        <canvas id="profitChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Orders by Type</h5>
                        <canvas id="orderTypesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Spending Customers -->
            <div class="col-md-6 mb-4">
                <div class="bg-white rounded-lg shadow p-4 card">
                    <h4 class="mb-4">Top Spending Customers</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">#</th>
                                    <th class="whitespace-nowrap">Name</th>
                                    <th class="whitespace-nowrap">Email</th>
                                    <th class="whitespace-nowrap">Transactions</th>
                                    <th class="whitespace-nowrap">Total Spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top_spending_customers->take(5) as $index => $customer)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $customer->username }}</td>
                                        <td>{{ $customer->email }}</td>
                                        <td>{{ $customer->transactions_count }}</td>
                                        <td>{{ number_format($customer->total_spent, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Earning Providers -->
            <div class="col-md-6 mb-4">
                <div class="bg-white rounded-lg shadow p-4 card">
                    <h4 class="mb-4">Top Earning Providers</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Transactions</th>
                                    <th>Total Earned</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top_earning_providers->take(5) as $index => $provider)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $provider->name }}</td>
                                        <td>{{ $provider->email }}</td>
                                        <td>{{ $provider->transactions_count }}</td>
                                        <td>{{ number_format($provider->transactions_sum_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <!-- Top Selling Category -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Top 5 selling categories</h5>
                        <a href="{{ route('admin.service-requests.index') }}" class="btn btn-primary">View All</a>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Category Name</th>
                                    <th>Order count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($category_by_orders->take(5) as $item)
                                    <tr>
                                        <td>{{ $item->category_name }}</td>
                                        <td><span class="badge bg-success">{{ $item->order_count }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Selling Services -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Top 5 selling service</h5>
                        <a href="{{ route('admin.service-requests.index') }}" class="btn btn-primary">View All</a>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Service Category Name</th>
                                    <th>Order count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($top_services_by_orders->take(5) as $item)
                                    <tr>
                                        <td>{{ $item->service_name }}</td>
                                        <td><span class="badge bg-success">{{ $item->order_count }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <!-- Top Selling Services -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Top Suppliers</h5>
                        <a href="{{ route('admin.service-requests.index') }}" class="btn btn-primary">View All</a>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Customer Name</th>
                                    {{-- <th>Email</th> --}}
                                    <th>Txn. count</th>
                                    <th>Total spent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($top_earning_suppliers->take(5) as $item)
                                    <tr>
                                        <td>{{ "$item->first_name $item->last_name" }}</td>
                                        {{-- <td>{{ $item->email }}</td> --}}
                                        <td>{{ $item->transactions_count }}</td>
                                        <td><span class="badge bg-success">{{ $item->total_spent }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Customers -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Customers</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table">
                            @foreach ($new_customers->take(5) as $customer)
                                <tr>
                                    <td>
                                        <h6 class="mb-0">{{ $customer->first_name }} {{ $customer->last_name }}</h6>
                                        <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <h6 class="mb-0">{{ $customer->email }}</h6>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Selling Services -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Top Selling Services</h5>
                        <a href="#" class="btn btn-primary">View All</a>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Star Refrigerator</td>
                                    <td>$1200</td>
                                    <td>Paid</td>
                                    <td><span class="badge bg-success">Delivered</span></td>
                                </tr>
                                <tr>
                                    <td>Adidas Shoes</td>
                                    <td>$620</td>
                                    <td>Due</td>
                                    <td><span class="badge bg-warning">In Progress</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Customers -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Customers</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table">
                            @foreach ($new_customers->take(5) as $customer)
                                <tr>
                                    <td>
                                        <h6 class="mb-0">{{ $customer->first_name }} {{ $customer->last_name }}</h6>
                                        <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <h6 class="mb-0">{{ $customer->email }}</h6>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Profit Chart
            var profitCtx = document.getElementById('profitChart').getContext('2d');
            var profitChart = new Chart(profitCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($profit_chart ?? [])) !!},
                    datasets: [{
                        label: 'Profit',
                        data: {!! json_encode(array_values($profit_chart ?? [])) !!},
                        borderColor: 'rgb(59, 130, 246)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Profit Over Time',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });

            // Order Types Chart
            var orderTypesCtx = document.getElementById('orderTypesChart').getContext('2d');
            var orderTypesChart = new Chart(orderTypesCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($order_types_chart ?? [])) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($order_types_chart ?? [])) !!},
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(249, 115, 22)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Orders by Type',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
