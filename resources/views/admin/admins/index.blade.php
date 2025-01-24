@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row py-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="h2">Admin Users</h2>
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
                Create New Admin
            </a>
        </div>
        <div class="col-12 my-3">
            <div class="form-group">
                <input type="text" id="search" class="form-control" placeholder="Search">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->roles->pluck('name')->implode(', ') }}</td>
                            <td>
                                <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-sm btn-info mr-2">Edit</a>
                                <form action="{{ route('admin.admins.destroy', $admin) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this admin?')">Delete</button>
                                </form>
                                @if(!$admin->hasRole('super-admin'))
                                <form action="{{ route('admin.admins.assign-super-admin', $admin) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success ml-2">Assign Super Admin</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
