  @extends('layouts.app')

  @section('content')
  <div class="container py-5">
      <div class="row justify-content-center">
          <div class="col-md-8">
              <h1 class="mb-4">Create Service Request</h1>

              <form action="{{ route('admin.service-requests.store') }}" method="POST" enctype="multipart/form-data">
                  @csrf

                  <div class="mb-3">
                      <label for="property_id" class="form-label">Property</label>
                      <select name="property_id" id="property_id" class="form-select">
                          <!-- Add property options here -->
                      </select>
                  </div>

                  <div class="mb-3">
                      <label for="problem_title" class="form-label">Problem Title</label>
                      <input type="text" name="problem_title" id="problem_title" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="problem_description" class="form-label">Problem Description</label>
                      <textarea name="problem_description" id="problem_description" rows="4" class="form-control" required></textarea>
                  </div>

                  <div class="mb-3">
                      <label for="inspection_date" class="form-label">Inspection Date</label>
                      <input type="date" name="inspection_date" id="inspection_date" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="inspection_time" class="form-label">Inspection Time</label>
                      <input type="time" name="inspection_time" id="inspection_time" class="form-control" required>
                  </div>

                  <!-- Add other form fields here -->

                  <div class="d-grid">
                      <button type="submit" class="btn btn-primary">Create Service Request</button>
                  </div>
              </form>
          </div>
      </div>
  </div>
  @endsection
