  @extends('layouts.app')

  @section('content')
  <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h2">Service Requests</h1>
          <a href="{{ route('admin.service-requests.create') }}" class="btn btn-primary">
              Create New Request
          </a>
      </div>

      <div class="card">
          <div class="card-body">
              <div class="table-responsive">
                  <table class="table table-striped">
                      <thead>
                          <tr>
                              <th>ID</th>
                              <th>Problem Title</th>
                              <th>Inspection Date</th>
                              <th>Status</th>
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach($serviceRequests as $request)
                              <tr>
                                  <td>{{ $request->id }}</td>
                                  <td>{{ $request->problem_title }}</td>
                                  <td>{{ $request->inspection_date }}</td>
                                  <td>{{ $request->request_status }}</td>
                                  <td>
                                      <a href="{{ route('admin.service-requests.show', $request) }}" class="btn btn-sm btn-info me-2">View</a>
                                      <a href="{{ route('admin.service-requests.edit', $request) }}" class="btn btn-sm btn-warning me-2">Edit</a>
                                      <form action="{{ route('admin.service-requests.destroy', $request) }}" method="POST" class="d-inline">
                                          @csrf
                                          @method('DELETE')
                                          <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                      </form>
                                  </td>
                              </tr>
                          @endforeach
                      </tbody>
                  </table>
              </div>
          </div>
      </div>

      <div class="mt-4">
          {{ $serviceRequests->links() }}
      </div>
  </div>
  @endsection
