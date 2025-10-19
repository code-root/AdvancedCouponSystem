@extends('dashboard.layouts.vertical', ['title' => 'Sync Schedules'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Data Sync', 'title' => 'Sync Schedules'])

    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary-subtle text-primary rounded">
                                    <i class="ti ti-calendar-stats fs-3"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Schedules</p>
                            <h4 class="mb-0">{{ $schedules->total() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success-subtle text-success rounded">
                                    <i class="ti ti-check fs-3"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Active Schedules</p>
                            <h4 class="mb-0">{{ $schedules->where('is_active', true)->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-warning-subtle text-warning rounded">
                                    <i class="ti ti-clock-pause fs-3"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Inactive Schedules</p>
                            <h4 class="mb-0">{{ $schedules->where('is_active', false)->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm">
                                <span class="avatar-title bg-info-subtle text-info rounded">
                                    <i class="ti ti-refresh fs-3"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1">Total Runs Today</p>
                            <h4 class="mb-0">{{ $schedules->sum('runs_today') }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light d-flex justify-content-between align-items-center">
                    <h4 class="header-title mb-0">All Sync Schedules</h4>
                    <a href="{{ route('dashboard.sync.schedules.create') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i> Create Schedule
                    </a>
                </div>
                <div class="card-body">
                    @if($schedules->isEmpty())
                        <div class="text-center py-5">
                            <i class="ti ti-calendar-off text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-3 mb-0">No schedules found</p>
                            <a href="{{ route('dashboard.sync.schedules.create') }}" class="btn btn-primary btn-sm mt-3">
                                <i class="ti ti-plus me-1"></i> Create Your First Schedule
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th>Schedule Name</th>
                                        <th>Networks</th>
                                        <th>Sync Type</th>
                                        <th>Interval</th>
                                        <th>Runs Today</th>
                                        <th>Last Run</th>
                                        <th>Next Run</th>
                                        <th>Status</th>
                                        <th class="text-center" style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $schedule)
                                        <tr id="schedule-{{ $schedule->id }}">
                                            <td>
                                                <strong>{{ $schedule->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $schedule->date_range_type }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $networks = $schedule->networks();
                                                @endphp
                                                @foreach($networks->take(2) as $network)
                                                    <span class="badge bg-info-subtle text-info">{{ $network->display_name }}</span>
                                                @endforeach
                                                @if($networks->count() > 2)
                                                    <span class="badge bg-secondary">+{{ $networks->count() - 2 }} more</span>
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
                                                    $badgeClass = $typeBadges[$schedule->sync_type] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $badgeClass }}">{{ ucfirst($schedule->sync_type) }}</span>
                                            </td>
                                            <td>
                                                @if($schedule->interval_minutes >= 1440)
                                                    {{ $schedule->interval_minutes / 1440 }} day(s)
                                                @elseif($schedule->interval_minutes >= 60)
                                                    {{ $schedule->interval_minutes / 60 }} hour(s)
                                                @else
                                                    {{ $schedule->interval_minutes }} min(s)
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ $schedule->runs_today }} / {{ $schedule->max_runs_per_day }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($schedule->last_run_at)
                                                    <small>{{ $schedule->last_run_at->diffForHumans() }}</small>
                                                @else
                                                    <small class="text-muted">Never</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($schedule->next_run_at)
                                                    <small>{{ $schedule->next_run_at->diffForHumans() }}</small>
                                                @else
                                                    <small class="text-muted">-</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="status-badge badge {{ $schedule->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                                    {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-light" onclick="toggleSchedule({{ $schedule->id }})" title="Toggle Status">
                                                        <i class="ti ti-power"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-light" onclick="runNow({{ $schedule->id }})" title="Run Now">
                                                        <i class="ti ti-player-play"></i>
                                                    </button>
                                                    <a href="{{ route('dashboard.sync.schedules.edit', $schedule->id) }}" class="btn btn-light" title="Edit">
                                                        <i class="ti ti-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-light text-danger" onclick="deleteSchedule({{ $schedule->id }})" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $schedules->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function toggleSchedule(id) {
    if (confirm('Are you sure you want to toggle this schedule status?')) {
        fetch(`/sync/schedules/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update status badge
                const row = document.querySelector(`#schedule-${id}`);
                const badge = row.querySelector('.status-badge');
                if (data.is_active) {
                    badge.className = 'status-badge badge bg-success-subtle text-success';
                    badge.textContent = 'Active';
                } else {
                    badge.className = 'status-badge badge bg-danger-subtle text-danger';
                    badge.textContent = 'Inactive';
                }
                
                // Show success message
                showToast('success', data.message);
            }
        })
        .catch(error => {
            showToast('error', 'Failed to toggle schedule status');
            console.error('Error:', error);
        });
    }
}

function runNow(id) {
    if (confirm('Are you sure you want to run this schedule now?')) {
        fetch(`/sync/schedules/${id}/run`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', data.message);
            } else {
                showToast('error', data.message);
            }
        })
        .catch(error => {
            showToast('error', 'Failed to run schedule');
            console.error('Error:', error);
        });
    }
}

function deleteSchedule(id) {
    if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
        fetch(`/sync/schedules/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            } else {
                throw new Error('Failed to delete');
            }
        })
        .catch(error => {
            showToast('error', 'Failed to delete schedule');
            console.error('Error:', error);
        });
    }
}

function showToast(type, message) {
    const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const toast = `
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    // Create toast container if not exists
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    container.insertAdjacentHTML('beforeend', toast);
    const toastElement = container.lastElementChild;
    const bsToast = new bootstrap.Toast(toastElement);
    bsToast.show();
    
    setTimeout(() => toastElement.remove(), 5000);
}
</script>
@endsection

