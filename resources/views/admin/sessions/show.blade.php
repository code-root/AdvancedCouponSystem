@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Session Details #{{ $session->id }}</h4>
        <p class="text-muted mb-0">Detailed information about this admin session</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.sessions.index') }}">Sessions</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Session Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Session Information</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 200px;">Session ID</th>
                                <td>
                                    <code>{{ $session->session_id }}</code>
                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $session->session_id }}')">
                                        <i class="ti ti-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Admin</th>
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
                            </tr>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    <span class="badge bg-{{ $session->is_active ? 'success' : 'danger' }}-subtle text-{{ $session->is_active ? 'success' : 'danger' }}">
                                        {{ $session->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">IP Address</th>
                                <td>
                                    <span class="text-muted">{{ $session->ip_address ?? 'N/A' }}</span>
                                    @if($session->ip_address)
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $session->ip_address }}')">
                                            <i class="ti ti-copy"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Device</th>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-{{ $session->device_type == 'mobile' ? 'device-mobile' : ($session->device_type == 'tablet' ? 'device-tablet' : 'device-desktop') }} me-2"></i>
                                        <div>
                                            <div class="fw-medium">{{ $session->device_name ?? 'Unknown Device' }}</div>
                                            <small class="text-muted">{{ $session->device_type ?? 'Unknown' }}</small>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Platform</th>
                                <td>{{ $session->platform ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Browser</th>
                                <td>
                                    {{ $session->browser ?? 'N/A' }}
                                    @if($session->browser_version)
                                        <span class="text-muted">({{ $session->browser_version }})</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Location</th>
                                <td>
                                    @if($session->city && $session->country)
                                        {{ $session->city }}, {{ $session->country }}
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Login Time</th>
                                <td>
                                    @if($session->login_at)
                                        {{ $session->login_at->format('M d, Y H:i:s') }}
                                        <br><small class="text-muted">{{ $session->login_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Last Activity</th>
                                <td>
                                    @if($session->last_activity)
                                        {{ $session->last_activity->format('M d, Y H:i:s') }}
                                        <br><small class="text-muted">{{ $session->last_activity->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Logout Time</th>
                                <td>
                                    @if($session->logout_at)
                                        {{ $session->logout_at->format('M d, Y H:i:s') }}
                                        <br><small class="text-muted">{{ $session->logout_at->diffForHumans() }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Session Duration</th>
                                <td>
                                    @if($session->login_at)
                                        @if($session->logout_at)
                                            {{ $session->login_at->diffForHumans($session->logout_at, true) }}
                                        @else
                                            {{ $session->login_at->diffForHumans(now(), true) }} (ongoing)
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Created At</th>
                                <td>{{ $session->created_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- User Agent -->
        @if($session->user_agent)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">User Agent</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded"><code>{{ $session->user_agent }}</code></pre>
                <button class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ $session->user_agent }}')">
                    <i class="ti ti-copy me-1"></i>Copy User Agent
                </button>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <!-- Admin Info -->
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-lg bg-primary-subtle rounded mx-auto mb-3">
                    <span class="avatar-title bg-primary-subtle text-primary fs-24">
                        {{ substr($session->admin->name, 0, 1) }}
                    </span>
                </div>
                
                <h5 class="mb-1">{{ $session->admin->name }}</h5>
                <p class="text-muted mb-3">{{ $session->admin->email }}</p>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold text-primary">{{ $session->admin->created_at->format('M Y') }}</div>
                        <small class="text-muted">Member since</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold {{ $session->admin->active ? 'text-success' : 'text-danger' }}">
                            {{ $session->admin->active ? 'Active' : 'Inactive' }}
                        </div>
                        <small class="text-muted">Status</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Session Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Session Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($session->is_active)
                        <button type="button" class="btn btn-danger" onclick="terminateSession({{ $session->id }})">
                            <i class="ti ti-logout me-2"></i>Terminate Session
                        </button>
                    @else
                        <span class="btn btn-secondary disabled">
                            <i class="ti ti-logout me-2"></i>Session Already Terminated
                        </span>
                    @endif
                    
                    <a href="{{ route('admin.admin-users.show', $session->admin->id) }}" class="btn btn-outline-info">
                        <i class="ti ti-user me-2"></i>View Admin Profile
                    </a>
                    
                    @if($session->admin_id !== auth()->guard('admin')->id())
                        <button type="button" class="btn btn-warning" onclick="terminateAllForAdmin({{ $session->admin_id }})">
                            <i class="ti ti-user-x me-2"></i>Terminate All for Admin
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Session Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Session Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold text-primary">{{ $stats['total_sessions_for_admin'] ?? 0 }}</div>
                        <small class="text-muted">Total Sessions</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold text-info">{{ $stats['active_sessions_for_admin'] ?? 0 }}</div>
                        <small class="text-muted">Active Sessions</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Security Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Security Information</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">IP Address</span>
                    <span class="badge bg-{{ $session->ip_address ? 'success' : 'warning' }}-subtle text-{{ $session->ip_address ? 'success' : 'warning' }}">
                        {{ $session->ip_address ? 'Tracked' : 'Unknown' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Location</span>
                    <span class="badge bg-{{ $session->city ? 'success' : 'warning' }}-subtle text-{{ $session->city ? 'success' : 'warning' }}">
                        {{ $session->city ? 'Tracked' : 'Unknown' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Device Info</span>
                    <span class="badge bg-{{ $session->device_name ? 'success' : 'warning' }}-subtle text-{{ $session->device_name ? 'success' : 'warning' }}">
                        {{ $session->device_name ? 'Tracked' : 'Unknown' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">User Agent</span>
                    <span class="badge bg-{{ $session->user_agent ? 'success' : 'warning' }}-subtle text-{{ $session->user_agent ? 'success' : 'warning' }}">
                        {{ $session->user_agent ? 'Tracked' : 'Unknown' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        window.showNotification('Copied to clipboard!', 'success');
    }, function(err) {
        window.showNotification('Failed to copy to clipboard', 'error');
    });
}

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
</script>
@endpush

