@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Edit Role</h4>
                <p class="text-muted mb-0">Update role information and permissions</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Roles
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Role Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.update', $role->id) }}" id="role-form">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $role->name) }}" 
                               placeholder="e.g., content-manager, support-agent" required>
                        <div class="form-text">Use lowercase letters, numbers, and hyphens only</div>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="display_name" class="form-label">Display Name</label>
                        <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                               id="display_name" name="display_name" value="{{ old('display_name', $role->display_name) }}" 
                               placeholder="e.g., Content Manager, Support Agent">
                        @error('display_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Describe what this role can do...">{{ old('description', $role->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Permissions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($permissionsData as $group => $permissions)
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">{{ ucfirst(str_replace('-', ' ', $group)) }}</h6>
                            <div class="permission-group">
                                @foreach($permissions as $permission)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input permission-checkbox" type="checkbox" 
                                               id="permission_{{ $permission->id }}" name="permissions[]" 
                                               value="{{ $permission->id }}"
                                               {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="select_all_permissions">
                        <label class="form-check-label fw-bold" for="select_all_permissions">
                            Select All Permissions
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary" form="role-form">
                <i class="ti ti-device-floppy me-1"></i>Update Role
            </button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Role Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg bg-primary-subtle rounded mx-auto">
                        <span class="avatar-title bg-primary-subtle text-primary fs-24">
                            {{ substr($role->name, 0, 1).toUpperCase() }}
                        </span>
                    </div>
                </div>
                
                <div class="text-center">
                    <h6 class="mb-1">{{ ucfirst(str_replace('-', ' ', $role->name)) }}</h6>
                    <small class="text-muted">{{ $role->name }}</small>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <h6 class="text-muted">Current Permissions</h6>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($role->permissions->take(5) as $permission)
                            <span class="badge bg-secondary-subtle text-secondary">
                                {{ ucfirst(str_replace('-', ' ', $permission->name)) }}
                            </span>
                        @endforeach
                        @if($role->permissions->count() > 5)
                            <span class="badge bg-light text-muted">
                                +{{ $role->permissions->count() - 5 }} more
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-muted">Users with this Role</h6>
                    <div class="text-center">
                        <div class="fw-bold text-primary fs-18">{{ $role->users_count }}</div>
                        <small class="text-muted">users</small>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6 class="alert-heading">Last Updated</h6>
                    <p class="mb-0">{{ $role->updated_at->format('M d, Y H:i:s') }}</p>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Important Notes</h6>
                    <ul class="mb-0">
                        <li>Changes affect all users with this role</li>
                        <li>Removing permissions may break functionality</li>
                        <li>Test changes in a safe environment first</li>
                        <li>Consider notifying affected users</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Selected Permissions</h5>
            </div>
            <div class="card-body">
                <div id="selected-permissions-list">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('role-form');
    const selectAllCheckbox = document.getElementById('select_all_permissions');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const selectedPermissionsList = document.getElementById('selected-permissions-list');
    
    // Select all permissions functionality
    selectAllCheckbox.addEventListener('change', function() {
        permissionCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedPermissionsList();
    });
    
    // Individual permission checkbox change
    permissionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedPermissionsList();
            updateSelectAllCheckbox();
        });
    });
    
    // Update selected permissions list
    function updateSelectedPermissionsList() {
        const selectedPermissions = Array.from(permissionCheckboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.nextElementSibling.textContent.trim());
        
        if (selectedPermissions.length === 0) {
            selectedPermissionsList.innerHTML = '<p class="text-muted">No permissions selected.</p>';
        } else {
            const listHtml = selectedPermissions.map(permission => 
                `<div class="d-flex align-items-center mb-1">
                    <i class="ti ti-check text-success me-2"></i>
                    <span class="text-muted">${permission}</span>
                </div>`
            ).join('');
            
            selectedPermissionsList.innerHTML = `
                <div class="mb-2">
                    <strong>${selectedPermissions.length} permission(s) selected:</strong>
                </div>
                ${listHtml}
            `;
        }
    }
    
    // Update select all checkbox state
    function updateSelectAllCheckbox() {
        const checkedCount = Array.from(permissionCheckboxes).filter(checkbox => checkbox.checked).length;
        const totalCount = permissionCheckboxes.length;
        
        if (checkedCount === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedCount === totalCount) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        }
    }
    
    // Form validation
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
        
        // Check if at least one permission is selected
        const hasPermissions = Array.from(permissionCheckboxes).some(checkbox => checkbox.checked);
        if (!hasPermissions) {
            e.preventDefault();
            alert('Please select at least one permission for this role.');
        }
    });
    
    // Role name validation
    const nameInput = document.getElementById('name');
    nameInput.addEventListener('input', function() {
        this.value = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
    });
    
    // Initial update
    updateSelectedPermissionsList();
    updateSelectAllCheckbox();
});
</script>
@endpush

