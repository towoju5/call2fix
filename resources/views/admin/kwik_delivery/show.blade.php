@extends('layouts.app')

@section('title', 'View Delivery details')

@section('content')
    <div class="container my-4">
        <h1 class="mb-4">Delivery Details - Order ID: {{ $KwikDelivery->order_id }}</h1>

        <!-- Delivery Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Delivery Information</h5>
            </div>
            <div class="card-body">
                <!-- Seller and Customer Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Seller:</strong> {{ $KwikDelivery->seller->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Customer:</strong> {{ $KwikDelivery->customer->name }}</p>
                    </div>
                </div>

                <!-- Cost Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Estimate:</strong> ${{ number_format($KwikDelivery->estimate, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Billed:</strong> ${{ number_format($KwikDelivery->billed, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metadata Section -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Metadata</h5>
            </div>
            <div class="card-body">
                <!-- Display Metadata as a Table -->
                @if(is_array($KwikDelivery->metadata) && count($KwikDelivery->metadata) > 0)
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($KwikDelivery->metadata as $key => $value)
                                @if(is_array($value))
                                    <!-- If the value is an array, loop through the nested array -->
                                    @foreach($value as $nestedKey => $nestedValue)
                                        <tr>
                                            <td>{{ $key }} > {{ $nestedKey }}</td>
                                            <td>{{ is_array($nestedValue) ? json_encode($nestedValue) : $nestedValue }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>{{ $key }}</td>
                                        <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>No metadata available.</p>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4">
            <form action="{{ route('admin.kwik_delivery.destroy', $KwikDelivery->id) }}" method="POST" class="d-inline-block">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this delivery?')">
                    Delete Delivery
                </button>
            </form>

            <a href="{{ route('admin.kwik_delivery.index') }}" class="btn btn-secondary ml-3">
                Back to Deliveries
            </a>
        </div>
    </div>
@endsection
