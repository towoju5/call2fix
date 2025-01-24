@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">Subscriptions</h1>
    <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary mb-3">Add New Subscription</a>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Plan</th>
                <th>Model ID</th>
                <th>Paid</th>
                <th>Recurring</th>
                <th>Starts On</th>
                <th>Expires On</th>
                <!-- <th>Actions</th> -->
            </tr>
        </thead>
        <tbody>
            @foreach($subscriptions as $subscription)
            <tr>
                <td>{{ $subscription->model?->first_name.' '.$subscription->model?->last_name }}</td>
                <td>{{ $subscription->plan->name }}</td>
                <td>{{ $subscription->model_id }}</td>
                <td>{{ $subscription->is_paid ? 'Yes' : 'No' }}</td>
                <td>{{ $subscription->is_recurring ? 'Yes' : 'No' }}</td>
                <td>{{ $subscription->starts_on }}</td>
                <td>{{ $subscription->expires_on }}</td>
                <!-- <td>
                    <a href="{{ route('admin.subscriptions.edit', $subscription->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td> -->
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
