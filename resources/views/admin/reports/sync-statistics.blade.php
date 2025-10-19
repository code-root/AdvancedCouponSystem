@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Sync Statistics</h4>
                <p class="text-muted mb-0">Comprehensive analytics and performance metrics for data synchronization</p>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Syncs Today</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['syncs_today'] ?? 0 }}</h3>
                <small class="text-success">
                    <i class="ti ti-trending-up me-1"></i>
                    {{ $stats['today_syncs_growth'] ?? 0 }}% vs yesterday
                </small>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Success Rate</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['success_rate'] ?? 0 }}%</h3>
                <small class="text-muted">Overall</small>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Avg Duration</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['avg_duration'] ?? 0 }}s</h3>
                <small class="text-muted">Per sync operation</small>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Records Synced</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ number_format($stats['total_records_synced'] ?? 0) }}</h3>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Sync Activity Over Time</h5>
            </div>
            <div class="card-body">
                <canvas id="syncActivityChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Sync Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="syncStatusChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Network Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Network</th>
                                <th>Success Rate</th>
                                <th>Avg Duration</th>
                                <th>Last Sync</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($networkStats ?? [] as $network)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded me-2">
                                                <span class="avatar-title bg-primary-subtle text-primary">
                                                    {{ substr($network->display_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $network->display_name }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 60px; height: 8px;">
                                                <div class="progress-bar {{ $network->success_rate >= 90 ? 'bg-success' : ($network->success_rate >= 70 ? 'bg-warning' : 'bg-danger') }}" 
                                                     style="width: {{ $network->success_rate }}%"></div>
                                            </div>
                                            <span class="fw-bold">{{ $network->success_rate }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $network->avg_duration }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $network->last_sync ? $network->last_sync->diffForHumans() : 'Never' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Users by Sync Activity</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Syncs</th>
                                <th>Success Rate</th>
                                <th>Records</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topUsers ?? [] as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-info-subtle rounded me-2">
                                                <span class="avatar-title bg-info-subtle text-info">
                                                    {{ substr($user['name'], 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user['name'] }}</h6>
                                                <small class="text-muted">{{ $user['email'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $user['total_syncs'] }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 50px; height: 6px;">
                                                <div class="progress-bar {{ $user['success_rate'] >= 90 ? 'bg-success' : ($user['success_rate'] >= 70 ? 'bg-warning' : 'bg-danger') }}" 
                                                     style="width: {{ $user['success_rate'] }}%"></div>
                                            </div>
                                            <span class="text-muted">{{ $user['success_rate'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($user['total_records_synced']) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Sync Activity</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Network</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Duration</th>
                                <th>Records</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivity ?? [] as $activity)
                                <tr>
                                    <td>
                                        <div>{{ $activity->started_at ? $activity->started_at->format('M d, Y') : 'N/A' }}</div>
                                        <small class="text-muted">{{ $activity->started_at ? $activity->started_at->format('H:i:s') : 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded me-2">
                                                <span class="avatar-title bg-primary-subtle text-primary">
                                                    {{ $activity->user ? substr($activity->user->name, 0, 1) : 'N' }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $activity->user->name ?? 'N/A' }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-info-subtle rounded me-2">
                                                <span class="avatar-title bg-info-subtle text-info">
                                                    {{ $activity->network ? substr($activity->network->display_name, 0, 1) : 'N' }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $activity->network->display_name ?? 'N/A' }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($activity->sync_type ?? 'N/A') }}</span>
                                    </td>
                                    <td>
                                        @if($activity->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($activity->status === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @elseif($activity->status === 'processing')
                                            <span class="badge bg-warning">Processing</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($activity->status ?? 'Unknown') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($activity->duration_seconds)
                                            @php
                                                $minutes = floor($activity->duration_seconds / 60);
                                                $seconds = $activity->duration_seconds % 60;
                                            @endphp
                                            <div>{{ $minutes }}m {{ $seconds }}s</div>
                                        @elseif($activity->started_at && !$activity->completed_at)
                                            <div class="text-warning">Running...</div>
                                        @else
                                            <div class="text-muted">N/A</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="fw-bold">{{ $activity->records_processed ?? 0 }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ti ti-chart-line-off fs-48 mb-3"></i>
                                            <p>No recent sync activity found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sync Activity Chart
    const activityCtx = document.getElementById('syncActivityChart').getContext('2d');
    new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels'] ?? []) !!},
            datasets: [{
                label: 'Successful Syncs',
                data: {!! json_encode($chartData['successful'] ?? []) !!},
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4
            }, {
                label: 'Failed Syncs',
                data: {!! json_encode($chartData['failed'] ?? []) !!},
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });

    // Sync Status Chart
    const statusCtx = document.getElementById('syncStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Successful', 'Failed', 'Processing'],
            datasets: [{
                data: [
                    {{ $stats['successful_syncs'] ?? 0 }},
                    {{ $stats['failed_syncs'] ?? 0 }},
                    {{ $stats['running_syncs'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgb(34, 197, 94)',
                    'rgb(239, 68, 68)',
                    'rgb(245, 158, 11)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Auto-refresh every 60 seconds
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 60000);
});
</script>
@endpush
