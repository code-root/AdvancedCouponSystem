@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">{{ $user->name }}</h4>
                <p class="text-muted mb-0">User details and activity</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.user-management.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Users
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.user-management.edit', $user->id) }}" class="btn btn-outline-primary">
                            <i class="ti ti-edit me-1"></i>Edit User
                        </a>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-warning" onclick="impersonateUser({{ $user->id }})">
                            <i class="ti ti-user-check me-1"></i>Impersonate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Overview -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">User Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Name</label>
                            <p class="text-muted">{{ $user->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <p class="text-muted">{{ $user->email }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Verified</label>
                            <p>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ti ti-check me-1"></i>Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ti ti-alert-circle me-1"></i>Not Verified
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Joined</label>
                            <p class="text-muted">{{ $user->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Last Updated</label>
                            <p class="text-muted">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">User Type</label>
                            <p>
                                @if($user->isSubUser())
                                    <span class="badge bg-info-subtle text-info">Sub User</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary">Main User</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Quick Stats</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Connected Networks</span>
                    <span class="fw-semibold text-primary">{{ $usageStats['connected_networks'] }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Campaigns</span>
                    <span class="fw-semibold text-info">{{ $user->campaigns->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Orders</span>
                    <span class="fw-semibold text-warning">{{ number_format($usageStats['total_orders']) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Revenue</span>
                    <span class="fw-semibold text-success">${{ number_format($usageStats['total_revenue'], 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Last Sync</span>
                    <span class="fw-semibold">{{ $usageStats['last_sync'] ? $usageStats['last_sync']->format('M d, H:i') : 'Never' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subscription Information -->
@if($user->subscription)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Subscription Details</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Plan</label>
                            <p class="text-muted">{{ $user->subscription->plan->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <p>
                                <span class="badge bg-{{ $user->subscription->status === 'active' ? 'success' : ($user->subscription->status === 'trial' ? 'info' : 'warning') }}-subtle text-{{ $user->subscription->status === 'active' ? 'success' : ($user->subscription->status === 'trial' ? 'info' : 'warning') }}">
                                    {{ ucfirst($user->subscription->status) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Started</label>
                            <p class="text-muted">{{ $user->subscription->starts_at ? $user->subscription->starts_at->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Expires</label>
                            <p class="text-muted">{{ $user->subscription->ends_at ? $user->subscription->ends_at->format('M d, Y') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Usage Statistics -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Usage Statistics</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Daily Usage</h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">Syncs</span>
                                <span class="fw-semibold">{{ $usageStats['daily_syncs'] }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Monthly Usage</h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">Syncs</span>
                                <span class="fw-semibold">{{ $usageStats['monthly_syncs'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connected Networks -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Connected Networks</h4>
            </div>
            <div class="card-body">
                @if($user->networkConnections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Network</th>
                                <th>Connection Name</th>
                                <th>Status</th>
                                <th>Connected At</th>
                                <th>Last Sync</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->networkConnections as $connection)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-info-subtle text-info">
                                                    <i class="ti ti-affiliate"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $connection->network->display_name }}</h6>
                                            <small class="text-muted">{{ $connection->network->name }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $connection->connection_name }}</td>
                                <td>
                                    <span class="badge bg-{{ $connection->is_connected ? 'success' : 'danger' }}-subtle text-{{ $connection->is_connected ? 'success' : 'danger' }}">
                                        {{ $connection->is_connected ? 'Connected' : 'Disconnected' }}
                                    </span>
                                </td>
                                <td>{{ $connection->connected_at ? $connection->connected_at->format('M d, Y') : 'Never' }}</td>
                                <td>
                                    @php
                                        $lastSync = \App\Models\SyncLog::where('user_id', $user->id)
                                            ->where('network_id', $connection->network_id)
                                            ->latest('started_at')
                                            ->first();
                                    @endphp
                                    {{ $lastSync ? $lastSync->started_at->format('M d, Y H:i') : 'Never' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="ti ti-affiliate fs-48 mb-3"></i>
                        <h5>No Connected Networks</h5>
                        <p>This user hasn't connected to any networks yet.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Sync Logs -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Recent Sync Logs</h4>
            </div>
            <div class="card-body">
                @if($recentSyncLogs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Network</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Duration</th>
                                <th>Records</th>
                                <th>Started</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSyncLogs as $log)
                            <tr>
                                <td>{{ $log->network->display_name }}</td>
                                <td>
                                    <span class="badge bg-{{ $log->sync_type === 'manual' ? 'primary' : 'secondary' }}-subtle text-{{ $log->sync_type === 'manual' ? 'primary' : 'secondary' }}">
                                        {{ ucfirst($log->sync_type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->status === 'completed' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}-subtle text-{{ $log->status === 'completed' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td>{{ $log->duration_seconds ? $log->duration_seconds . 's' : 'N/A' }}</td>
                                <td>{{ number_format($log->records_synced) }}</td>
                                <td>{{ $log->started_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="ti ti-refresh fs-48 mb-3"></i>
                        <h5>No Sync Logs</h5>
                        <p>No sync operations have been performed yet.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Trend -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Monthly Revenue Trend</h4>
            </div>
            <div class="card-body">
                @if($monthlyRevenue->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Revenue</th>
                                <th>Orders</th>
                                <th>Avg Order Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyRevenue as $month)
                            <tr>
                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}</td>
                                <td><span class="fw-semibold text-success">${{ number_format($month->revenue, 2) }}</span></td>
                                <td><span class="fw-semibold">{{ number_format($month->orders) }}</span></td>
                                <td><span class="fw-semibold">${{ number_format($month->revenue / max($month->orders, 1), 2) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="ti ti-chart-line fs-48 mb-3"></i>
                        <h5>No Revenue Data</h5>
                        <p>No revenue data available for this user.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function impersonateUser(userId) {
    if (confirm('Are you sure you want to impersonate this user? You will be logged in as them.')) {
        fetch(`/admin/user-management/${userId}/impersonate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                alert('Error: ' + (data.message || 'Failed to impersonate user'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error impersonating user');
        });
    }
}
</script>
@endsection



