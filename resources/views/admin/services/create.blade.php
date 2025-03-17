@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Service</h1>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="service_name" class="form-label">Service Name</label>
            <input type="text" class="form-control" id="service_name" name="service_name" required>
        </div>

        <div class="mb-3">
            <label for="service_slug" class="form-label">Slug</label>
            <input type="text" class="form-control" id="service_slug" name="service_slug" required>
        </div>

        <div class="mb-3">
            <label for="parent_service" class="form-label">Parent Service Area</label>
            <select class="form-select" id="parent_service" name="parent_service">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="service_description" class="form-label">Description</label>
            <textarea class="form-control" id="service_description" name="service_description"></textarea>
        </div>

        <div class="mb-3">
            <label for="service_image" class="form-label">Image</label>
            <input type="file" class="form-control" id="service_image" name="service_image">
        </div>

        <button type="submit" class="btn btn-primary">Create Service</button>
    </form>
</div>
@endsection
