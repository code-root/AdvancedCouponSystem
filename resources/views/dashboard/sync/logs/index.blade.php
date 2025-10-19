@extends('dashboard.layouts.vertical', ['title' => 'Sync Logs'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@endsection

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Data Sync', 'title' => 'Sync Logs'])

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Network</label>
                            <select id="networkFilter" class="form-select" data-toggle="select2">
                                <option value="">All Networks</option>
                                @foreach($networks as $network)
                                    <option value="{{ $network->id }}" {{ request('network_id') == $network->id ? 'selected' : '' }}>
                                        {{ $network->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select id="statusFilter" class="form-select">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Schedule</label>
                            <select id="scheduleFilter" class="form-select" data-toggle="select2">
                                <option value="">All Schedules</option>
                                <option value="manual">Manual Sync</option>
                                @foreach($schedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ request('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <input type="text" id="dateRangeFilter" class="form-control" placeholder="Select date range">
                        </div>
                        <div class="col-md-1">
                            <button type="button" id="applyFilters" class="btn btn-primary w-100">
                                <i class="ti ti-filter"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <h4 class="header-title mb-0">Sync History</h4>
                </div>
                <div class="card-body">
                    @if($logs->isEmpty())
                        <div class="text-center py-5">
                            <i class="ti ti-file-off text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-3 mb-0">No sync logs found</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Network</th>
                                        <th>Schedule</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th>Records</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>
                                                <strong>{{ $log->created_at->format('Y-m-d') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ $log->network->display_name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($log->syncSchedule)
                                                    {{ $log->syncSchedule->name }}
                                                @else
                                                    <span class="badge bg-secondary">Manual</span>
                                                @endif
                                            </td>
                                            <td>
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
                                            </td>
                                            <td>
                                                @php
                                                    $statusBadges = [
                                                        'pending' => 'bg-secondary',
                                                        'processing' => 'bg-info-subtle text-info',
                                                        'completed' => 'bg-success-subtle text-success',
                                                        'failed' => 'bg-danger-subtle text-danger'
                                                    ];
                                                    $badgeClass = $statusBadges[$log->status] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">
                                                    @if($log->status == 'processing')
                                                        <span class="spinner-border spinner-border-sm me-1"></span>
                                                    @endif
                                                    {{ ucfirst($log->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($log->duration_seconds)
                                                    {{ $log->duration_seconds }}s
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->status == 'completed')
                                                    <div class="d-flex flex-column">
                                                        <small><strong>{{ $log->records_synced }}</strong> total</small>
                                                        @if($log->campaigns_count > 0)
                                                            <small class="text-muted">{{ $log->campaigns_count }} campaigns</small>
                                                        @endif
                                                        @if($log->coupons_count > 0)
                                                            <small class="text-muted">{{ $log->coupons_count }} coupons</small>
                                                        @endif
                                                        @if($log->purchases_count > 0)
                                                            <small class="text-muted">{{ $log->purchases_count }} purchases</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('dashboard.sync.logs.show', $log->id) }}" class="btn btn-sm btn-light">
                                                    <i class="ti ti-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $logs->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
window.addEventListener('load', function() {
    // Initialize Flatpickr
    flatpickr("#dateRangeFilter", {
        mode: "range",
        dateFormat: "Y-m-d"
    });

    // Apply filters
    document.getElementById('applyFilters').addEventListener('click', function() {
        const networkId = document.getElementById('networkFilter').value;
        const status = document.getElementById('statusFilter').value;
        const scheduleId = document.getElementById('scheduleFilter').value;
        const dateRange = document.getElementById('dateRangeFilter').value;
        
        const params = new URLSearchParams();
        if (networkId) params.append('network_id', networkId);
        if (status) params.append('status', status);
        if (scheduleId) params.append('schedule_id', scheduleId);
        if (dateRange) {
            const dates = dateRange.split(' to ');
            if (dates[0]) params.append('date_from', dates[0]);
            if (dates[1]) params.append('date_to', dates[1]);
        }
        
        window.location.href = '{{ route("sync.logs.index") }}?' + params.toString();
    });
});
</script>
@endsection

