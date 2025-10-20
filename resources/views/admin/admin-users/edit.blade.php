@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Edit Admin User</h4>
                <p class="text-muted mb-0">Update admin user information and roles</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Admin Users
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Admin User Information</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.admin-users.update', $admin->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                <small class="text-muted">Leave blank to keep current password</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="roles" class="form-label">Roles</label>
                                <select class="form-select @error('roles') is-invalid @enderror" 
                                        id="roles" name="roles[]" multiple>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" 
                                                {{ in_array($role->id, old('roles', $admin->roles->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('roles')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" value="1" 
                                           {{ old('active', $admin->active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="active">
                                        Active Status
                                    </label>
                                </div>
                                <small class="text-muted">Inactive admins cannot log in to the system</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i> Update Admin User
                        </button>
                        <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Current Information</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Created At</label>
                    <p class="form-control-plaintext">{{ $admin->created_at->format('M d, Y H:i') }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Last Updated</label>
                    <p class="form-control-plaintext">{{ $admin->updated_at->format('M d, Y H:i') }}</p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Current Roles</label>
                    <div>
                        @if($admin->roles->count() > 0)
                            @foreach($admin->roles as $role)
                                <span class="badge bg-primary me-1">{{ $role->name }}</span>
                            @endforeach
                        @else
                            <span class="text-muted">No roles assigned</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for roles
    $('#roles').select2({
        placeholder: 'Select roles...',
        allowClear: true
    });
});
</script>
@endpush


