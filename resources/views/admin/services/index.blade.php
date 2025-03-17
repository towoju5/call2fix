@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Categories</h1>
    
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Service Name</th>
                <th>Service service</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $service->service_name }}</td>
                <td>{{ $service->category?->category_name }}</td>
                <td>
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editserviceModal" data-service-id="{{ $service->id }}" data-service-name="{{ $service->service_name }}" data-service-description="{{ $service->service_description }}" data-parent-service="{{ $service->parent_service }}" data-service-image="{{ $service->service_image }}">
                        Edit service
                    </button>
                    
                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewServicesModal" data-service-id="{{ $service->id }}">
                        View Services
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{ $services->links() }}

    <!-- Edit service Modal -->
    <div class="modal fade" id="editserviceModal" tabindex="-1" aria-labelledby="editserviceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editserviceModalLabel">Edit service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.services.update', 'placeholder') }}" method="POST" enctype="multipart/form-data" id="editserviceForm">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="service_name" class="form-label">service Name</label>
                            <input type="text" class="form-control" id="service_name" name="service_name" {{ old('service_name') }} required>
                        </div>

                        <div class="mb-3">
                            <label for="service_slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="service_slug" name="service_slug" {{ old('service_slug') }} required>
                        </div>

                        <div class="mb-3">
                            <label for="parent_service" class="form-label">Parent Service Area</label>
                            <select class="form-select" id="parent_service" name="parent_service" required>
                                @foreach($serviceAreas as $serviceArea)
                                    <option value="{{ $serviceArea->id }}">{{ $serviceArea->service_area_title }}</option>
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
                    <h5 class="modal-title" id="viewServicesModalLabel">Services Under service</h5>
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
    // Update modal with service data
    var editserviceModal = document.getElementById('editserviceModal');
    editserviceModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var serviceId = button.getAttribute('data-service-id');
        var serviceName = button.getAttribute('data-service-name');
        var serviceDescription = button.getAttribute('data-service-description');
        var parentservice = button.getAttribute('data-parent-service');
        var serviceImage = button.getAttribute('data-service-image');

        var modal = editserviceModal.querySelector('form');
        modal.action = '/admin/services/' + serviceId; // Update action URL

        // Set the modal form fields
        modal.querySelector('#service_name').value = serviceName;
        modal.querySelector('#service_description').value = serviceDescription;
        modal.querySelector('#parent_service').value = parentservice;
    });

    // Load services under a service
    var viewServicesModal = document.getElementById('viewServicesModal');
    viewServicesModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        var serviceId = button.getAttribute('data-service-id');

        // Fetch services related to the service
        fetch('/admin/services/' + serviceId + '/services')
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
                    servicesList.innerHTML = '<p>No services found for this service.</p>';
                }
            })
            .catch(error => console.error('Error fetching services:', error));
    });
</script>
@endsection
