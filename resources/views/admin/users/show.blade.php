@extends('layouts.app')

@section('title', 'User Details')
@section('content')
    <div class="container">
        <h1>User Details: {{ $user->name }}</h1>

        <div class="row mb-3">
            <div class="col-md-6">
                <h3>Wallet Balance: ${{ number_format($user->wallet_balance, 2) }}</h3>
            </div>
            <div class="col-md-6">
                <form action="{{ route('admin.users.topup', $user->id) }}" method="POST" class="mb-2">
                    @csrf
                    <div class="input-group">
                        <input type="number" name="amount" class="form-control" placeholder="Amount" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Top Up</button>
                        </div>
                    </div>
                </form>
                <form action="{{ route('admin.users.debit', $user->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="number" name="amount" class="form-control" placeholder="Amount" required>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-warning">Debit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <ul class="nav nav-tabs" id="userTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="transactions-tab" data-bs-toggle="tab" href="#transactions" role="tab">Transactions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="service-requests-tab" data-bs-toggle="tab" href="#service-requests" role="tab">Service Requests</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="products-tab" data-bs-toggle="tab" href="#products" role="tab">Products</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="orders-tab" data-bs-toggle="tab" href="#orders" role="tab">Orders</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="wallets-tab" data-bs-toggle="tab" href="#wallets" role="tab">Wallets</a>
            </li>
        </ul>

        <div class="tab-content" id="userTabsContent">
            <div class="tab-pane fade show active" id="transactions" role="tabpanel">
                <h3>Recent Transactions</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="transactions-table-body">
                                    @foreach ($transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->id }}</td>
                                            <td>${{ number_format($transaction->amount, 2) }}</td>
                                            <td>{{ ucfirst($transaction->type) }}</td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <button id="load-more-transactions" class="btn btn-primary my-2">Load More</button>
            </div>

            <div class="tab-pane fade" id="service-requests" role="tabpanel">
                <h3>Service Requests</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="service-requests-table-body">
                                    @foreach ($serviceRequests as $request)
                                        <tr>
                                            <td>{{ $request->id }}</td>
                                            <td>{{ $request->service_name }}</td>
                                            <td>{{ $request->status }}</td>
                                            <td>{{ $request->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <button id="load-more-service-requests" class="btn btn-primary my-2">Load More</button>
            </div>

            <div class="tab-pane fade" id="products" role="tabpanel">
                <h3>Products</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="products-table-body">
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>{{ $product->id }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>${{ number_format($product->price, 2) }}</td>
                                            <td>{{ $product->stock }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <button id="load-more-products" class="btn btn-primary my-2">Load More</button>
            </div>

            <div class="tab-pane fade" id="orders" role="tabpanel">
                <h3>Orders</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="orders-table-body">
                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>${{ number_format($order->total, 2) }}</td>
                                            <td>{{ $order->status }}</td>
                                            <td>{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <button id="load-more-orders" class="btn btn-primary my-2">Load More</button>
            </div>

            <div class="tab-pane fade" id="wallets" role="tabpanel">
                <h3>Wallets</h3>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Curreny</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wallets as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->name }}</td>
                                            <td>{{ $order->meta['symbol'].number_format($order->balance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let transactionsPage = 1;
            let serviceRequestsPage = 1;
            let productsPage = 1;
            let ordersPage = 1;

            $('#load-more-transactions').click(function() {
                loadMore('transactions', ++transactionsPage);
            });

            $('#load-more-service-requests').click(function() {
                loadMore('service-requests', ++serviceRequestsPage);
            });

            $('#load-more-products').click(function() {
                loadMore('products', ++productsPage);
            });

            $('#load-more-orders').click(function() {
                loadMore('orders', ++ordersPage);
            });


            function loadMore(type, page) {
                $.ajax({
                    url: `/admin/users/{{ $user->id }}/${type}?page=${page}`,
                    method: 'GET',
                    success: function(response) {
                        const data = response.data;
                        let html = '';

                        switch (type) {
                            case 'transactions':
                                data.forEach(item => {
                                    html += `
                                    <tr>
                                        <td>${item.id}</td>
                                        <td>${parseFloat(item.amount).toFixed(2)}</td>
                                        <td>${item.type.charAt(0).toUpperCase() + item.type.slice(1)}</td>
                                        <td>${item.description}</td>
                                        <td>${item.created_at}</td>
                                    </tr>
                                `;
                                });
                                $('#transactions-table-body').append(html);
                                break;
                            case 'service-requests':
                                data.forEach(item => {
                                    html += `
                                    <tr>
                                        <td>${item.id}</td>
                                        <td>${item.service_name}</td>
                                        <td>${item.status}</td>
                                        <td>${item.created_at}</td>
                                    </tr>
                                `;
                                });
                                $('#service-requests-table-body').append(html);
                                break;
                            case 'products':
                                data.forEach(item => {
                                    html += `
                                    <tr>
                                        <td>${item.id}</td>
                                        <td>${item.name}</td>
                                        <td>${parseFloat(item.price).toFixed(2)}</td>
                                        <td>${item.stock}</td>
                                    </tr>
                                `;
                                });
                                $('#products-table-body').append(html);
                                break;
                            case 'orders':
                                data.forEach(item => {
                                    html += `
                                    <tr>
                                        <td>${item.id}</td>
                                        <td>${parseFloat(item.total).toFixed(2)}</td>
                                        <td>${item.status}</td>
                                        <td>${item.created_at}</td>
                                    </tr>
                                `;
                                });
                                $('#orders-table-body').append(html);
                                break;
                        }

                        if (response.next_page_url === null) {
                            $(`#load-more-${type}`).hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
        });
    </script>
@endpush