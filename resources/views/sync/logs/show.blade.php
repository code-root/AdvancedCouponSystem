@extends('layouts.vertical', ['title' => 'Sync Log Details'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Data Sync', 'title' => 'Sync Log Details'])

    <div class="row">
        <div class="col-lg-8">
            <!-- Log Information -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Sync Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Network</label>
                            <p class="mb-0">
                                <span class="badge bg-info-subtle text-info">
                                    {{ $log->network->display_name ?? 'N/A' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Schedule</label>
                            <p class="mb-0">
                                @if($log->syncSchedule)
                                    <a href="{{ route('sync.schedules.edit', $log->syncSchedule->id) }}">
                                        {{ $log->syncSchedule->name }}
                                    </a>
                                @else
                                    <span class="badge bg-secondary">Manual Sync</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Sync Type</label>
                            <p class="mb-0">
                                @php
                                    $typeBadges = [
                                        'all' => 'bg-primary',
                                        'campaigns' => 'bg-success',
                                        'coupons' => 'bg-warning',
                                        'purchases' => 'bg-info'
                                    ];
                                    $badgeClass = $typeBadges[$log->sync_type] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($log->sync_type) }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @php
                                    $statusBadges = [
                                        'pending' => 'bg-secondary',
                                        'processing' => 'bg-info-subtle text-info',
                                        'completed' => 'bg-success-subtle text-success',
                                        'failed' => 'bg-danger-subtle text-danger'
                                    ];
                                    $badgeClass = $statusBadges[$log->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-6">
                                    @if($log->status == 'processing')
                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                    @endif
                                    {{ ucfirst($log->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">User</label>
                            <p class="mb-0">{{ $log->user->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Created At</label>
                            <p class="mb-0">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Started At</label>
                            <p class="mb-0">
                                {{ $log->started_at ? $log->started_at->format('Y-m-d H:i:s') : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Completed At</label>
                            <p class="mb-0">
                                {{ $log->completed_at ? $log->completed_at->format('Y-m-d H:i:s') : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-0">
                            <label class="text-muted small">Duration</label>
                            <p class="mb-0">
                                @if($log->duration_seconds)
                                    <strong>{{ $log->duration_seconds }}</strong> seconds
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if($log->status == 'completed')
                <!-- Statistics -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Sync Statistics</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-primary-subtle rounded">
                                    <i class="ti ti-database fs-1 text-primary"></i>
                                    <h3 class="mt-2 mb-0">{{ $log->records_synced }}</h3>
                                    <p class="text-muted mb-0 small">Total Records</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-success-subtle rounded">
                                    <i class="ti ti-folders fs-1 text-success"></i>
                                    <h3 class="mt-2 mb-0">{{ $log->campaigns_count }}</h3>
                                    <p class="text-muted mb-0 small">Campaigns</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-warning-subtle rounded">
                                    <i class="ti ti-ticket fs-1 text-warning"></i>
                                    <h3 class="mt-2 mb-0">{{ $log->coupons_count }}</h3>
                                    <p class="text-muted mb-0 small">Coupons</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-info-subtle rounded">
                                    <i class="ti ti-shopping-cart fs-1 text-info"></i>
                                    <h3 class="mt-2 mb-0">{{ $log->purchases_count }}</h3>
                                    <p class="text-muted mb-0 small">Purchases</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($log->error_message)
                <!-- Error Message -->
                <div class="card border-danger">
                    <div class="card-header bg-danger-subtle">
                        <h4 class="card-title mb-0 text-danger">
                            <i class="ti ti-alert-triangle me-2"></i>Error Message
                        </h4>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0 bg-light p-3 rounded"><code>{{ $log->error_message }}</code></pre>
                    </div>
                </div>
            @endif

            @if($log->metadata)
                <!-- Metadata -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Additional Data</h4>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0 bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('sync.logs.index') }}" class="btn btn-light">
                            <i class="ti ti-arrow-left me-1"></i> Back to Logs
                        </a>
                        @if($log->syncSchedule)
                            <a href="{{ route('sync.schedules.edit', $log->syncSchedule->id) }}" class="btn btn-primary">
                                <i class="ti ti-calendar me-1"></i> View Schedule
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Timeline</h4>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <small class="text-muted">Created</small>
                                <p class="mb-0">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                        @if($log->started_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">Started</small>
                                    <p class="mb-0">{{ $log->started_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        @endif
                        @if($log->completed_at)
                            <div class="timeline-item">
                                <div class="timeline-marker {{ $log->status == 'completed' ? 'bg-success' : 'bg-danger' }}"></div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ ucfirst($log->status) }}</small>
                                    <p class="mb-0">{{ $log->completed_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Related Logs -->
            @if($log->syncSchedule)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Recent Logs from Schedule</h4>
                    </div>
                    <div class="card-body">
                        @php
                            $recentLogs = $log->syncSchedule->syncLogs()->where('id', '!=', $log->id)->latest()->take(5)->get();
                        @endphp
                        @if($recentLogs->isEmpty())
                            <p class="text-muted mb-0">No other logs found</p>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($recentLogs as $recentLog)
                                    <a href="{{ route('sync.logs.show', $recentLog->id) }}" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <small>{{ $recentLog->created_at->format('Y-m-d H:i') }}</small>
                                            @php
                                                $statusBadges = [
                                                    'pending' => 'bg-secondary',
                                                    'processing' => 'bg-info',
                                                    'completed' => 'bg-success',
                                                    'failed' => 'bg-danger'
                                                ];
                                                $badgeClass = $statusBadges[$recentLog->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ ucfirst($recentLog->status) }}</span>
                                        </div>
                                        <small class="text-muted">{{ $recentLog->network->display_name ?? 'N/A' }}</small>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('css')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}
.timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-item:before {
    content: '';
    position: absolute;
    left: -24px;
    top: 8px;
    bottom: -12px;
    width: 2px;
    background: #e9ecef;
}
.timeline-item:last-child:before {
    display: none;
}
.timeline-marker {
    position: absolute;
    left: -30px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}
.timeline-content {
    padding-left: 0;
}
</style>
@endsection

