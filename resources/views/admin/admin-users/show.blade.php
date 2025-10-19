@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Admin User Details: {{ $admin->name }}</h4>
        <p class="text-muted mb-0">View and manage admin user information</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.admin-users.index') }}">Admin Users</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Admin Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Admin Information</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 200px;">Name</th>
                                <td>{{ $admin->name }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Email</th>
                                <td>{{ $admin->email }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    <span class="badge bg-{{ $admin->active ? 'success' : 'danger' }}-subtle text-{{ $admin->active ? 'success' : 'danger' }}">
                                        {{ $admin->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Roles</th>
                                <td>
                                    @if($admin->roles->count() > 0)
                                        @foreach($admin->roles as $role)
                                            <span class="badge bg-info-subtle text-info me-1">{{ $role->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">No Roles Assigned</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Last Login</th>
                                <td>
                                    @if($admin->last_login_at)
                                        {{ $admin->last_login_at->format('M d, Y H:i:s') }}
                                        <br><small class="text-muted">{{ $admin->last_login_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">Never logged in</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Created At</th>
                                <td>{{ $admin->created_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Last Updated</th>
                                <td>{{ $admin->updated_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                            @if($admin->notes)
                            <tr>
                                <th scope="row">Notes</th>
                                <td>{{ $admin->notes }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Permissions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Permissions</h5>
            </div>
            <div class="card-body">
                @if($admin->getAllPermissions()->count() > 0)
                    <div class="row">
                        @foreach($admin->getAllPermissions()->groupBy('group') as $group => $permissions)
                            <div class="col-md-6 mb-3">
                                <h6 class="text-muted">{{ $group ?: 'General' }}</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($permissions as $permission)
                                        <span class="badge bg-primary-subtle text-primary">{{ $permission->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ti ti-shield-off fs-48 text-muted mb-3"></i>
                        <p class="text-muted">No permissions assigned</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    <div class="timeline">
                        @foreach($recentActivity as $activity)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $activity->action == 'login' ? 'success' : ($activity->action == 'logout' ? 'danger' : 'info') }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ ucfirst($activity->action) }}</h6>
                                    <p class="text-muted mb-1">{{ $activity->description }}</p>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="ti ti-activity fs-48 text-muted mb-3"></i>
                        <p class="text-muted">No recent activity</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Admin Avatar & Quick Info -->
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-lg bg-primary-subtle rounded mx-auto mb-3">
                    <span class="avatar-title bg-primary-subtle text-primary fs-24">
                        {{ substr($admin->name, 0, 1) }}
                    </span>
                </div>
                
                <h5 class="mb-1">{{ $admin->name }}</h5>
                <p class="text-muted mb-3">{{ $admin->email }}</p>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold text-primary">{{ $admin->created_at->format('M Y') }}</div>
                        <small class="text-muted">Member since</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold {{ $admin->active ? 'text-success' : 'text-danger' }}">
                            {{ $admin->active ? 'Active' : 'Inactive' }}
                        </div>
                        <small class="text-muted">Status</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.admin-users.edit', $admin->id) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-2"></i>Edit Admin User
                    </a>
                    
                    <a href="{{ route('admin.admin-users.permissions', $admin->id) }}" class="btn btn-info">
                        <i class="ti ti-shield me-2"></i>Manage Permissions
                    </a>
                    
                    @if($admin->id !== auth()->guard('admin')->id())
                        @if($admin->active)
                            <button type="button" class="btn btn-warning" onclick="toggleAdminStatus({{ $admin->id }}, false)">
                                <i class="ti ti-user-x me-2"></i>Deactivate Admin
                            </button>
                        @else
                            <button type="button" class="btn btn-success" onclick="toggleAdminStatus({{ $admin->id }}, true)">
                                <i class="ti ti-user-check me-2"></i>Activate Admin
                            </button>
                        @endif
                        
                        <button type="button" class="btn btn-danger" onclick="deleteAdmin({{ $admin->id }})">
                            <i class="ti ti-trash me-2"></i>Delete Admin
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold text-primary">{{ $stats['total_logins'] ?? 0 }}</div>
                        <small class="text-muted">Total Logins</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold text-info">{{ $stats['recent_actions'] ?? 0 }}</div>
                        <small class="text-muted">Recent Actions</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAdminStatus(adminId, newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    const message = `Are you sure you want to ${action} this admin user?`;
    
    if (confirm(message)) {
        window.ajaxHelper.post(`/admin/admin-users/${adminId}/toggle-status`, { 
            active: newStatus 
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
                    window.location.href = '{{ route("admin.admin-users.index") }}';
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

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}
</style>
@endpush

