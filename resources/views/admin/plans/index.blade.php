@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mt-4">Plans</h1>
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary mb-3">Add New Plan</a>
    
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Currency</th>
                <th>Duration (Days)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
            <tr>
                <td>{{ $plan->name }}</td>
                <td>{{ $plan->price }}</td>
                <td>{{ $plan->currency }}</td>
                <td>{{ $plan->duration }}</td>
                <td>
                    <a href="{{ route('admin.plans.edit', $plan->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('admin.plans.destroy', $plan->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
