@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Services under {{ $category->category_name }}</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Service Name</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
                <tr>
                    <td>{{ $service->service_name }}</td>
                    <td>{{ $service->service_description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mt-3">Back to Categories</a>
</div>
@endsection
