@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Create Admin User</h4>
        <p class="text-muted mb-0">Add a new administrative user to the system</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.admin-users.index') }}">Admin Users</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Admin User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.admin-users.store') }}" id="createAdminForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                            onclick="togglePassword('password')">
                                        <i class="ti ti-eye" id="password_icon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                           id="password_confirmation" name="password_confirmation" required>
                                    <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                            onclick="togglePassword('password_confirmation')">
                                        <i class="ti ti-eye" id="password_confirmation_icon"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                        <option value="{{ $role->id }}" {{ in_array($role->id, old('roles', [])) ? 'selected' : '' }}>
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
                                <label for="active" class="form-label">Status</label>
                                <select class="form-select @error('active') is-invalid @enderror" 
                                        id="active" name="active">
                                    <option value="1" {{ old('active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3" 
                                  placeholder="Internal notes about this admin user...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Create Admin User
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
                <h5 class="card-title mb-0">Available Roles</h5>
            </div>
            <div class="card-body">
                @if($roles->count() > 0)
                    @foreach($roles as $role)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">{{ $role->name }}</h6>
                                <small class="text-muted">{{ $role->description ?? 'No description' }}</small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary">{{ $role->permissions->count() }} permissions</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="ti ti-shield-off fs-48 text-muted mb-3"></i>
                        <p class="text-muted">No roles available</p>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-primary">
                            Create Roles
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Security Tips</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="ti ti-check text-success me-2"></i>
                        Use strong passwords (8+ characters)
                    </li>
                    <li class="mb-2">
                        <i class="ti ti-check text-success me-2"></i>
                        Assign minimal required permissions
                    </li>
                    <li class="mb-2">
                        <i class="ti ti-check text-success me-2"></i>
                        Regularly review admin access
                    </li>
                    <li class="mb-0">
                        <i class="ti ti-check text-success me-2"></i>
                        Monitor admin activities
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
    } else {
        input.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('createAdminForm');
    
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Password confirmation validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    
    function validatePasswordMatch() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePasswordMatch);
    confirmPassword.addEventListener('input', validatePasswordMatch);
    
    // Initialize Select2 for roles
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#roles').select2({
            placeholder: 'Select roles...',
            allowClear: true
        });
    }
});
</script>
@endpush