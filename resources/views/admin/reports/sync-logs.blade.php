@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Sync Logs Report</h4>
                <p class="text-muted mb-0">Monitor data synchronization operations and track sync performance</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Syncs</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_syncs'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Successful</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['successful_syncs'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Failed</h5>
                <h3 class="mb-0 fw-bold text-danger">{{ $stats['failed_syncs'] }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">In Progress</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['in_progress_syncs'] }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Sync Log Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reports.sync-logs') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Successful</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
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
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
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
                            <i class="ti ti-search me-1"></i>Filter Logs
                        </button>
                        <a href="{{ route('admin.reports.sync-logs') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-refresh me-1"></i>Clear Filters
                        </a>
                        <button type="button" class="btn btn-outline-info" onclick="exportLogs()">
                            <i class="ti ti-download me-1"></i>Export Logs
                        </button>
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
                <h5 class="card-title mb-0">Sync Logs</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Network</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Records</th>
                                <th>Duration</th>
                                <th>Started At</th>
                                <th>Completed At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded me-2">
                                                <span class="avatar-title bg-primary-subtle text-primary">
                                                    {{ substr($log->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $log->user->name }}</h6>
                                                <small class="text-muted">{{ $log->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-info-subtle rounded me-2">
                                                <span class="avatar-title bg-info-subtle text-info">
                                                    {{ substr($log->network->display_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $log->network->display_name }}</h6>
                                                <small class="text-muted">{{ $log->network->name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($log->sync_type) }}</span>
                                    </td>
                                    <td>
                                        @if($log->status === 'success')
                                            <span class="badge bg-success">Success</span>
                                        @elseif($log->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @elseif($log->status === 'in_progress')
                                            <span class="badge bg-warning">In Progress</span>
                                        @elseif($log->status === 'cancelled')
                                            <span class="badge bg-secondary">Cancelled</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($log->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="fw-bold">{{ $log->records_processed ?? 0 }}</div>
                                            @if($log->records_processed && $log->records_total)
                                                <small class="text-muted">of {{ $log->records_total }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($log->started_at && $log->completed_at)
                                            @php
                                                $duration = $log->started_at->diffInSeconds($log->completed_at);
                                                $minutes = floor($duration / 60);
                                                $seconds = $duration % 60;
                                            @endphp
                                            <div>{{ $minutes }}m {{ $seconds }}s</div>
                                        @elseif($log->started_at)
                                            <div class="text-warning">Running...</div>
                                        @else
                                            <div class="text-muted">N/A</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $log->started_at ? $log->started_at->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $log->started_at ? $log->started_at->format('H:i:s') : '' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $log->completed_at ? $log->completed_at->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $log->completed_at ? $log->completed_at->format('H:i:s') : '' }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" onclick="viewLogDetails({{ $log->id }})">
                                                        <i class="ti ti-eye me-2"></i>View Details
                                                    </a>
                                                </li>
                                                @if($log->status === 'failed')
                                                    <li>
                                                        <a class="dropdown-item text-info" href="#" onclick="retrySync({{ $log->id }})">
                                                            <i class="ti ti-refresh me-2"></i>Retry Sync
                                                        </a>
                                                    </li>
                                                @endif
                                                @if($log->status === 'in_progress')
                                                    <li>
                                                        <a class="dropdown-item text-warning" href="#" onclick="cancelSync({{ $log->id }})">
                                                            <i class="ti ti-x me-2"></i>Cancel Sync
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
                                            <p>No sync logs found matching your criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($logs->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-labelledby="logDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logDetailsModalLabel">Sync Log Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="logDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewLogDetails(logId) {
    // Load log details via AJAX
    fetch(`/admin/reports/sync-logs/${logId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('logDetailsContent').innerHTML = data.html;
                new bootstrap.Modal(document.getElementById('logDetailsModal')).show();
            } else {
                alert('Failed to load log details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading log details');
        });
}

function retrySync(logId) {
    if (confirm('Are you sure you want to retry this sync operation?')) {
        fetch(`/admin/reports/sync-logs/${logId}/retry`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sync retry initiated successfully');
                location.reload();
            } else {
                alert('Failed to retry sync: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error retrying sync');
        });
    }
}

function cancelSync(logId) {
    if (confirm('Are you sure you want to cancel this sync operation?')) {
        fetch(`/admin/reports/sync-logs/${logId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sync operation cancelled');
                location.reload();
            } else {
                alert('Failed to cancel sync: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cancelling sync');
        });
    }
}

function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    
    window.open(`{{ route('admin.reports.sync-logs') }}?${params.toString()}`, '_blank');
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh every 30 seconds for in-progress syncs
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            const inProgressCount = {{ $stats['in_progress_syncs'] }};
            if (inProgressCount > 0) {
                location.reload();
            }
        }
    }, 30000);
});
</script>
@endpush
