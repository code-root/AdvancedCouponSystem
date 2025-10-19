@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">User Sessions Report</h4>
                <p class="text-muted mb-0">Monitor and analyze user login sessions and activity</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
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
                <h5 class="text-muted fs-13 text-uppercase">Active Sessions</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_sessions'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Unique Users</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['unique_users'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Today's Sessions</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['today_sessions'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Session Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.user-sessions.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Sessions</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Only</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" 
                               value="{{ request('date_from') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" 
                               value="{{ request('date_to') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>Filter Sessions
                        </button>
                        <a href="{{ route('admin.reports.user-sessions.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-refresh me-1"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">User Sessions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Device</th>
                                <th>Browser</th>
                                <th>Status</th>
                                <th>Last Activity</th>
                                <th>Login Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $session)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded me-2">
                                                <span class="avatar-title bg-primary-subtle text-primary">
                                                    {{ substr($session->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $session->user->name }}</h6>
                                                <small class="text-muted">{{ $session->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $session->ip_address }}</code>
                                    </td>
                                    <td>
                                        @if($session->city && $session->country)
                                            {{ $session->city }}, {{ $session->country }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <i class="ti ti-{{ $session->device_type == 'mobile' ? 'device-mobile' : ($session->device_type == 'tablet' ? 'device-tablet' : 'device-desktop') }} me-1"></i>
                                            {{ $session->device_name }}
                                        </div>
                                        <small class="text-muted">{{ $session->platform }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $session->browser }}</div>
                                        <small class="text-muted">{{ $session->browser_version }}</small>
                                    </td>
                                    <td>
                                        @if($session->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $session->last_activity ? $session->last_activity->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $session->last_activity ? $session->last_activity->format('H:i:s') : '' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $session->login_at ? $session->login_at->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $session->login_at ? $session->login_at->format('H:i:s') : '' }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="viewSessionDetails({{ $session->id }})">
                                                        <i class="ti ti-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                @if($session->is_active)
                                                    <li>
                                                        <a class="dropdown-item text-warning" href="#" onclick="terminateSession({{ $session->id }})">
                                                            <i class="ti ti-logout me-2"></i>Terminate Session
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ti ti-database-off fs-48 mb-3"></i>
                                            <p>No sessions found matching your criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($sessions->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $sessions->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Session Details Modal -->
<div class="modal fade" id="sessionDetailsModal" tabindex="-1" aria-labelledby="sessionDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionDetailsModalLabel">Session Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="sessionDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewSessionDetails(sessionId) {
    // Load session details via AJAX
    window.ajaxHelper.get(`/admin/reports/user-sessions/${sessionId}/details`)
        .then(data => {
            if (data.success) {
                document.getElementById('sessionDetailsContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('sessionDetailsModal')).show();
            } else {
                window.showNotification('Failed to load session details', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showNotification('Error loading session details', 'error');
        });
}

function terminateSession(sessionId) {
    if (confirm('Are you sure you want to terminate this session?')) {
        window.ajaxHelper.post(`/admin/reports/user-sessions/${sessionId}/terminate`)
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
                window.showNotification('Error terminating session', 'error');
            });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 30 seconds for active sessions
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 30000);
});
</script>
@endpush
