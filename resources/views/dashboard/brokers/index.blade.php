@extends('dashboard.layouts.main')

@section('title', 'Broker Integration')

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
                                <li class="breadcrumb-item active" aria-current="page">Broker Integration</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Broker Button -->
            <div class="mb-3 text-end">
                <a href="{{ route('brokers.create') }}" class="btn btn-primary fw-semibold">
                    <i class="ti ti-plus me-1"></i> Connect New Broker
                </a>
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
                <!-- Brokers Table -->
                <div class="col-sm-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Available Brokers</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted font-13 mb-3">
                                Connect with the following affiliate marketing brokers to manage your campaigns and track performance.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="brokersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Broker</th>
                                            <th>Status</th>
                                            <th>Last Sync</th>
                                            <th>Campaigns</th>
                                            <th>Revenue</th>
                                            <th>Operations</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($brokers as $broker)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                            <i class="ti ti-{{ $broker->name === 'boostiny' ? 'bolt' : ($broker->name === 'digizag' ? 'device-desktop' : ($broker->name === 'platformance' ? 'building' : ($broker->name === 'optimize' ? 'optimization' : ($broker->name === 'marketeers' ? 'chart-pie' : 'world')))) }}"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fs-14">{{ ucfirst($broker->name) }}</h6>
                                                        <small class="text-muted">{{ $broker->status === 'active' ? 'Active' : 'Inactive' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($broker->is_connected)
                                                    <span class="badge bg-success-subtle text-success">Connected</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">Not Connected</span>
                                                @endif
                                            </td>
                                            <td>{{ $broker->last_sync ?? '-' }}</td>
                                            <td>{{ $broker->campaigns_count ?? 0 }}</td>
                                            <td>${{ number_format($broker->total_revenue ?? 0, 2) }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if($broker->is_connected)
                                                        <button class="btn btn-outline-primary" onclick="syncBroker('{{ $broker->name }}')" title="Sync">
                                                            <i class="ti ti-refresh"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="viewBrokerData('{{ $broker->name }}')" title="View Data">
                                                            <i class="ti ti-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-warning" onclick="editBroker('{{ $broker->name }}')" title="Edit">
                                                            <i class="ti ti-edit"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="disconnectBroker('{{ $broker->name }}')" title="Disconnect">
                                                            <i class="ti ti-plug-off"></i>
                                                        </button>
                                                    @else
                                                        <button class="btn btn-primary btn-sm" onclick="connectBroker('{{ $broker->name }}')">
                                                            <i class="ti ti-plug me-1"></i>Connect
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <p class="text-muted">No brokers available at the moment.</p>
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
        </div>
    </div>

    <!-- Connect Broker Modal -->
    <div class="modal fade" id="connectionModal" tabindex="-1" aria-labelledby="connectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="connectionModalTitle">
                        <i class="ti ti-plug me-2"></i>Connect Broker
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="connectionForm">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="avatar-lg bg-primary-subtle rounded mx-auto mb-3">
                                <div class="avatar-title bg-primary-subtle text-primary fs-40" id="modalBrokerIcon">
                                    <i class="ti ti-plug"></i>
                                </div>
                            </div>
                            <h5 id="modalBrokerName">Broker</h5>
                            <p class="text-muted" id="modalBrokerDescription">Connect your broker account</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">API Key <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="ti ti-key text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 border-0 bg-light" 
                                           name="api_key" required placeholder="Enter your API Key">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">API Secret <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="ti ti-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 border-0 bg-light" 
                                           name="api_secret" required placeholder="Enter your API Secret">
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">API URL</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="ti ti-world text-muted"></i>
                                    </span>
                                    <input type="url" class="form-control border-start-0 border-0 bg-light" 
                                           name="api_url" placeholder="Enter API URL (optional)">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="auto_sync" id="autoSync" checked>
                                    <label class="form-check-label" for="autoSync">
                                        Enable automatic data synchronization
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="ti ti-plug me-1"></i> Connect Broker
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let selectedBroker = '';

const brokerInfo = {
    boostiny: {
        name: 'Boostiny',
        icon: 'ti-bolt',
        description: 'Affiliate Marketing Platform'
    },
    digizag: {
        name: 'Digizag',
        icon: 'ti-device-desktop',
        description: 'Digital Marketing Network'
    },
    platformance: {
        name: 'Platformance',
        icon: 'ti-building',
        description: 'Performance Marketing'
    },
    optimize: {
        name: 'Optimize',
        icon: 'ti-optimization',
        description: 'Media Optimization'
    },
    marketeers: {
        name: 'Marketeers',
        icon: 'ti-chart-pie',
        description: 'Marketing Analytics'
    },
    admitad: {
        name: 'Admitad',
        icon: 'ti-world',
        description: 'Affiliate Network'
    }
};

$(document).ready(function() {
    // Initialize DataTable
    $('#brokersTable').DataTable({
        responsive: true,
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: [5] }
        ]
    });

    // Handle connection form
    $('#connectionForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('broker', selectedBroker);
        
        dashboardUtils.showLoading('button[type="submit"]');
        
        fetch('{{ route("brokers.store") }}', {
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
                bootstrap.Modal.getInstance(document.getElementById('connectionModal')).hide();
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                dashboardUtils.showError(data.message);
            }
        })
        .catch(error => {
            dashboardUtils.showError('Connection failed. Please try again.');
        })
        .finally(() => {
            dashboardUtils.hideLoading('button[type="submit"]', '<i class="ti ti-plug me-1"></i> Connect Broker');
        });
    });
});

function connectBroker(broker) {
    selectedBroker = broker;
    const info = brokerInfo[broker];
    
    document.getElementById('modalBrokerName').textContent = info.name;
    document.getElementById('modalBrokerDescription').textContent = info.description;
    document.getElementById('modalBrokerIcon').innerHTML = `<i class="${info.icon}"></i>`;
    
    // Reset form
    document.getElementById('connectionForm').reset();
    
    new bootstrap.Modal(document.getElementById('connectionModal')).show();
}

function syncBroker(broker) {
    dashboardUtils.showSuccess(`Syncing ${broker} data...`);
}

function viewBrokerData(broker) {
    window.location.href = `/brokers/${broker}/data`;
}

function editBroker(broker) {
    window.location.href = `/brokers/${broker}`;
}

function disconnectBroker(broker) {
    dashboardUtils.showConfirm(
        'Disconnect Broker',
        `Are you sure you want to disconnect ${broker}?`,
        () => {
            dashboardUtils.showSuccess(`${broker} disconnected successfully!`);
        }
    );
}
</script>
@endpush
