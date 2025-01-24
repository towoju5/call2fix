  @extends('layouts.app')

  @section('content')
  <div class="container py-5">
      <div class="row justify-content-center">
          <div class="col-md-8">
              <h1 class="mb-4">Edit Product</h1>

              <form action="{{ route('admin.products.update', $product) }}" method="POST">
                  @csrf
                  @method('PUT')

                  <div class="mb-3">
                      <label for="name" class="form-label">Name</label>
                      <input type="text" name="name" id="name" value="{{ $product->name }}" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="description" class="form-label">Description</label>
                      <textarea name="description" id="description" rows="4" class="form-control" required>{{ $product->description }}</textarea>
                  </div>

                  <div class="mb-3">
                      <label for="price" class="form-label">Price</label>
                      <input type="number" name="price" id="price" step="0.01" value="{{ $product->price }}" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="category_id" class="form-label">Category</label>
                      <select name="category_id" id="category_id" class="form-select" required>
                          @foreach($categories as $category)
                              <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                          @endforeach
                      </select>
                  </div>

                  <div class="mb-3">
                      <label for="seller_id" class="form-label">Seller</label>
                      <select name="seller_id" id="seller_id" class="form-select" required>
                          @foreach($sellers as $seller)
                              <option value="{{ $seller->id }}" {{ $product->seller_id == $seller->id ? 'selected' : '' }}>{{ $seller->name }}</option>
                          @endforeach
                      </select>
                  </div>

                  <div class="mb-3">
                      <label for="stock" class="form-label">Stock</label>
                      <input type="number" name="stock" id="stock" value="{{ $product->stock }}" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="sku" class="form-label">SKU</label>
                      <input type="text" name="sku" id="sku" value="{{ $product->sku }}" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="product_currency" class="form-label">Currency</label>
                      <input type="text" name="product_currency" id="product_currency" value="{{ $product->product_currency }}" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="weight" class="form-label">Weight</label>
                      <input type="number" name="weight" id="weight" step="0.01" value="{{ $product->weight }}" class="form-control">
                  </div>

                  <div class="mb-3">
                      <label for="dimensions" class="form-label">Dimensions</label>
                      <input type="text" name="dimensions" id="dimensions" value="{{ $product->dimensions }}" class="form-control">
                  </div>

                  <div class="mb-3 form-check">
                      <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="form-check-input" id="is_active">
                      <label class="form-check-label" for="is_active">Is Active</label>
                  </div>

                  <div class="mb-3 form-check">
                      <input type="checkbox" name="is_leasable" value="1" {{ $product->is_leasable ? 'checked' : '' }} class="form-check-input" id="is_leasable">
                      <label class="form-check-label" for="is_leasable">Is Leasable</label>
                  </div>

                  <button type="submit" class="btn btn-primary">Update Product</button>
              </form>
          </div>
      </div>
  </div>
  @endsection
