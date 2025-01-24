@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Track Order</h1>

    @if($trackingDetails)
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tracking Details for Order ID: {{ $order->order_id }}</h5>
                <p><strong>Status:</strong> {{ $trackingDetails['status'] }}</p>
                <p><strong>Location:</strong> {{ $trackingDetails['location'] ?? 'N/A' }}</p>
                <p><strong>Estimated Arrival:</strong> {{ $trackingDetails['estimated_arrival'] ?? 'N/A' }}</p>
                <!-- Add more tracking details as needed -->
            </div>
        </div>
    @else
        <p>Tracking information not available for this order.</p>
    @endif

    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary mt-3">Back to Orders</a>
</div>
@endsection
