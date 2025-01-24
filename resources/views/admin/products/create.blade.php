  @extends('layouts.app')

  @section('content')
  <div class="container py-5">
      <div class="row justify-content-center">
          <div class="col-md-8">
              <h1 class="mb-4">Create Product</h1>

              <form action="{{ route('admin.products.store') }}" method="POST">
                  @csrf

                  <div class="mb-3">
                      <label for="name" class="form-label">Name</label>
                      <input type="text" name="name" id="name" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="description" class="form-label">Description</label>
                      <textarea name="description" id="description" rows="4" class="form-control" required></textarea>
                  </div>

                  <div class="mb-3">
                      <label for="price" class="form-label">Price</label>
                      <input type="number" name="price" id="price" step="0.01" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="category_id" class="form-label">Category</label>
                      <select name="category_id" id="category_id" class="form-select" required>
                          @foreach($categories as $category)
                              <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                          @endforeach
                      </select>
                  </div>

                  <div class="mb-3">
                      <label for="seller_id" class="form-label">Seller</label>
                      <select name="seller_id" id="seller_id" class="form-select" required>
                          @foreach($sellers as $seller)
                              <option value="{{ $seller->id }}">{{ "$seller->first_name $seller->last_name" }}</option>
                          @endforeach
                      </select>
                  </div>

                  <div class="mb-3">
                      <label for="stock" class="form-label">Stock</label>
                      <input type="number" name="stock" id="stock" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="sku" class="form-label">SKU</label>
                      <input type="text" name="sku" id="sku" class="form-control" required>
                  </div>

                  <div class="mb-3">
                      <label for="product_currency" class="form-label">Currency</label>
                      <select name="product_currency" id="product_currency" class="form-select" required>
                          <option value="NGN">NGN</option>
                          <option value="GHC">GHC</option>
                          <option value="CFA">CFA</option>
                          <option value="USD">USD</option>
                          <option value="CAD">CAD</option>
                      </select>
                  </div>

                  <div class="mb-3">
                      <label for="weight" class="form-label">Weight</label>
                      <input type="number" name="weight" id="weight" step="0.01" class="form-control">
                  </div>

                  <div class="mb-3">
                      <label for="dimensions" class="form-label">Dimensions</label>
                      <input type="text" name="dimensions" id="dimensions" class="form-control">
                  </div>

                  <div class="mb-3 form-check">
                      <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active">
                      <label class="form-check-label" for="is_active">Is Active</label>
                  </div>

                  <div class="mb-3 form-check">
                      <input type="checkbox" name="is_leasable" value="1" class="form-check-input" id="is_leasable">
                      <label class="form-check-label" for="is_leasable">Is Leasable</label>
                  </div>

                  <button type="submit" class="btn btn-primary">Create Product</button>
              </form>
          </div>
      </div>
  </div>
  @endsection
