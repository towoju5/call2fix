@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit Service Area</h1>
    <form action="{{ route('admin.service_areas.update', $serviceArea) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="service_area_title">Service Area Title</label>
            <input type="text" class="form-control @error('service_area_title') is-invalid @enderror" id="service_area_title" name="service_area_title" value="{{ old('service_area_title', $serviceArea->service_area_title) }}" required>
            @error('service_area_title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary my-3">Update</button>
        <a href="{{ route('admin.service_areas.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
