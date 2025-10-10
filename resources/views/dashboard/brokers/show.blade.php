@extends('dashboard.layouts.main')

@section('title', 'Broker Details')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [breadcrumb] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                        <i class="ti ti-home me-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('brokers.index') }}" class="text-decoration-none">Brokers</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($broker) }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-3 text-end">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">{{ ucfirst($broker) }} Integration</h4>
                        <p class="text-muted mb-0">Connect and manage your {{ $broker }} affiliate marketing account.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary" onclick="refreshConnection()">
                            <i class="ti ti-refresh me-1"></i> Refresh
                        </button>
                        <button class="btn btn-outline-success" onclick="testConnection()">
                            <i class="ti ti-plug me-1"></i> Test Connection
                        </button>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Connection Status -->
                <div class="col-12 mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary-subtle rounded me-3">
                                            <div class="avatar-title bg-primary-subtle text-primary fs-22">
                                                <i class="ti ti-{{ $broker === 'boostiny' ? 'bolt' : ($broker === 'digizag' ? 'device-desktop' : ($broker === 'platformance' ? 'building' : ($broker === 'optimize' ? 'optimization' : ($broker === 'marketeers' ? 'chart-pie' : 'world')))) }}"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">{{ ucfirst($broker) }}</h5>
                                            <p class="text-muted mb-0">Status: <span class="badge bg-success-subtle text-success">Connected</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <button class="btn btn-outline-danger" onclick="disconnectBroker()">
                                        <i class="ti ti-plug-off me-1"></i> Disconnect
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connection Form -->
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Connection Settings</h5>
                        </div>
                        <div class="card-body">
                            <form id="brokerForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">API Key</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-key text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0 border-0 bg-light" 
                                                   name="api_key" value="{{ $brokerData['api_key'] ?? '' }}" 
                                                   placeholder="Enter API Key">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">API Secret</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-lock text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control border-start-0 border-0 bg-light" 
                                                   name="api_secret" value="{{ $brokerData['api_secret'] ?? '' }}" 
                                                   placeholder="Enter API Secret">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Access Token</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-token text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 border-0 bg-light" 
                                                   name="access_token" value="{{ $brokerData['access_token'] ?? '' }}" 
                                                   placeholder="Enter Access Token">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Refresh Token</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-refresh text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control border-start-0 border-0 bg-light" 
                                                   name="refresh_token" value="{{ $brokerData['refresh_token'] ?? '' }}" 
                                                   placeholder="Enter Refresh Token">
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">API URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="ti ti-world text-muted"></i>
                                            </span>
                                            <input type="url" class="form-control border-start-0 border-0 bg-light" 
                                                   name="api_url" value="{{ $brokerData['api_url'] ?? '' }}" 
                                                   placeholder="Enter API URL">
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Save Settings
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                        <i class="ti ti-refresh me-1"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <!-- Connection Info -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Connection Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Last Sync</label>
                                <p class="mb-0">{{ $brokerData['last_sync'] ?? 'Never' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Campaigns</label>
                                <p class="mb-0">{{ $brokerData['campaigns_count'] ?? 0 }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Total Revenue</label>
                                <p class="mb-0">${{ number_format($brokerData['total_revenue'] ?? 0, 2) }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <p class="mb-0">
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ti ti-check me-1"></i>Active
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-outline-primary" onclick="syncData()">
                                    <i class="ti ti-refresh me-1"></i> Sync Data
                                </button>
                                <button class="btn btn-outline-info" onclick="viewCampaigns()">
                                    <i class="ti ti-target me-1"></i> View Campaigns
                                </button>
                                <button class="btn btn-outline-success" onclick="viewReports()">
                                    <i class="ti ti-chart-line me-1"></i> View Reports
                                </button>
                                <button class="btn btn-outline-warning" onclick="testApi()">
                                    <i class="ti ti-api me-1"></i> Test API
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#brokerForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('broker', '{{ $broker }}');
        
        dashboardUtils.showLoading('button[type="submit"]');
        
        fetch('{{ route("brokers.update", $broker) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                dashboardUtils.showSuccess(data.message);
            } else {
                dashboardUtils.showError(data.message);
            }
        })
        .catch(error => {
            dashboardUtils.showError('Connection failed. Please try again.');
        })
        .finally(() => {
            dashboardUtils.hideLoading('button[type="submit"]', '<i class="ti ti-device-floppy me-1"></i> Save Settings');
        });
    });
});

function refreshConnection() {
    dashboardUtils.showSuccess('Refreshing connection...');
}

function testConnection() {
    dashboardUtils.showSuccess('Testing connection...');
}

function disconnectBroker() {
    dashboardUtils.showConfirm(
        'Disconnect Broker',
        'Are you sure you want to disconnect from {{ ucfirst($broker) }}?',
        () => {
            dashboardUtils.showSuccess('Disconnected successfully!');
        }
    );
}

function resetForm() {
    document.getElementById('brokerForm').reset();
}

function syncData() {
    dashboardUtils.showSuccess('Syncing data...');
}

function viewCampaigns() {
    window.location.href = '{{ route("brokers.data", $broker) }}';
}

function viewReports() {
    window.location.href = '{{ route("reports.revenue") }}';
}

function testApi() {
    dashboardUtils.showSuccess('Testing API connection...');
}
</script>
@endpush