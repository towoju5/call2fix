@extends('layouts.app')

@section('content')
@php $serviceAreas = App\Models\ServiceArea::all(); @endphp
<div class="container">
    <h1>Categories</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Category Name</th>
                <th>Service Area</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $category->category_name }}</td>
                <td>{{ $category->serviceArea->service_area_title }}</td>
                <td>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCategoryModal" data-category-id="{{ $category->id }}" data-category-name="{{ $category->category_name }}" data-category-description="{{ $category->category_description }}" data-parent-category="{{ $category->parent_category }}" data-category-image="{{ $category->category_image }}">
                        Edit Category
                    </button>
                    
                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewServicesModal" data-category-id="{{ $category->id }}">
                        View Services
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $categories->links() }}

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.categories.update', 'placeholder') }}" method="POST" enctype="multipart/form-data" id="editCategoryForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" {{ old('category_name') }} required>
                        </div>

                        <div class="mb-3">
                            <label for="category_slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="category_slug" name="category_slug" {{ old('category_slug') }} required>
                        </div>

                        <div class="mb-3">
                            <label for="parent_category" class="form-label">Parent Service Area</label>
                            <select class="form-select" id="parent_category" name="parent_category" required>
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

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Services Modal -->
    <div class="modal fade" id="viewServicesModal" tabindex="-1" aria-labelledby="viewServicesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewServicesModalLabel">Services Under Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="services-list">
                    <!-- Services will be dynamically loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Update modal with category data
    var editCategoryModal = document.getElementById('editCategoryModal');
    editCategoryModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var categoryId = button.getAttribute('data-category-id');
        var categoryName = button.getAttribute('data-category-name');
        var categoryDescription = button.getAttribute('data-category-description');
        var parentCategory = button.getAttribute('data-parent-category');
        var categoryImage = button.getAttribute('data-category-image');

        var modal = editCategoryModal.querySelector('form');
        modal.action = '/admin/categories/' + categoryId; // Update action URL

        // Set the modal form fields
        modal.querySelector('#category_name').value = categoryName;
        modal.querySelector('#category_description').value = categoryDescription;
        modal.querySelector('#parent_category').value = parentCategory;
    });

    // Load services under a category
    var viewServicesModal = document.getElementById('viewServicesModal');
    viewServicesModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var categoryId = button.getAttribute('data-category-id');

        // Fetch services related to the category
        fetch('/admin/categories/' + categoryId + '/services')
            .then(response => response.json())
            .then(data => {
                var servicesList = document.getElementById('services-list');
                servicesList.innerHTML = '';

                if (data.services && data.services.length) {
                    data.services.forEach(function(service) {
                        var serviceItem = document.createElement('p');
                        serviceItem.textContent = service.service_name; // Update with your service fields
                        servicesList.appendChild(serviceItem);
                    });
                } else {
                    servicesList.innerHTML = '<p>No services found for this category.</p>';
                }
            })
            .catch(error => console.error('Error fetching services:', error));
    });
</script>
@endsection
