@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit Order</h1>

    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="delivery_type" class="form-label">Delivery Type</label>
            <select class="form-select" id="delivery_type" name="delivery_type">
                <option value="home_delivery" {{ $order->delivery_type == 'home_delivery' ? 'selected' : '' }}>Home Delivery</option>
                <option value="pickup" {{ $order->delivery_type == 'pickup' ? 'selected' : '' }}>Pickup</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="total_price" class="form-label">Total Price</label>
            <input type="number" class="form-control" id="total_price" name="total_price" value="{{ $order->total_price }}" required step="0.01">
        </div>

        <button type="submit" class="btn btn-primary">Update Order</button>
    </form>
</div>
@endsection
