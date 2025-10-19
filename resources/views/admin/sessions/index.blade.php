@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Admin Sessions</h4>
        <p class="text-muted mb-0">Monitor and manage active admin sessions</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Sessions</li>
        </ol>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Sessions</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_sessions'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Sessions</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_sessions'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Unique Admins</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['unique_admins'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Today's Logins</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['today_logins'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.sessions.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="admin_id" class="form-label">Admin</label>
                <select class="form-select" id="admin_id" name="admin_id">
                    <option value="">All Admins</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="device_type" class="form-label">Device Type</label>
                <select class="form-select" id="device_type" name="device_type">
                    <option value="">All Devices</option>
                    <option value="desktop" {{ request('device_type') == 'desktop' ? 'selected' : '' }}>Desktop</option>
                    <option value="mobile" {{ request('device_type') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                    <option value="tablet" {{ request('device_type') == 'tablet' ? 'selected' : '' }}>Tablet</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.sessions.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Sessions Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Admin Sessions</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-warning btn-sm" onclick="cleanupInactiveSessions()">
                    <i class="ti ti-trash me-1"></i>Cleanup Inactive
                </button>
                <button class="btn btn-danger btn-sm" onclick="terminateAllSessions()">
                    <i class="ti ti-logout me-1"></i>Terminate All
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-nowrap" id="sessionsTable">
                <thead class="table-light">
                    <tr>
                        <th>Admin</th>
                        <th>Device</th>
                        <th>Location</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Login Time</th>
                        <th>Last Activity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary-subtle rounded me-3">
                                    <span class="avatar-title bg-primary-subtle text-primary fs-16">
                                        {{ substr($session->admin->name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $session->admin->name }}</h6>
                                    <small class="text-muted">{{ $session->admin->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="ti ti-{{ $session->device_type == 'mobile' ? 'device-mobile' : ($session->device_type == 'tablet' ? 'device-tablet' : 'device-desktop') }} me-2"></i>
                                <div>
                                    <div class="fw-medium">{{ $session->device_name ?? 'Unknown Device' }}</div>
                                    <small class="text-muted">{{ $session->platform ?? 'Unknown' }} - {{ $session->browser ?? 'Unknown' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($session->city && $session->country)
                                <div class="fw-medium">{{ $session->city }}</div>
                                <small class="text-muted">{{ $session->country }}</small>
                            @else
                                <span class="text-muted">Unknown</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ $session->ip_address ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $session->is_active ? 'success' : 'danger' }}-subtle text-{{ $session->is_active ? 'success' : 'danger' }}">
                                {{ $session->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            @if($session->login_at)
                                <span class="text-muted">{{ $session->login_at->format('M d, H:i') }}</span>
                                <br><small class="text-muted">{{ $session->login_at->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($session->last_activity)
                                <span class="text-muted">{{ $session->last_activity->format('M d, H:i') }}</span>
                                <br><small class="text-muted">{{ $session->last_activity->diffForHumans() }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.sessions.show', $session->id) }}">
                                            <i class="ti ti-eye me-2"></i>View Details
                                        </a>
                                    </li>
                                    @if($session->is_active)
                                    <li>
                                        <button class="dropdown-item text-danger" onclick="terminateSession({{ $session->id }})">
                                            <i class="ti ti-logout me-2"></i>Terminate Session
                                        </button>
                                    </li>
                                    @endif
                                    @if($session->admin_id !== auth()->guard('admin')->id())
                                    <li>
                                        <button class="dropdown-item text-warning" onclick="terminateAllForAdmin({{ $session->admin_id }})">
                                            <i class="ti ti-user-x me-2"></i>Terminate All for Admin
                                        </button>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="text-muted">
                                <i class="ti ti-device-desktop fs-48 mb-3"></i>
                                <h5>No Sessions Found</h5>
                                <p>No admin sessions match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($sessions->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $sessions->links() }}
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
    $('#sessionsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[6, 'desc']],
        columnDefs: [
            { orderable: false, targets: [7] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search sessions...",
            infoFiltered: ""
        }
    });
});

function terminateSession(sessionId) {
    if (confirm('Are you sure you want to terminate this session?')) {
        window.ajaxHelper.post(`/admin/sessions/${sessionId}/terminate`)
            .then(data => {
                if (data.success) {
                    window.showNotification('Session terminated successfully', 'success');
                    location.reload();
                } else {
                    window.showNotification('Failed to terminate session: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error terminating session: ' + error.message, 'error');
            });
    }
}

function terminateAllForAdmin(adminId) {
    if (confirm('Are you sure you want to terminate all sessions for this admin?')) {
        window.ajaxHelper.post(`/admin/sessions/${adminId}/terminate-all`)
            .then(data => {
                if (data.success) {
                    window.showNotification('All sessions terminated successfully', 'success');
                    location.reload();
                } else {
                    window.showNotification('Failed to terminate sessions: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error terminating sessions: ' + error.message, 'error');
            });
    }
}

function cleanupInactiveSessions() {
    if (confirm('Are you sure you want to cleanup inactive sessions?')) {
        window.ajaxHelper.post('/admin/sessions/cleanup')
            .then(data => {
                if (data.success) {
                    window.showNotification('Inactive sessions cleaned up successfully', 'success');
                    location.reload();
                } else {
                    window.showNotification('Failed to cleanup sessions: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error cleaning up sessions: ' + error.message, 'error');
            });
    }
}

function terminateAllSessions() {
    if (confirm('Are you sure you want to terminate ALL active sessions? This will log out all admins!')) {
        window.ajaxHelper.post('/admin/sessions/terminate-all')
            .then(data => {
                if (data.success) {
                    window.showNotification('All sessions terminated successfully', 'success');
                    location.reload();
                } else {
                    window.showNotification('Failed to terminate all sessions: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error terminating all sessions: ' + error.message, 'error');
            });
    }
}
</script>
@endpush

