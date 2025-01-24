@extends('layouts.app')
@section('title', 'Deliveries')

@section('content')
    <div class="container my-4">
        <h1>Kwik Deliveries</h1>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Delivery List -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order ID</th>
                    <th>Seller</th>
                    <th>Customer</th>
                    <th>Estimate</th>
                    <th>Billed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceAreas as $kwikDelivery)
                    <tr>
                        <td>{{ $kwikDelivery->id }}</td>
                        <td>{{ $kwikDelivery->order_id }}</td>
                        <td>{{ $kwikDelivery->seller->name }}</td>
                        <td>{{ $kwikDelivery->customer->name }}</td>
                        <td>${{ number_format($kwikDelivery->estimate, 2) }}</td>
                        <td>${{ number_format($kwikDelivery->billed, 2) }}</td>
                        <td>
                            <!-- View Button -->
                            <a href="{{ route('admin.kwik_delivery.show', $kwikDelivery->id) }}" class="btn btn-info btn-sm">
                                View
                            </a>

                            <!-- Delete Button -->
                            <form action="{{ route('admin.kwik_delivery.destroy', $kwikDelivery->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this delivery?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        {{ $serviceAreas->links() }}
    </div>
@endsection
