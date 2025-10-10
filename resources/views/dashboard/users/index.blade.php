@extends('layouts.vertical', ['title' => 'Users Management'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Administration', 'title' => 'Users Management'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end">
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    <i class="ti ti-user-plus me-1"></i> Add New User
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h4 class="header-title mb-0">All Users</h4>
                        <div class="position-relative">
                            <input type="text" class="form-control ps-4" placeholder="Search User">
                            <i class="ti ti-search position-absolute top-50 translate-middle-y ms-2"></i>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Networks</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <img src="/images/users/avatar-1.jpg" alt="" class="rounded-circle avatar-sm me-2">
                                        <div>
                                            <h6 class="mb-0 fw-semibold">{{ $user->name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary-subtle text-primary">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $user->getActiveNetworkConnectionsCount() }}</td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success p-1">Active</span>
                                </td>
                                <td class="pe-3">
                                    <div class="hstack gap-1 justify-content-end">
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-soft-warning btn-icon btn-sm rounded-circle">
                                            <i class="ti ti-edit fs-16"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-icon btn-sm rounded-circle" onclick="return confirm('Are you sure?')">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="ti ti-users-off fs-64 text-muted mb-3"></i>
                                        <h5 class="text-muted mb-3">No Users Found</h5>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        {{ $users->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

