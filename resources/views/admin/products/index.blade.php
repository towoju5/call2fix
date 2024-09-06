  @extends('layouts.app')

  @section('content')
  <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
          <h1 class="h2">Products</h1>
          <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
              Create New Product
          </a>
      </div>

      <div class="card">
          <div class="card-body">
              <div class="table-responsive">
                  <table class="table table-striped">
                      <thead>
                          <tr>
                              <th>Name</th>
                              <th>Price</th>
                              <th>Stock</th>
                              <th>Actions</th>
                          </tr>
                      </thead>
                      <tbody>
                          @foreach($products as $product)
                              <tr>
                                  <td>{{ $product->name }}</td>
                                  <td>{{ $product->price }}</td>
                                  <td>{{ $product->stock }}</td>
                                  <td>
                                      <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info me-2">View</a>
                                      <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning me-2">Edit</a>
                                      <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
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
          {{ $products->links() }}
      </div>
  </div>
  @endsection
