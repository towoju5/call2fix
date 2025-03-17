@extends('layouts.app')

@section('content')
    <div class="container my-3">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email/Phone</th>
                            <th>Account Type</th>
                            <th>Roles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user['id'] }}</td>
                                <td>{{ $user['first_name'] }} {{ $user['last_name'] }}</td>
                                <td>{{ $user['email'] ?? $user['phone'] }}</td>
                                <td>{{ ucfirst($user['account_type']) }}</td>
                                <td>
                                    @if (count($user['roles']) > 0)
                                        @foreach ($user['roles'] as $role)
                                            <span
                                                class="badge bg-primary">{{ str_replace('_', ' ', ucfirst($role->name)) }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-secondary">No roles</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="btn-group gap-3" role="group">
                                        <a href="{{ route('admin.users.show', $user['id']) }}"
                                            class="btn btn-sm btn-info">View</a>
                                        @if ($user['is_banned'])
                                            <a href="{{ route('admin.users.unban', $user['id']) }}"
                                                class="btn btn-sm btn-warning">Unban</a>
                                        @else
                                            <a href="{{ route('admin.users.ban', $user['id']) }}"
                                                class="btn btn-sm btn-warning">Ban</a>
                                        @endif
                                        <form action="{{ route('admin.users.destroy', $user['id']) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
