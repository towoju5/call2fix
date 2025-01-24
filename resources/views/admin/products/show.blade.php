  @extends('layouts.app')

  @section('content')
  <div class="container py-5">
      <div class="row justify-content-center">
          <div class="col-md-8">
              <h1 class="mb-4">Product Details</h1>

              <div class="card">
                  <div class="card-body">
                      <h2 class="card-title mb-3">{{ $product->name }}</h2>
                      <p class="card-text text-muted mb-4">{{ $product->description }}</p>
                      <div class="row">
                          <div class="col-md-6">
                              <p><strong>Price:</strong> {{ $product->price }} {{ $product->product_currency }}</p>
                              <p><strong>SKU:</strong> {{ $product->sku }}</p>
                              <p><strong>Stock:</strong> {{ $product->stock }}</p>
                              <p><strong>Category:</strong> {{ $product->category->name }}</p>
                          </div>
                          <div class="col-md-6">
                              <p><strong>Weight:</strong> {{ $product->weight ?? 'N/A' }}</p>
                              <p><strong>Dimensions:</strong> {{ $product->dimensions ?? 'N/A' }}</p>
                              <p><strong>Active:</strong> {{ $product->is_active ? 'Yes' : 'No' }}</p>
                              <p><strong>Leasable:</strong> {{ $product->is_leasable ? 'Yes' : 'No' }}</p>
                          </div>
                      </div>
                  </div>
              </div>

              <div class="mt-4">
                  <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary me-2">Edit</a>
                  <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                  </form>
              </div>
          </div>
      </div>
  </div>
  @endsection
