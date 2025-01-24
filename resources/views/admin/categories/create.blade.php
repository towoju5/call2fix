@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Category</h1>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="category_name" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="category_name" name="category_name" required>
        </div>

        <div class="mb-3">
            <label for="category_slug" class="form-label">Slug</label>
            <input type="text" class="form-control" id="category_slug" name="category_slug" required>
        </div>

        <div class="mb-3">
            <label for="parent_category" class="form-label">Parent Service Area</label>
            <select class="form-select" id="parent_category" name="parent_category">
                @foreach($serviceAreas as $serviceArea)
                    <option value="{{ $serviceArea->id }}">{{ $serviceArea->service_area_title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="category_description" class="form-label">Description</label>
            <textarea class="form-control" id="category_description" name="category_description"></textarea>
        </div>

        <div class="mb-3">
            <label for="category_image" class="form-label">Image</label>
            <input type="file" class="form-control" id="category_image" name="category_image">
        </div>

        <button type="submit" class="btn btn-primary">Create Category</button>
    </form>
</div>
@endsection
