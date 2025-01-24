@extends('layouts.app')

@section('title', 'Manage Orders')

@section('content')
<div class="container">
    <h1 class="mb-4">Manage Orders</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Seller ID</th>
                <th>Status</th>
                <th>Delivery Type</th>
                <th>Total Price</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->user->first_name .' '.$order->user->last_name }}</td>
                    <td>{{ $order->seller->first_name .' '.$order->seller->last_name }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $order->delivery_type)) }}</td>
                    <td>${{ number_format($order->total_price, 2) }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('admin.orders.track', $order->id) }}" class="btn btn-primary btn-sm">Track</a>
                        <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No orders found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $orders->links() }}
</div>
@endsection
