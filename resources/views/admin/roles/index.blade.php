@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Roles Management</h4>
        <p class="text-muted mb-0">Manage admin roles and permissions</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Roles</li>
        </ol>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Roles</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_roles'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Roles</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_roles'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Permissions</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['total_permissions'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Assigned Admins</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['assigned_admins'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Roles Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Roles</h5>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>Add Role
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-nowrap" id="rolesTable">
                <thead class="table-light">
                    <tr>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th>Admins</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary-subtle rounded me-3">
                                    <span class="avatar-title bg-primary-subtle text-primary fs-16">
                                        <i class="ti ti-shield"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $role->name }}</h6>
                                    <small class="text-muted">{{ $role->guard_name }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">{{ $role->description ?? 'No description' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-info-subtle text-info">
                                {{ $role->permissions->count() }} permissions
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-success-subtle text-success">
                                {{ $role->users->count() }} admins
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $role->is_active ? 'success' : 'danger' }}-subtle text-{{ $role->is_active ? 'success' : 'danger' }}">
                                {{ $role->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $role->created_at->format('M d, Y') }}</span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.roles.show', $role->id) }}">
                                            <i class="ti ti-eye me-2"></i>View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.roles.edit', $role->id) }}">
                                            <i class="ti ti-edit me-2"></i>Edit Role
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.roles.permissions', $role->id) }}">
                                            <i class="ti ti-shield me-2"></i>Manage Permissions
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    @if($role->name !== 'Super Admin')
                                    <li>
                                        <button class="dropdown-item text-{{ $role->is_active ? 'warning' : 'success' }}" 
                                                onclick="toggleRoleStatus({{ $role->id }}, {{ $role->is_active ? 'false' : 'true' }})">
                                            <i class="ti ti-{{ $role->is_active ? 'user-x' : 'user-check' }} me-2"></i>
                                            {{ $role->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-danger" onclick="deleteRole({{ $role->id }})">
                                            <i class="ti ti-trash me-2"></i>Delete
                                        </button>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="ti ti-shield-off fs-48 mb-3"></i>
                                <h5>No Roles Found</h5>
                                <p>No roles have been created yet.</p>
                                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i>Add First Role
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Wait for jQuery to be available
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    // Initialize DataTable
    $('#rolesTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[5, 'desc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search roles...",
            infoFiltered: ""
        }
    });
});

function toggleRoleStatus(roleId, newStatus) {
    const action = newStatus === 'true' ? 'activate' : 'deactivate';
    const message = `Are you sure you want to ${action} this role?`;
    
    if (confirm(message)) {
        window.ajaxHelper.post(`/admin/roles/${roleId}/toggle-status`, { 
            is_active: newStatus === 'true' 
        })
        .then(data => {
            if (data.success) {
                window.showNotification(`Role ${action}d successfully`, 'success');
                location.reload();
            } else {
                window.showNotification(`Failed to ${action} role: ` + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showNotification(`Error ${action}ing role: ` + error.message, 'error');
        });
    }
}

function deleteRole(roleId) {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone and may affect admin users assigned to this role.')) {
        window.ajaxHelper.post(`/admin/roles/${roleId}/delete`)
            .then(data => {
                if (data.success) {
                    window.showNotification('Role deleted successfully', 'success');
                    location.reload();
                } else {
                    window.showNotification('Failed to delete role: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error deleting role: ' + error.message, 'error');
            });
    }
}
</script>
@endpush