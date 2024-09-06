@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('content')
    <div class="topbar">
        <div class="toggle">
            <ion-icon name="menu-outline"></ion-icon>
        </div>

        <div class="search">
            <label>
                <input type="text" placeholder="Search here">
                <ion-icon name="search-outline"></ion-icon>
            </label>
        </div>

        <div class="user">
            <img src="assets/imgs/customer01.jpg" alt="">
        </div>
    </div>

    <!-- ======================= Cards ================== -->
    <div class="cardBox">
        <div class="card">
            <div>
                <div class="numbers">{{ number_format($user_count)}}</div>
                <div class="cardName">Total users</div>
            </div>

            <div class="iconBx">
                <ion-icon name="eye-outline"></ion-icon>
            </div>
        </div>

        @foreach ($profits as $type => $profit)
        <div class="card">
            <div>
                <div class="numbers">{{ $profit }}</div>
                <div class="cardName">Sales Profit {{ ucwords(str_replace('_', ' ', $type)) }}</div>
            </div>

            <div class="iconBx">
                <ion-icon name="cart-outline"></ion-icon>
            </div>
        </div>            
        @endforeach

        @foreach ($total_sales as $type => $sales)
        <div class="card">
            <div>
                <div class="numbers">{{ $sales }}</div>
                <div class="cardName">Total sales {{ ucwords(str_replace('_', ' ', $type)) }}</div>
            </div>

            <div class="iconBx">
                <ion-icon name="cart-outline"></ion-icon>
            </div>
        </div>            
        @endforeach

        <div class="card">
            <div>
                <div class="numbers">{{ $service_count }}</div>
                <div class="cardName">Service Count</div>
            </div>

            <div class="iconBx">
                <ion-icon name="chatbubbles-outline"></ion-icon>
            </div>
        </div>

        <div class="card">
            <div>
                <div class="numbers">$7,842</div>
                <div class="cardName">Earning</div>
            </div>

            <div class="iconBx">
                <ion-icon name="cash-outline"></ion-icon>
            </div>
        </div>
    </div>
    
    <!-- ================ Chart js ================= -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 my-5 mx-2 lg:mx-4">
        <div class="bg-white rounded-lg shadow p- card">
            <canvas id="profitChart"></canvas>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <canvas id="orderTypesChart"></canvas>
        </div>
    </div>


    <!-- ================ Order Details List ================= -->
    <div class="details">
        <div class="recentOrders">
            <div class="cardHeader">
                <h2>Top Selling Services</h2>
                <a href="#" class="btn">View All</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <td>Name</td>
                        <td>Price</td>
                        <td>Payment</td>
                        <td>Status</td>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Star Refrigerator</td>
                        <td>$1200</td>
                        <td>Paid</td>
                        <td><span class="status delivered">Delivered</span></td>
                    </tr>
                    <tr>
                        <td>Addidas Shoes</td>
                        <td>$620</td>
                        <td>Due</td>
                        <td><span class="status inProgress">In Progress</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ================= New Customers ================ -->
        <div class="hidden recentCustomers">
            <div class="cardHeader">
                <h2>Recent Customers</h2>
            </div>

            <table>
                @foreach ($new_customers as $customer)
                <tr>
                    <td width="60px">
                        <div class="imgBx">
                            <img src="{{ asset($customer->photo ?? 'assets/imgs/customer02.jpg') }}" alt="{{ $customer->name }} profile image">
                        </div>
                    </td>
                    <td>
                        <h4>{{ $customer->name }} <br> <span>{{ $customer->created_at->diffForHumans() }}</span></h4>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>

    
@endsection



@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"></script>
<script>
    // Profit Chart
    var profitCtx = document.getElementById('profitChart').getContext('2d');
    var profitChart = new Chart(profitCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($profit_chart)) !!},
            datasets: [{
                label: 'Profit',
                data: {!! json_encode(array_values($profit_chart)) !!},
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
            labels: {!! json_encode(array_keys($order_types_chart)) !!},
            datasets: [{
                data: {!! json_encode(array_values($order_types_chart)) !!},
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
</script>
@endpush