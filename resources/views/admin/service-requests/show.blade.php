  @extends('layouts.app')

  @section('content')
  <div class="container py-5">
      <div class="row justify-content-center">
          <div class="col-md-8">
              <h1 class="mb-4">Service Request Details</h1>

              <div class="card">
                  <div class="card-body">
                      <div class="row">
                          <div class="col-md-6 mb-3">
                              <h2 class="h5">Problem Title</h2>
                              <p>{{ $serviceRequest->problem_title }}</p>
                          </div>

                          <div class="col-md-6 mb-3">
                              <h2 class="h5">Problem Description</h2>
                              <p>{{ $serviceRequest->problem_description }}</p>
                          </div>

                          <div class="col-md-6 mb-3">
                              <h2 class="h5">Inspection Date</h2>
                              <p>{{ $serviceRequest->inspection_date }}</p>
                          </div>

                          <div class="col-md-6 mb-3">
                              <h2 class="h5">Inspection Time</h2>
                              <p>{{ $serviceRequest->inspection_time }}</p>
                          </div>

                          <!-- Add other details here -->

                          <div class="col-12 mt-4">
                              <a href="{{ route('admin.service-requests.edit', $serviceRequest) }}" class="btn btn-primary me-2">Edit</a>
                              <form action="{{ route('admin.service-requests.destroy', $serviceRequest) }}" method="POST" class="d-inline">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                              </form>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
  @endsection
