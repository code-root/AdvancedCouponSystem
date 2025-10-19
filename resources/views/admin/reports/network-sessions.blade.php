@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Network Sessions Report</h4>
                <p class="text-muted mb-0">Monitor network authentication sessions and API connections</p>
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
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_sessions'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Sessions</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_sessions'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Connected Networks</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['connected_networks'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Failed Logins</h5>
                <h3 class="mb-0 fw-bold text-danger">{{ $stats['failed_logins'] }}</h3>
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
                <form method="GET" action="{{ route('admin.reports.network-sessions') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="network_id" class="form-label">Network</label>
                        <select class="form-select" id="network_id" name="network_id">
                            <option value="">All Networks</option>
                            @foreach($networks as $network)
                                <option value="{{ $network->id }}" {{ request('network_id') == $network->id ? 'selected' : '' }}>
                                    {{ $network->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
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
                    
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>Filter Sessions
                        </button>
                        <a href="{{ route('admin.reports.network-sessions') }}" class="btn btn-outline-secondary">
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
                <h5 class="card-title mb-0">Network Sessions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Network</th>
                                <th>User</th>
                                <th>Session ID</th>
                                <th>Status</th>
                                <th>Login Time</th>
                                <th>Last Activity</th>
                                <th>Expires At</th>
                                <th>IP Address</th>
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
                                                    {{ substr($session->network->display_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $session->network->display_name }}</h6>
                                                <small class="text-muted">{{ $session->network->name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $session->user->name }}</h6>
                                            <small class="text-muted">{{ $session->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-truncate" style="max-width: 100px;" title="{{ $session->session_id }}">
                                            {{ $session->session_id }}
                                        </code>
                                    </td>
                                    <td>
                                        @if($session->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($session->status === 'expired')
                                            <span class="badge bg-warning">Expired</span>
                                        @elseif($session->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($session->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $session->login_at ? $session->login_at->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $session->login_at ? $session->login_at->format('H:i:s') : '' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $session->last_activity ? $session->last_activity->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $session->last_activity ? $session->last_activity->format('H:i:s') : '' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $session->expires_at ? $session->expires_at->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $session->expires_at ? $session->expires_at->format('H:i:s') : '' }}</small>
                                    </td>
                                    <td>
                                        <code>{{ $session->ip_address }}</code>
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
                                                @if($session->status === 'active')
                                                    <li>
                                                        <a class="dropdown-item text-warning" href="#" onclick="terminateSession({{ $session->id }})">
                                                            <i class="ti ti-logout me-2"></i>Terminate Session
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($session->status === 'failed')
                                                    <li>
                                                        <a class="dropdown-item text-info" href="#" onclick="retryConnection({{ $session->id }})">
                                                            <i class="ti ti-refresh me-2"></i>Retry Connection
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
                                            <i class="ti ti-network-off fs-48 mb-3"></i>
                                            <p>No network sessions found matching your criteria.</p>
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
                <h5 class="modal-title" id="sessionDetailsModalLabel">Network Session Details</h5>
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
    fetch(`/admin/reports/network-sessions/${sessionId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('sessionDetailsContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('sessionDetailsModal')).show();
            } else {
                alert('Failed to load session details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading session details');
        });
}

function terminateSession(sessionId) {
    if (confirm('Are you sure you want to terminate this network session?')) {
        fetch(`/admin/reports/network-sessions/${sessionId}/terminate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Network session terminated successfully');
                location.reload();
            } else {
                alert('Failed to terminate session: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error terminating session');
        });
    }
}

function retryConnection(sessionId) {
    if (confirm('Are you sure you want to retry the network connection?')) {
        fetch(`/admin/reports/network-sessions/${sessionId}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Connection retry initiated');
                location.reload();
            } else {
                alert('Failed to retry connection: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error retrying connection');
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 60 seconds for network sessions
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 60000);
});
</script>
@endpush
