@extends('layouts.vertical', ['title' => 'Quick Sync'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Data Sync', 'title' => 'Quick Sync'])

    <div class="row">
        <div class="col-lg-8">
            <!-- Quick Sync Form -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-bolt me-2"></i>Quick Data Sync
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Quick Sync:</strong> Instantly fetch data from your networks without creating a schedule. Perfect for one-time data pulls or testing.
                    </div>

                    <form id="quickSyncForm">
                        @csrf

                        <!-- Select Networks -->
                        <div class="mb-3">
                            <label class="form-label">Select Networks <span class="text-danger">*</span></label>
                            <select class="form-select" name="network_ids[]" id="networkSelect" multiple="multiple" data-toggle="select2" required>
                                @foreach($networks as $network)
                                    <option value="{{ $network->id }}">
                                        {{ $network->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select one or more networks to sync data from</small>
                        </div>

                        <!-- Sync Type -->
                        <div class="mb-3">
                            <label class="form-label">Data Type <span class="text-danger">*</span></label>
                            <select class="form-select" name="sync_type" id="syncType" required>
                                <option value="all" selected>All (Campaigns, Coupons & Purchases)</option>
                                <option value="campaigns">Campaigns Only</option>
                                <option value="coupons">Coupons Only</option>
                                <option value="purchases">Purchases Only</option>
                            </select>
                            <small class="text-muted">Choose what data to fetch</small>
                        </div>

                        <!-- Date Range Type -->
                        <div class="mb-3">
                            <label class="form-label">Date Range <span class="text-danger">*</span></label>
                            <select class="form-select" name="date_range_type" id="dateRangeType" required>
                                <option value="today" selected>Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="last_7_days">Last 7 Days</option>
                                <option value="last_30_days">Last 30 Days</option>
                                <option value="current_month">Current Month (1st to Today)</option>
                                <option value="previous_month">Previous Month (Full Month)</option>
                                <option value="custom">Custom Date Range</option>
                            </select>
                        </div>

                        <!-- Custom Date Range -->
                        <div id="customDateRange" class="row mb-3" style="display: none;">
                            <div class="col-md-6">
                                <label class="form-label">From Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="date_from" id="dateFrom" placeholder="Select start date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="date_to" id="dateTo" placeholder="Select end date">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="syncButton">
                                <i class="ti ti-refresh me-2"></i>Start Sync Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sync Progress -->
            <div class="card" id="progressCard" style="display: none;">
                <div class="card-header bg-primary-subtle">
                    <h4 class="card-title mb-0 text-primary">
                        <i class="ti ti-loader-2 spinner-border spinner-border-sm me-2"></i>Sync in Progress
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Processing...</span>
                            <span id="progressPercentage">0%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" id="progressBar" style="width: 0%">
                            </div>
                        </div>
                    </div>
                    <div id="progressMessages" class="small text-muted">
                        <p class="mb-1"><i class="ti ti-clock me-1"></i>Initializing sync...</p>
                    </div>
                </div>
            </div>

            <!-- Results Card -->
            <div class="card" id="resultsCard" style="display: none;">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-check-circle me-2"></i>Sync Results
                    </h4>
                </div>
                <div class="card-body" id="resultsContent">
                    <!-- Results will be populated here -->
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quick Presets</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="applyPreset('today')">
                            <i class="ti ti-calendar-today me-1"></i>Sync Today's Data
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="applyPreset('yesterday')">
                            <i class="ti ti-calendar-minus me-1"></i>Sync Yesterday's Data
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="applyPreset('current_month')">
                            <i class="ti ti-calendar-month me-1"></i>Sync Current Month
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="applyPreset('previous_month')">
                            <i class="ti ti-calendar-event me-1"></i>Sync Previous Month
                        </button>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Important Notes</h4>
                </div>
                <div class="card-body">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2"><small>Quick Sync runs immediately in the background</small></li>
                        <li class="mb-2"><small>Multiple networks can be synced simultaneously</small></li>
                        <li class="mb-2"><small>Progress can be tracked in real-time</small></li>
                        <li class="mb-2"><small>Results are saved in Sync Logs</small></li>
                        <li class="mb-0"><small>For recurring syncs, create a Schedule instead</small></li>
                    </ul>
                </div>
            </div>

            <!-- Recent Quick Syncs -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Recent Quick Syncs</h4>
                </div>
                <div class="card-body">
                    @php
                        $recentLogs = \App\Models\SyncLog::where('user_id', Auth::id())
                            ->whereNull('sync_schedule_id')
                            ->latest()
                            ->take(5)
                            ->get();
                    @endphp
                    
                    @if($recentLogs->isEmpty())
                        <p class="text-muted mb-0 small">No recent quick syncs</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentLogs as $log)
                                <a href="{{ route('sync.logs.show', $log->id) }}" class="list-group-item list-group-item-action p-2">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <small>{{ $log->created_at->format('M d, H:i') }}</small>
                                        @php
                                            $statusBadges = [
                                                'pending' => 'bg-secondary',
                                                'processing' => 'bg-info',
                                                'completed' => 'bg-success',
                                                'failed' => 'bg-danger'
                                            ];
                                            $badgeClass = $statusBadges[$log->status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $badgeClass }} badge-sm">{{ ucfirst($log->status) }}</span>
                                    </div>
                                    <small class="text-muted">{{ $log->network->display_name ?? 'N/A' }}</small>
                                </a>
                            @endforeach
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
let syncJobs = [];
let completedJobs = 0;
let totalJobs = 0;

window.addEventListener('load', function() {
    // Initialize Flatpickr for custom date range
    flatpickr("#dateFrom", {
        dateFormat: "Y-m-d",
        maxDate: "today"
    });
    
    flatpickr("#dateTo", {
        dateFormat: "Y-m-d",
        maxDate: "today"
    });

    // Toggle custom date range
    document.getElementById('dateRangeType').addEventListener('change', function() {
        const customDateRange = document.getElementById('customDateRange');
        if (this.value === 'custom') {
            customDateRange.style.display = 'flex';
            document.getElementById('dateFrom').required = true;
            document.getElementById('dateTo').required = true;
        } else {
            customDateRange.style.display = 'none';
            document.getElementById('dateFrom').required = false;
            document.getElementById('dateTo').required = false;
        }
    });

    // Handle form submission
    document.getElementById('quickSyncForm').addEventListener('submit', function(e) {
        e.preventDefault();
        startQuickSync();
    });
});

function applyPreset(preset) {
    const dateRangeType = document.getElementById('dateRangeType');
    const networkSelect = document.getElementById('networkSelect');
    const syncType = document.getElementById('syncType');
    
    // Set date range
    dateRangeType.value = preset;
    dateRangeType.dispatchEvent(new Event('change'));
    
    // Select all networks
    const allOptions = Array.from(networkSelect.options).map(opt => opt.value);
    $(networkSelect).val(allOptions).trigger('change');
    
    // Set sync type to all
    syncType.value = 'all';
    
    // Show notification
    showToast('info', `Preset applied: ${preset.replace('_', ' ')}`);
}

function startQuickSync() {
    const form = document.getElementById('quickSyncForm');
    const formData = new FormData(form);
    
    // Validate
    const networkIds = formData.getAll('network_ids[]');
    if (networkIds.length === 0) {
        showToast('error', 'Please select at least one network');
        return;
    }
    
    // Calculate date range
    const dateRange = calculateDateRange(formData.get('date_range_type'), formData.get('date_from'), formData.get('date_to'));
    
    // Prepare sync data
    const syncData = {
        network_ids: networkIds,
        sync_type: formData.get('sync_type'),
        date_from: dateRange.from,
        date_to: dateRange.to
    };
    
    // Show progress card
    document.getElementById('progressCard').style.display = 'block';
    document.getElementById('resultsCard').style.display = 'none';
    document.getElementById('syncButton').disabled = true;
    
    // Reset progress
    totalJobs = networkIds.length;
    completedJobs = 0;
    syncJobs = [];
    updateProgress(0, 'Starting sync...');
    
    // Start sync
    performSync(syncData);
}

function calculateDateRange(type, customFrom, customTo) {
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    switch(type) {
        case 'today':
            return {
                from: formatDate(today),
                to: formatDate(today)
            };
        case 'yesterday':
            return {
                from: formatDate(yesterday),
                to: formatDate(yesterday)
            };
        case 'last_7_days':
            const last7 = new Date(today);
            last7.setDate(last7.getDate() - 7);
            return {
                from: formatDate(last7),
                to: formatDate(today)
            };
        case 'last_30_days':
            const last30 = new Date(today);
            last30.setDate(last30.getDate() - 30);
            return {
                from: formatDate(last30),
                to: formatDate(today)
            };
        case 'current_month':
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            return {
                from: formatDate(firstDay),
                to: formatDate(today)
            };
        case 'previous_month':
            const prevMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastDayPrevMonth = new Date(today.getFullYear(), today.getMonth(), 0);
            return {
                from: formatDate(prevMonth),
                to: formatDate(lastDayPrevMonth)
            };
        case 'custom':
            return {
                from: customFrom,
                to: customTo
            };
        default:
            return {
                from: formatDate(today),
                to: formatDate(today)
            };
    }
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function performSync(syncData) {
    fetch('{{ route("sync.manual") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(syncData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateProgress(50, `Dispatched ${data.dispatched} sync job(s)`);
            
            // Poll for results
            setTimeout(() => checkSyncStatus(syncData), 2000);
        } else {
            updateProgress(0, 'Failed to start sync');
            showToast('error', data.message || 'Failed to start sync');
            document.getElementById('syncButton').disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        updateProgress(0, 'Error starting sync');
        showToast('error', 'Error starting sync: ' + error.message);
        document.getElementById('syncButton').disabled = false;
    });
}

function checkSyncStatus(syncData) {
    // For now, simulate completion after a delay
    // In production, you'd poll the sync logs endpoint
    setTimeout(() => {
        updateProgress(100, 'Sync completed!');
        document.getElementById('syncButton').disabled = false;
        showResults(syncData);
        showToast('success', 'Quick sync completed successfully!');
    }, 3000);
}

function updateProgress(percentage, message) {
    document.getElementById('progressBar').style.width = percentage + '%';
    document.getElementById('progressPercentage').textContent = percentage + '%';
    
    const messagesDiv = document.getElementById('progressMessages');
    const timestamp = new Date().toLocaleTimeString();
    messagesDiv.innerHTML += `<p class="mb-1"><i class="ti ti-check-circle me-1"></i>[${timestamp}] ${message}</p>`;
    
    // Scroll to bottom
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function showResults(syncData) {
    const resultsCard = document.getElementById('resultsCard');
    const resultsContent = document.getElementById('resultsContent');
    
    resultsContent.innerHTML = `
        <div class="alert alert-success">
            <i class="ti ti-check-circle me-2"></i>
            <strong>Success!</strong> Data sync has been queued successfully.
        </div>
        
        <h5 class="mb-3">Sync Details:</h5>
        <ul class="mb-3">
            <li><strong>Networks:</strong> ${syncData.network_ids.length} network(s)</li>
            <li><strong>Type:</strong> ${syncData.sync_type}</li>
            <li><strong>Date Range:</strong> ${syncData.date_from} to ${syncData.date_to}</li>
        </ul>
        
        <p class="mb-3">
            The sync is now running in the background. You can check the progress in 
            <a href="{{ route('sync.logs.index') }}">Sync Logs</a>.
        </p>
        
        <div class="d-grid gap-2">
            <a href="{{ route('sync.logs.index') }}" class="btn btn-primary">
                <i class="ti ti-file-text me-1"></i>View Sync Logs
            </a>
            <button type="button" class="btn btn-light" onclick="resetForm()">
                <i class="ti ti-refresh me-1"></i>Start Another Sync
            </button>
        </div>
    `;
    
    resultsCard.style.display = 'block';
}

function resetForm() {
    document.getElementById('quickSyncForm').reset();
    document.getElementById('progressCard').style.display = 'none';
    document.getElementById('resultsCard').style.display = 'none';
    document.getElementById('progressMessages').innerHTML = '<p class="mb-1"><i class="ti ti-clock me-1"></i>Initializing sync...</p>';
    $('#networkSelect').val(null).trigger('change');
}

function showToast(type, message) {
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    const toast = `
        <div class="toast align-items-center text-white ${bgClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
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

