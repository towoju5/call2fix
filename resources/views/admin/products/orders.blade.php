@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4">Products</h1>

        <div class="mb-3">
            <select id="category-filter" class="form-select">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <select id="status-filter" class="form-select">
                <option value="">All Statuses</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div class="card">
            <div class="card-body">
                <table id="products-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-primary btn-sm me-2">View</a>
    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-success btn-sm me-2">Edit</a>
    <button onclick="orderProduct({{ $product->id }})" class="btn btn-info btn-sm">Order</button>

    <script>
        function orderProduct(productId) {
            let quantity = prompt("Enter quantity:", "1");
            if (quantity != null) {
                $.ajax({
                    url: "{{ route('admin.products.order') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        product_id: productId,
                        quantity: quantity
                    },
                    success: function(response) {
                        alert(response.success);
                    },
                    error: function(xhr) {
                        alert('Error: ' + xhr.responseJSON.error);
                    }
                });
            }
        }
    </script>

    <script>
        $(function() {
            var table = $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.products.index') }}",
                    data: function(d) {
                        d.category = $('#category-filter').val();
                        d.status = $('#status-filter').val();
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'category.name',
                        name: 'category.name'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data) {
                            return data ? 'Active' : 'Inactive';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $('#category-filter, #status-filter').change(function() {
                table.draw();
            });
        });
    </script>
@endpush