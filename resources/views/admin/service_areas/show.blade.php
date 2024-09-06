@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Service Area Details</h1>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">{{ $serviceArea->service_area_title }}</h5>
            <p class="card-text">ID: {{ $serviceArea->id }}</p>
        </div>
    </div>
    <div class="mt-3">
        <a href="{{ route('admin.service_areas.edit', $serviceArea) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('admin.service_areas.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>
@endsection
