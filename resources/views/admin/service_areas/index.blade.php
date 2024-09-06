@extends('layouts.app')

@section('title', 'Service Areas')
@section('content')
    <div class="container my-3">
        <a href="{{ route('admin.service_areas.create') }}" class="btn btn-primary mb-3">Create New Service Area</a>

        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($serviceAreas as $k => $serviceArea)
                            <tr>
                                <td>{{ $k + 1 }}</td>
                                <td>{{ $serviceArea->service_area_title }}</td>
                                <td>
                                    {{-- <a href="{{ route('admin.service_areas.show', $serviceArea) }}"
                                        class="btn btn-info btn-sm">View</a> --}}
                                    <a href="{{ route('admin.service_areas.edit', $serviceArea) }}"
                                        class="btn btn-primary btn-sm">Edit</a>
                                    <form action="{{ route('admin.service_areas.destroy', $serviceArea) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
