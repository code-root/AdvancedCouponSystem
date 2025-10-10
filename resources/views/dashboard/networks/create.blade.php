@extends('layouts.vertical', ['title' => 'Connect Network'])

@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css'])
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Networks', 'title' => 'Connect New Network'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end">
                <a href="{{ route('networks.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Networks
                </a>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('networks.store') }}" method="POST" id="networkForm">
        @csrf
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="card-title mb-0">Network Connection Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Select Network -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Select Network <span class="text-danger">*</span></label>
                                <select class="form-select" name="network_id" id="networkSelect" required>
                                    <option value="">Choose a network...</option>
                                    @foreach($networks as $network)
                                        <option value="{{ $network->id }}" 
                                                data-name="{{ $network->name }}"
                                                data-display="{{ $network->display_name }}"
                                                data-description="{{ $network->description }}"
                                                data-api-url="{{ $network->api_url }}"
                                                data-features="{{ implode(', ', $network->supported_features ?? []) }}"
                                                data-logo="{{ $network->logo_url }}"
                                                {{ old('network_id') == $network->id ? 'selected' : '' }}>
                                            {{ $network->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Connection Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Connection Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="connection_name" id="connectionName" 
                                       value="{{ old('connection_name') }}" placeholder="e.g., My Main Account" required>
                                <small class="text-muted">Give this connection a memorable name</small>
                            </div>

                            <!-- API Endpoint -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">API Endpoint <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="api_endpoint" id="apiEndpoint" 
                                       value="{{ old('api_endpoint') }}" placeholder="https://api.network.com" required>
                                <small class="text-muted">Network's API URL</small>
                            </div>

                            <!-- Client ID -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="client_id" 
                                       value="{{ old('client_id') }}" placeholder="Enter Client ID" required>
                            </div>

                            <!-- Client Secret -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client Secret <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="client_secret" id="clientSecret"
                                           value="{{ old('client_secret') }}" placeholder="Enter Client Secret" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('clientSecret')">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Token (Optional) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Access Token (Optional)</label>
                                <input type="text" class="form-control" name="token" 
                                       value="{{ old('token') }}" placeholder="Bearer token if available">
                            </div>

                            <!-- Contact ID (Optional) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contact ID (Optional)</label>
                                <input type="text" class="form-control" name="contact_id" 
                                       value="{{ old('contact_id') }}" placeholder="Your contact ID at network">
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Auto Connect -->
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_connected" id="isConnected" value="1" {{ old('is_connected') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isConnected">
                                        Mark as connected immediately
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Info -->
            <div class="col-lg-4">
                <div class="card bg-primary-subtle border-primary border-dashed" id="networkInfoCard" style="display: none;">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg bg-primary-subtle mx-auto mb-3">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-32">
                                    <i class="ti ti-building-store" id="sidebarIcon"></i>
                                </span>
                            </div>
                            <h5 class="fw-semibold mb-1" id="sidebarNetworkName">Select a Network</h5>
                            <p class="text-muted mb-0" id="sidebarNetworkCountry"></p>
                        </div>

                        <div class="border-top border-dashed border-primary pt-3" id="sidebarDetails" style="display: none;">
                            <h6 class="text-uppercase text-muted mb-3">Network Details</h6>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Commission Rate:</span>
                                    <span class="fw-medium" id="sidebarCommission">-</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Country:</span>
                                    <span class="fw-medium" id="sidebarCountryName">-</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">API URL:</span>
                                    <span class="fw-medium text-truncate" id="sidebarApiUrl" style="max-width: 150px;">-</span>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="text-muted mb-2">Features:</label>
                                <div id="sidebarFeatures" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h6 class="card-title mb-0">Connection Guide</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2 mb-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                    1
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">Select Network</h6>
                                <p class="text-muted fs-13 mb-0">Choose the network you want to connect</p>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle">
                                    2
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">Enter Credentials</h6>
                                <p class="text-muted fs-13 mb-0">Provide your API credentials</p>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                    3
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">Connect & Start</h6>
                                <p class="text-muted fs-13 mb-0">Complete setup and start earning</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('networks.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-plug-connected me-1"></i> Connect Network
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#networkSelect').select2({
        placeholder: 'Choose a network...',
        allowClear: true
    });

    // Handle network selection
    $('#networkSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        
        if (selectedOption.val()) {
            // Update sidebar
            $('#sidebarNetworkName').text(selectedOption.data('display'));
            $('#sidebarNetworkCountry').text(selectedOption.data('country'));
            $('#sidebarCommission').text(selectedOption.data('commission') + '%');
            $('#sidebarCountryName').text(selectedOption.data('country'));
            $('#sidebarApiUrl').text(selectedOption.data('api-url'));
            
            // Update API endpoint field
            $('#apiEndpoint').val(selectedOption.data('api-url'));
            
            // Auto-generate connection name if empty
            if (!$('#connectionName').val()) {
                $('#connectionName').val(selectedOption.data('display') + ' - ' + new Date().toLocaleDateString());
            }

            // Show network info card
            $('#networkInfoCard').fadeIn();
            $('#sidebarDetails').fadeIn();

            // Display features
            const features = selectedOption.data('features');
            if (features) {
                const featuresArray = features.split(', ');
                let featuresHTML = '';
                featuresArray.forEach(feature => {
                    featuresHTML += `<span class="badge bg-primary-subtle text-primary fs-11 p-1">${feature}</span> `;
                });
                $('#sidebarFeatures').html(featuresHTML);
            }
        } else {
            $('#networkInfoCard').fadeOut();
            $('#sidebarDetails').fadeOut();
        }
    });

    // Form validation
    $('#networkForm').on('submit', function(e) {
        const network = $('#networkSelect').val();
        const connectionName = $('#connectionName').val();
        const apiEndpoint = $('#apiEndpoint').val();
        const clientId = $('input[name="client_id"]').val();
        const clientSecret = $('input[name="client_secret"]').val();

        if (!network || !connectionName || !apiEndpoint || !clientId || !clientSecret) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please fill in all required fields'
            });
            return false;
        }
    });
});

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = event.currentTarget.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
    } else {
        field.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
    }
}
</script>
@endsection
