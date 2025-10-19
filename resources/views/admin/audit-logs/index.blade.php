@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Audit Logs</h4>
        <p class="text-muted mb-0">Track and monitor all administrative actions</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Audit Logs</li>
        </ol>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Actions</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_logs'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Actions Today</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['logs_today'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Actions This Month</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['logs_this_month'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Unique Admins</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ count($filterOptions['admins'] ?? []) }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3">
            <div class="col-md-2">
                <label for="admin_id" class="form-label">Admin</label>
                <select class="form-select" id="admin_id" name="admin_id">
                    <option value="">All Admins</option>
                    @foreach($filterOptions['admins'] ?? [] as $admin)
                        <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="action" class="form-label">Action</label>
                <select class="form-select" id="action" name="action">
                    <option value="">All Actions</option>
                    @foreach($filterOptions['actions'] ?? [] as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="model_type" class="form-label">Model Type</label>
                <select class="form-select" id="model_type" name="model_type">
                    <option value="">All Types</option>
                    @foreach($filterOptions['model_types'] ?? [] as $modelType)
                        <option value="{{ $modelType }}" {{ request('model_type') == $modelType ? 'selected' : '' }}>
                            {{ class_basename($modelType) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="Search logs...">
            </div>
            <div class="col-12">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i>Reset
                    </a>
                    <a href="{{ route('admin.audit-logs.export', request()->query()) }}" class="btn btn-success">
                        <i class="ti ti-download me-1"></i>Export CSV
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Audit Logs Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Audit Logs</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-nowrap" id="auditLogsTable">
                <thead class="table-light">
                    <tr>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Model</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary-subtle rounded me-3">
                                    <span class="avatar-title bg-primary-subtle text-primary fs-16">
                                        {{ substr($log->admin->name ?? 'A', 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $log->admin->name ?? 'Unknown' }}</h6>
                                    <small class="text-muted">ID: {{ $log->admin_id ?? 'N/A' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $log->action == 'created' ? 'success' : ($log->action == 'updated' ? 'warning' : ($log->action == 'deleted' ? 'danger' : 'info')) }}-subtle text-{{ $log->action == 'created' ? 'success' : ($log->action == 'updated' ? 'warning' : ($log->action == 'deleted' ? 'danger' : 'info')) }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                  title="{{ $log->description }}">
                                {{ $log->description }}
                            </span>
                        </td>
                        <td>
                            @if($log->model_type)
                                <span class="badge bg-secondary-subtle text-secondary">
                                    {{ class_basename($log->model_type) }}
                                </span>
                                @if($log->model_id)
                                    <br><small class="text-muted">ID: {{ $log->model_id }}</small>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ $log->ip_address ?? 'N/A' }}</span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $log->created_at->format('M d, Y H:i') }}</span>
                            <br><small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.audit-logs.show', $log->id) }}">
                                            <i class="ti ti-eye me-2"></i>View Details
                                        </a>
                                    </li>
                                    @if($log->old_values || $log->new_values)
                                    <li>
                                        <button class="dropdown-item" onclick="showChanges({{ $log->id }})">
                                            <i class="ti ti-git-compare me-2"></i>View Changes
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
                                <i class="ti ti-file-text fs-48 mb-3"></i>
                                <h5>No Audit Logs Found</h5>
                                <p>No audit logs match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Changes Modal -->
<div class="modal fade" id="changesModal" tabindex="-1" aria-labelledby="changesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changesModalLabel">Audit Log Changes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="changesContent">
                    <!-- Changes will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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
    $('#auditLogsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[5, 'desc']],
        columnDefs: [
            { orderable: false, targets: [6] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search audit logs...",
            infoFiltered: ""
        }
    });
});

function showChanges(logId) {
    // This would typically make an AJAX call to get the changes
    // For now, we'll show a placeholder
    document.getElementById('changesContent').innerHTML = `
        <div class="text-center py-4">
            <i class="ti ti-loader fs-48 text-muted mb-3"></i>
            <p class="text-muted">Loading changes...</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('changesModal'));
    modal.show();
    
    // Simulate loading changes
    setTimeout(() => {
        document.getElementById('changesContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Old Values</h6>
                    <pre class="bg-light p-3 rounded"><code>Loading old values...</code></pre>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">New Values</h6>
                    <pre class="bg-light p-3 rounded"><code>Loading new values...</code></pre>
                </div>
            </div>
        `;
    }, 1000);
}
</script>
@endpush