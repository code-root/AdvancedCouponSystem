@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-box">
    <div class="page-title-right">
        <ol class="breadcrumb m-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
            <li class="breadcrumb-item active">Advanced Reports</li>
        </ol>
    </div>
    <h4 class="page-title">{{ $title }}</h4>
    <p class="text-muted mb-0">{{ $subtitle }}</p>
</div>

<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ $stats['total_reports'] }}</h4>
                        <p class="text-muted mb-0">Total Reports</p>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                            <i class="ti ti-chart-bar"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ $stats['active_reports'] }}</h4>
                        <p class="text-muted mb-0">Active Reports</p>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success-subtle text-success rounded">
                            <i class="ti ti-check-circle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ number_format($stats['total_data_points']) }}</h4>
                        <p class="text-muted mb-0">Data Points</p>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-info-subtle text-info rounded">
                            <i class="ti ti-database"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ $stats['last_updated'] }}</h4>
                        <p class="text-muted mb-0">Last Updated</p>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning-subtle text-warning rounded">
                            <i class="ti ti-clock"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- User Sessions Report -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ti ti-users me-2"></i>User Sessions Report
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($reportSummaries['user_sessions']['total_sessions']) }}</h3>
                            <p class="text-muted mb-0">Total Sessions</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ number_format($reportSummaries['user_sessions']['active_sessions']) }}</h3>
                            <p class="text-muted mb-0">Active Sessions</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-info">{{ number_format($reportSummaries['user_sessions']['unique_users']) }}</h4>
                            <p class="text-muted mb-0">Unique Users</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-warning">{{ number_format($reportSummaries['user_sessions']['avg_session_duration']) }}m</h4>
                            <p class="text-muted mb-0">Avg Duration</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.reports.user-sessions.index') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-eye me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Sessions Report -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ti ti-network me-2"></i>Network Sessions Report
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($reportSummaries['network_sessions']['total_sessions']) }}</h3>
                            <p class="text-muted mb-0">Total Sessions</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ number_format($reportSummaries['network_sessions']['active_sessions']) }}</h3>
                            <p class="text-muted mb-0">Active Sessions</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-info">{{ number_format($reportSummaries['network_sessions']['unique_networks']) }}</h4>
                            <p class="text-muted mb-0">Unique Networks</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-warning">{{ number_format($reportSummaries['network_sessions']['avg_session_duration']) }}m</h4>
                            <p class="text-muted mb-0">Avg Duration</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.reports.network-sessions.index') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-eye me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Sync Logs Report -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ti ti-sync me-2"></i>Sync Logs Report
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($reportSummaries['sync_logs']['total_syncs']) }}</h3>
                            <p class="text-muted mb-0">Total Syncs</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">{{ number_format($reportSummaries['sync_logs']['successful_syncs']) }}</h3>
                            <p class="text-muted mb-0">Successful</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-danger">{{ number_format($reportSummaries['sync_logs']['failed_syncs']) }}</h4>
                            <p class="text-muted mb-0">Failed</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-warning">{{ number_format($reportSummaries['sync_logs']['avg_duration'], 2) }}s</h4>
                            <p class="text-muted mb-0">Avg Duration</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.reports.sync-logs.index') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-eye me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Statistics Report -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ti ti-chart-line me-2"></i>Sync Statistics Report
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-primary">{{ number_format($reportSummaries['sync_statistics']['total_records']) }}</h3>
                            <p class="text-muted mb-0">Total Records</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h3 class="text-success">${{ number_format($reportSummaries['sync_statistics']['total_revenue'], 2) }}</h3>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-info">{{ number_format($reportSummaries['sync_statistics']['avg_records_per_sync'], 0) }}</h4>
                            <p class="text-muted mb-0">Avg Records/Sync</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-warning">{{ number_format($reportSummaries['sync_statistics']['success_rate'], 1) }}%</h4>
                            <p class="text-muted mb-0">Success Rate</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.reports.sync-statistics.index') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-eye me-1"></i>View Report
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="ti ti-bolt me-2"></i>Quick Actions
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.user-sessions.export') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="ti ti-download me-1"></i>Export User Sessions
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.network-sessions.export') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="ti ti-download me-1"></i>Export Network Sessions
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.sync-logs.export') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="ti ti-download me-1"></i>Export Sync Logs
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.sync-statistics.export') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="ti ti-download me-1"></i>Export Statistics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh data every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endpush