@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Admin Users Management</h4>
        <p class="text-muted mb-0">Manage administrative users and their permissions</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Admin Users</li>
        </ol>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Admins</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_admins'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Admins</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_admins'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Online Now</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['online_admins'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Last 24h</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['recent_logins'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.admin-users.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search by name or email...">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.admin-users.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Admin Users Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Admin Users</h5>
            <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>Add Admin User
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-nowrap" id="adminUsersTable">
                <thead class="table-light">
                    <tr>
                        <th>Admin</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary-subtle rounded me-3">
                                    <span class="avatar-title bg-primary-subtle text-primary fs-16">
                                        {{ substr($admin->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $admin->name }}</h6>
                                    <small class="text-muted">ID: {{ $admin->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>
                            @if($admin->roles->count() > 0)
                                @foreach($admin->roles as $role)
                                    <span class="badge bg-info-subtle text-info">{{ $role->name }}</span>
                                @endforeach
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">No Role</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $admin->active ? 'success' : 'danger' }}-subtle text-{{ $admin->active ? 'success' : 'danger' }}">
                                {{ $admin->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if($admin->last_login_at)
                                <span class="text-muted">{{ $admin->last_login_at->diffForHumans() }}</span>
                                <br><small class="text-muted">{{ $admin->last_login_at->format('M d, Y H:i') }}</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ $admin->created_at->format('M d, Y') }}</span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.admin-users.show', $admin->id) }}">
                                            <i class="ti ti-eye me-2"></i>View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.admin-users.edit', $admin->id) }}">
                                            <i class="ti ti-edit me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.admin-users.permissions', $admin->id) }}">
                                            <i class="ti ti-shield me-2"></i>Permissions
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-{{ $admin->active ? 'warning' : 'success' }}" 
                                                onclick="toggleAdminStatus({{ $admin->id }}, {{ $admin->active ? 'false' : 'true' }})">
                                            <i class="ti ti-{{ $admin->active ? 'user-x' : 'user-check' }} me-2"></i>
                                            {{ $admin->active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </li>
                                    @if($admin->id !== auth()->guard('admin')->id())
                                    <li>
                                        <button class="dropdown-item text-danger" onclick="deleteAdmin({{ $admin->id }})">
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
                                <i class="ti ti-user-shield fs-48 mb-3"></i>
                                <h5>No Admin Users Found</h5>
                                <p>No admin users have been created yet.</p>
                                <a href="{{ route('admin.admin-users.create') }}" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i>Add First Admin User
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($admins->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $admins->links() }}
        </div>
        @endif
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
    $('#adminUsersTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[5, 'desc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search admins...",
            infoFiltered: ""
        }
    });
});

function toggleAdminStatus(adminId, newStatus) {
    const action = newStatus === 'true' ? 'activate' : 'deactivate';
    const message = `Are you sure you want to ${action} this admin user?`;
    
    if (confirm(message)) {
        window.ajaxHelper.post(`/admin/admin-users/${adminId}/toggle-status`, { 
            active: newStatus === 'true' 
        })
        .then(data => {
            if (data.success) {
                window.showNotification(`Admin user ${action}d successfully`, 'success');
                location.reload();
            } else {
                window.showNotification(`Failed to ${action} admin user: ` + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showNotification(`Error ${action}ing admin user: ` + error.message, 'error');
        });
    }
}

function deleteAdmin(adminId) {
    if (confirm('Are you sure you want to delete this admin user? This action cannot be undone.')) {
        window.ajaxHelper.post(`/admin/admin-users/${adminId}/delete`)
            .then(data => {
                if (data.success) {
                    window.showNotification('Admin user deleted successfully', 'success');
                    location.reload();
                } else {
                    window.showNotification('Failed to delete admin user: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error deleting admin user: ' + error.message, 'error');
            });
    }
}
</script>
@endpush