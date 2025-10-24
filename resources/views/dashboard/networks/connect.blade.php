@extends('dashboard.layouts.main')

@section('title', 'Connect Network')

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
                                    <a href="{{ route('networks.index') }}" class="text-decoration-none">Networks</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Connect Network</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-3 text-end">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">Connect New Network</h4>
                        <p class="text-muted mb-0">Choose a network to connect and start managing your affiliate campaigns.</p>
                    </div>
                    <a href="{{ route('networks.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Back to Networks
                    </a>
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
                <!-- Network Selection -->
                @foreach(['boostiny', 'digizag', 'platformance', 'optimize', 'marketeers', 'admitad', 'icw'] as $network)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100 network-card" data-network="{{ $network }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm bg-primary-subtle rounded">
                                    <div class="avatar-title bg-primary-subtle text-primary fs-22">
                                        <i class="ti ti-{{ $network === 'boostiny' ? 'bolt' : ($network === 'digizag' ? 'device-desktop' : ($network === 'platformance' ? 'building' : ($network === 'optimize' ? 'optimization' : ($network === 'marketeers' ? 'chart-pie' : 'world')))) }}"></i>
                                    </div>
                                </div>
                                <div class="ms-3">
                                    <h5 class="mb-1">{{ ucfirst($network) }}</h5>
                                    <p class="text-muted mb-0 fs-13">{{ $network === 'boostiny' ? 'Affiliate Marketing Platform' : ($network === 'digizag' ? 'Digital Marketing Network' : ($network === 'platformance' ? 'Performance Marketing' : ($network === 'optimize' ? 'Media Optimization' : ($network === 'marketeers' ? 'Marketing Analytics' : 'Affiliate Network')))) }}</p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="fw-semibold">Features:</h6>
                                <ul class="list-unstyled mb-0">
                                    @if($network === 'boostiny')
                                        <li><i class="ti ti-check text-success me-1"></i> Campaign Management</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Real-time Analytics</li>
                                        <li><i class="ti ti-check text-success me-1"></i> API Integration</li>
                                    @elseif($network === 'digizag')
                                        <li><i class="ti ti-check text-success me-1"></i> Digital Network</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Publisher Tools</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Performance Tracking</li>
                                    @elseif($network === 'platformance')
                                        <li><i class="ti ti-check text-success me-1"></i> Advanced Analytics</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Conversion Tracking</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Multi-channel Support</li>
                                    @elseif($network === 'optimize')
                                        <li><i class="ti ti-check text-success me-1"></i> Media Optimization</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Targeting Tools</li>
                                        <li><i class="ti ti-check text-success me-1"></i> ROI Analytics</li>
                                    @elseif($network === 'marketeers')
                                        <li><i class="ti ti-check text-success me-1"></i> Marketing Analytics</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Campaign Optimization</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Data Insights</li>
                                    @elseif($network === 'icw')
                                        <li><i class="ti ti-check text-success me-1"></i> ICubesWire Integration</li>
                                        <li><i class="ti ti-check text-success me-1"></i> API Data Sync</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Real-time Analytics</li>
                                    @else
                                        <li><i class="ti ti-check text-success me-1"></i> Global Network</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Multiple Verticals</li>
                                        <li><i class="ti ti-check text-success me-1"></i> Advanced Reporting</li>
                                    @endif
                                </ul>
                            </div>
                            
                            <div class="d-grid">
                                <button class="btn btn-primary" onclick="connectNetwork('{{ $network }}')">
                                    <i class="ti ti-plug me-1"></i> Connect {{ ucfirst($network) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Connection Modal -->
    <div class="modal fade" id="connectionModal" tabindex="-1" aria-labelledby="connectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="connectionModalTitle">
                        <i class="ti ti-plug me-2"></i>Connect Network
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="connectionForm">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="avatar-lg bg-primary-subtle rounded mx-auto mb-3">
                                <div class="avatar-title bg-primary-subtle text-primary fs-40" id="modalNetworkIcon">
                                    <i class="ti ti-plug"></i>
                                </div>
                            </div>
                            <h5 id="modalNetworkName">Network</h5>
                            <p class="text-muted" id="modalNetworkDescription">Connect your network account</p>
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
                            <i class="ti ti-plug me-1"></i> Connect Network
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let selectedNetwork = '';

const networkInfo = {
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
    },
    icw: {
        name: 'ICW',
        icon: 'ti-device-desktop',
        description: 'ICubesWire Network'
    }
};

function connectNetwork(network) {
    selectedNetwork = network;
    const info = networkInfo[network];
    
    document.getElementById('modalNetworkName').textContent = info.name;
    document.getElementById('modalNetworkDescription').textContent = info.description;
    document.getElementById('modalNetworkIcon').innerHTML = `<i class="${info.icon}"></i>`;
    
    // Reset form
    document.getElementById('connectionForm').reset();
    
    new bootstrap.Modal(document.getElementById('connectionModal')).show();
}

$(document).ready(function() {
    $('#connectionForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('network', selectedNetwork);
        
        // Show network connection progress
        dashboardUtils.showNetworkProgress(selectedNetwork, 'Connecting to network...');
        
        fetch('{{ route("networks.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success and redirect
                dashboardUtils.hideProgress();
                dashboardUtils.showSuccess(data.message);
                
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('connectionModal')).hide();
                
                // Redirect after delay
                setTimeout(() => {
                    window.location.href = `{{ route('networks.index') }}/${selectedNetwork}`;
                }, 1500);
            } else {
                // Handle upgrade required
                if (data.upgrade_required) {
                    dashboardUtils.hideProgress();
                    dashboardUtils.showUpgradePrompt(data);
                } else {
                    dashboardUtils.hideProgress();
                    dashboardUtils.showError(data.message);
                }
            }
        })
        .catch(error => {
            dashboardUtils.hideProgress();
            dashboardUtils.showError('Connection failed. Please try again.');
        });
    });
    
    // Add hover effects to network cards
    $('.network-card').hover(
        function() {
            $(this).addClass('shadow-lg');
        },
        function() {
            $(this).removeClass('shadow-lg');
        }
    );
});
</script>
@endpush