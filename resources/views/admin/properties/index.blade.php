@extends('layouts.app')

@section('title', 'Manage Properties')
@section('content')
<div class="container">
    <h1>Properties</h1>

    <!-- Search and Filter Form -->
    <form method="GET" action="{{ route('admin.properties.index') }}">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" name="user_id" class="form-control" placeholder="Search by User ID" value="{{ request('user_id') }}">
            </div>
            {{-- <div class="col-md-4">
                <select name="_account_type" class="form-control">
                    <option value="">Filter by Property Type</option>
                    <option value="type1" {{ request('_account_type') == 'type1' ? 'selected' : '' }}>Type 1</option>
                    <option value="type2" {{ request('_account_type') == 'type2' ? 'selected' : '' }}>Type 2</option>
                </select>
            </div> --}}
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Property Name</th>
                <th>Address</th>
                <th>Type</th>
                <th>Nearest Landmark</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($properties as $property)
            <tr>
                <td>{{ $property->property_name }}</td>
                <td>{{ $property->property_address }}</td>
                <td>{{ $property->property_type }}</td>
                <td>{{ $property->property_nearest_landmark }}</td>
                <td>
                    <a href="{{ route('admin.properties.show', $property->id) }}" class="btn btn-info btn-sm">View</a>
                    <a href="{{ route('admin.properties.edit', $property->id) }}" class="btn btn-warning btn-sm">Edit</a>
                    <form action="{{ route('admin.properties.destroy', $property->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $properties->links() }}
</div>
@endsection
