@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Edit Role: {{ $role->name }}</h2>
            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Role Name:</label>
                    <input type="text" name="name" id="name" value="{{ $role->name }}" class="form-control" required>
                </div>
                <div class="row mb-3">
                    <label class="form-label">Permissions: </label>
                    <div class="col-12 mb-2">
                        <button type="button" id="checkAllBtn" class="btn btn-secondary btn-sm">Check All</button>
                    </div>
                    @foreach($permissions as $permission)
                        <div class="col-3 m-2 mx-3 form-check mb-2">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission_{{ $permission->id }}" class="form-check-input permission-checkbox" {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                            <label class="form-check-label" for="permission_{{ $permission->id }}">{{ str_replace("_", " ", $permission->name) }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('checkAllBtn').addEventListener('click', function() {
            var checkboxes = document.getElementsByClassName('permission-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
        });
    </script>
    </div>
</div>
@endsection