@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Order Details</h1>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="order-tab" data-bs-toggle="tab" href="#order" role="tab">Order Info</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="buyer-tab" data-bs-toggle="tab" href="#buyer" role="tab">Buyer Info</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="seller-tab" data-bs-toggle="tab" href="#seller" role="tab">Seller Info</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="product-tab" data-bs-toggle="tab" href="#product" role="tab">Product Info</a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <!-- Order Info Tab -->
                <div class="tab-pane fade show active" id="order" role="tabpanel">
                    <h5 class="card-title">Order ID: {{ $order->order_id }}</h5>
                    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                    <p><strong>Delivery Type:</strong> {{ ucfirst(str_replace('_', ' ', $order->delivery_type)) }}</p>
                    <p><strong>Total Price:</strong> ${{ number_format($order->total_price, 2) }}</p>
                    <p><strong>Delivery Address:</strong> {{ $order->delivery_address ?? 'N/A' }}</p>
                    <p><strong>Additional Info:</strong> {{ $order->additional_info ?? 'N/A' }}</p>
                    <p><strong>Estimated Delivery:</strong> {{ $order->estimated_delivery ? $order->estimated_delivery->format('Y-m-d H:i') : 'N/A' }}</p>
                </div>

                <!-- Buyer Info Tab -->
                <div class="tab-pane fade" id="buyer" role="tabpanel">
                    <h5 class="card-title">Buyer Information</h5>
                    <p><strong>Name:</strong> {{ $order->user->first_name .' '.$order->user->last_name ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ $order->user->phone ?? 'N/A' }}</p>
                </div>

                <!-- Seller Info Tab -->
                <div class="tab-pane fade" id="seller" role="tabpanel">
                    <h5 class="card-title">Seller Information</h5>
                    <p><strong>Name:</strong> {{ $order->seller->first_name .' '.$order->seller->last_name ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $order->seller->email ?? 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ $order->seller->phone ?? 'N/A' }}</p>
                </div>

                <!-- Product Info Tab -->
                <div class="tab-pane fade" id="product" role="tabpanel">
                    <h5 class="card-title">Product Details</h5>
                    <div class="border-bottom mb-3 pb-3">
                        <div class="row">
                            <div class="col-md-4">
                                @if($order->product->product_image)
                                <img src="{{ $order->product->product_image[0] ?? '' }}"
                                    class="img-fluid rounded"
                                    alt="{{ $order->product->name }}">
                                @endif
                            </div>
                            <div class="col-md-8">
                                <h6><strong>Product Name:</strong> {{ $order->product->name ?? 'N/A' }}</h6>
                                <p><strong>Description:</strong> {{ $order->product->description ?? 'N/A' }}</p>
                                <p><strong>SKU:</strong> {{ $order->product->sku }}</p>
                                <p><strong>Category:</strong> {{ $order->product->category->name ?? 'N/A' }}</p>
                                <p><strong>Price:</strong> {{ $order->product->product_currency }} {{ number_format($order->product->price, 2) }}</p>
                                <p><strong>Quantity Ordered:</strong> {{ $order->quantity }}</p>
                                <p><strong>Subtotal:</strong> {{ $order->product->product_currency }} {{ number_format($order->quantity * $order->product->price, 2) }}</p>

                                <div class="mt-3">
                                    <h6>Product Details:</h6>
                                    <p><strong>Weight:</strong> {{ $order->product->weight ?? 'N/A' }}</p>
                                    <p><strong>Dimensions:</strong> {{ $order->product->dimensions ?? 'N/A' }}</p>
                                    <p><strong>Current Stock:</strong> {{ $order->product->stock }}</p>
                                    <p><strong>Leasable:</strong> {{ $order->product->is_leasable ? 'Yes' : 'No' }}</p>
                                </div>

                                <div class="mt-3">
                                    <h6>Location Information:</h6>
                                    <p><strong>Location:</strong> {{ $order->product->product_location ?? 'N/A' }}</p>
                                    <p><strong>Coordinates:</strong>
                                        @if($order->product->product_latitude && $order->product->product_longitude)
                                        {{ $order->product->product_latitude }}, {{ $order->product->product_longitude }}
                                        @else
                                        N/A
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary mt-3">Back to Orders</a>
</div>
@endsection


@section('styles')
<style>
    .product-image {
        max-height: 200px;
        object-fit: cover;
    }
</style>
@endsection
