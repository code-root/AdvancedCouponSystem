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
                            <!-- Statistics -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-primary-subtle rounded">
                                        <h4 class="mb-1 text-primary">{{ $userConnections->total() }}</h4>
                                        <p class="text-muted mb-0 fs-13">Total Connections</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-success-subtle rounded">
                                        <h4 class="mb-1 text-success">{{ auth()->user()->getActiveBrokerConnectionsCount() }}</h4>
                                        <p class="text-muted mb-0 fs-13">Active</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-info-subtle rounded">
                                        <h4 class="mb-1 text-info">{{ $availableBrokers->count() }}</h4>
                                        <p class="text-muted mb-0 fs-13">Available</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center p-3 bg-warning-subtle rounded">
                                        <h4 class="mb-1 text-warning">{{ \App\Models\Broker::where('is_active', true)->count() }}</h4>
                                        <p class="text-muted mb-0 fs-13">Total Brokers</p>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle" id="brokersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Broker</th>
                                            <th>Connection Name</th>
                                            <th>Status</th>
                                            <th>Connected At</th>
                                            <th>Last Sync</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($userConnections as $connection)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-3">
                                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                                            <i class="ti ti-building-store"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fs-14">{{ $connection->broker->display_name }}</h6>
                                                        <small class="text-muted">{{ $connection->broker->name }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $connection->connection_name }}</td>
                                            <td>
                                                @if($connection->is_connected)
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="ti ti-plug me-1"></i>Connected
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger">
                                                        <i class="ti ti-plug-off me-1"></i>Disconnected
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $connection->connected_at ? $connection->connected_at->format('M d, Y') : '-' }}</td>
                                            <td>{{ $connection->last_sync ? $connection->last_sync->diffForHumans() : 'Never' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary" onclick="syncConnection({{ $connection->id }})" title="Sync">
                                                        <i class="ti ti-refresh"></i>
                                                    </button>
                                                    <a href="{{ route('brokers.show', $connection->broker_id) }}" class="btn btn-outline-info" title="View">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <button class="btn btn-outline-warning" onclick="editConnection({{ $connection->id }})" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="disconnectConnection({{ $connection->id }})" title="Disconnect">
                                                        <i class="ti ti-plug-off"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="ti ti-plug-off fs-48 text-muted mb-3"></i>
                                                <p class="text-muted mb-3">You haven't connected any brokers yet.</p>
                                                <a href="{{ route('brokers.create') }}" class="btn btn-primary">
                                                    <i class="ti ti-plus me-1"></i> Connect Your First Broker
                                                </a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if($userConnections->hasPages())
                                <div class="mt-3">
                                    {{ $userConnections->links() }}
                                </div>
                            @endif
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

function syncConnection(connectionId) {
    Swal.fire({
        title: 'Syncing...',
        text: 'Fetching latest data from broker',
        icon: 'info',
        showConfirmButton: false,
        timer: 2000
    });
}

function editConnection(connectionId) {
    // Implement edit connection functionality
    Swal.fire({
        title: 'Edit Connection',
        text: 'This feature will be available soon',
        icon: 'info'
    });
}

function disconnectConnection(connectionId) {
    Swal.fire({
        title: 'Disconnect Broker?',
        text: 'Are you sure you want to disconnect this broker?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, disconnect',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implement disconnect logic
            fetch(`/brokers/connections/${connectionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Disconnected!', data.message, 'success');
                    location.reload();
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to disconnect broker', 'error');
            });
        }
    });
}
</script>
@endpush
