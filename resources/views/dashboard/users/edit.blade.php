@extends('layouts.vertical', ['title' => 'Edit User'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Users', 'title' => 'Edit User'])

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Edit User Information</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
                                <input type="password" class="form-control" name="password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="password_confirmation">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="user" {{ $user->hasRole('user') ? 'selected' : '' }}>User</option>
                                    <option value="manager" {{ $user->hasRole('manager') ? 'selected' : '' }}>Manager</option>
                                    <option value="admin" {{ $user->hasRole('admin') ? 'selected' : '' }}>Admin</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

