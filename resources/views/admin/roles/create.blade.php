@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Create Role</h4>
        <p class="text-muted mb-0">Create a new admin role with specific permissions</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Role Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.store') }}" id="createRoleForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="guard_name" class="form-label">Guard Name</label>
                                <select class="form-select @error('guard_name') is-invalid @enderror" 
                                        id="guard_name" name="guard_name">
                                    <option value="admin" {{ old('guard_name', 'admin') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="web" {{ old('guard_name') == 'web' ? 'selected' : '' }}>Web</option>
                                </select>
                                @error('guard_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" 
                                  placeholder="Describe the role's purpose and responsibilities...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Role
                            </label>
                        </div>
                        <small class="text-muted">Inactive roles cannot be assigned to new admins</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Create Role
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
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
                <h5 class="card-title mb-0">Available Permissions</h5>
            </div>
            <div class="card-body">
                @if($permissions->count() > 0)
                    <div class="mb-3">
                        <input type="text" class="form-control" id="permissionSearch" 
                               placeholder="Search permissions...">
                    </div>
                    
                    <div id="permissionsList" style="max-height: 400px; overflow-y: auto;">
                        @foreach($permissions->groupBy('group') as $group => $groupPermissions)
                            <div class="permission-group mb-3">
                                <h6 class="text-muted mb-2">{{ $group ?: 'General' }}</h6>
                                @foreach($groupPermissions as $permission)
                                    <div class="form-check permission-item" data-group="{{ $group }}">
                                        <input class="form-check-input" type="checkbox" 
                                               id="permission_{{ $permission->id }}" 
                                               name="permissions[]" 
                                               value="{{ $permission->id }}"
                                               {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ti ti-shield-off fs-48 text-muted mb-3"></i>
                        <p class="text-muted">No permissions available</p>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Role Guidelines</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="ti ti-check text-success me-2"></i>
                        Use descriptive role names
                    </li>
                    <li class="mb-2">
                        <i class="ti ti-check text-success me-2"></i>
                        Assign minimal required permissions
                    </li>
                    <li class="mb-2">
                        <i class="ti ti-check text-success me-2"></i>
                        Document role responsibilities
                    </li>
                    <li class="mb-0">
                        <i class="ti ti-check text-success me-2"></i>
                        Regularly review role assignments
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('createRoleForm');
    
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Permission search
    const permissionSearch = document.getElementById('permissionSearch');
    const permissionItems = document.querySelectorAll('.permission-item');
    
    permissionSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        permissionItems.forEach(item => {
            const label = item.querySelector('label').textContent.toLowerCase();
            const group = item.closest('.permission-group');
            
            if (label.includes(searchTerm)) {
                item.style.display = 'block';
                group.style.display = 'block';
            } else {
                item.style.display = 'none';
                // Hide group if no visible items
                const visibleItems = group.querySelectorAll('.permission-item[style*="block"], .permission-item:not([style*="none"])');
                if (visibleItems.length === 0) {
                    group.style.display = 'none';
                }
            }
        });
    });
    
    // Select all permissions in a group
    document.querySelectorAll('.permission-group').forEach(group => {
        const groupHeader = group.querySelector('h6');
        const checkboxes = group.querySelectorAll('input[type="checkbox"]');
        
        groupHeader.style.cursor = 'pointer';
        groupHeader.addEventListener('click', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        });
    });
});
</script>
@endpush