@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Create New Order</h1>

    <form action="{{ route('admin.orders.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="user_id" class="form-label">User ID</label>
            <input type="text" class="form-control" id="user_id" name="user_id" required>
        </div>

        <div class="mb-3">
            <label for="seller_id" class="form-label">Seller ID</label>
            <input type="text" class="form-control" id="seller_id" name="seller_id" required>
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <input type="text" class="form-control" id="status" name="status" required>
        </div>

        <div class="mb-3">
            <label for="order_id" class="form-label">Order ID</label>
            <input type="text" class="form-control" id="order_id" name="order_id" required>
        </div>

        <div class="mb-3">
            <label for="total_price" class="form-label">Total Price</label>
            <input type="number" class="form-control" id="total_price" name="total_price" required step="0.01">
        </div>

        <button type="submit" class="btn btn-primary">Create Order</button>
    </form>
</div>
@endsection
