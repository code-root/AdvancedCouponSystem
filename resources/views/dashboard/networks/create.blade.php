@extends('layouts.vertical', ['title' => 'Connect Network'])

@section('css')
    @vite(['node_modules/sweetalert2/dist/sweetalert2.min.css'])
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
                                <select class="select2 form-control" name="network_id" id="networkSelect" data-toggle="select2" required>
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

                            <!-- API Endpoint (Auto-filled) -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">API Endpoint <span class="text-danger">*</span></label>
                                <input type="url" class="form-control bg-light" name="api_endpoint" id="apiEndpoint" 
                                       value="{{ old('api_endpoint') }}" placeholder="Will be auto-filled" readonly>
                                <small class="text-muted">Network's API URL (auto-configured)</small>
                            </div>

                            <!-- Dynamic Fields Container -->
                            <div id="dynamicFieldsContainer" class="col-12">
                                <!-- Fields will be loaded dynamically based on network selection -->
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>
                                    Please select a network above to see required fields
                                </div>
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
                                    <input class="form-check-input" type="checkbox" name="is_connected" id="isConnected" value="1" checked>
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
                                    <span class="text-muted">revenue Rate:</span>
                                    <span class="fw-medium" id="sidebarrevenue">-</span>
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
let currentNetworkConfig = null;

// Wait for DOM and jQuery to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Make sure jQuery and other libraries are loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }
    
    // Initialize Select2 explicitly
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('[data-toggle="select2"]').select2();
    }

    // Handle network selection
    $('#networkSelect').on('change', function() {
        const networkId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        
        if (networkId) {
            loadNetworkConfig(networkId, selectedOption);
        } else {
            resetForm();
        }
    });

    // Form submission with connection test
    $('#networkForm').on('submit', function(e) {
        e.preventDefault();
        testAndSubmit();
    });
});

// Load network configuration
function loadNetworkConfig(networkId, selectedOption) {
    // Show loading
    $('#dynamicFieldsContainer').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading network configuration...</p>
        </div>
    `);

    // Fetch network config
    $.ajax({
        url: `/networks/${networkId}/config`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                currentNetworkConfig = response.data;
                renderDynamicFields(response.data);
                updateSidebar(selectedOption, response.data);
                
                // Set API endpoint
                $('#apiEndpoint').val(response.data.network_info.api_url);
                
                // Auto-generate connection name if empty
                if (!$('#connectionName').val()) {
                    $('#connectionName').val(response.data.network_info.display_name + ' - ' + new Date().toLocaleDateString());
                }
                
                $('#networkInfoCard').fadeIn();
            } else {
                showError('Failed to load network configuration');
            }
        },
        error: function() {
            showError('Error loading network configuration');
        }
    });
}

// Render dynamic fields based on network requirements
function renderDynamicFields(config) {
    let fieldsHTML = '<div class="row">';
    
    // Handle both array and object formats
    const requiredFields = config.required_fields;
    
    if (typeof requiredFields === 'object' && !Array.isArray(requiredFields)) {
        // Object format (new style with field details)
        Object.keys(requiredFields).forEach(fieldKey => {
            const field = requiredFields[fieldKey];
            const fieldLabel = field.label || fieldKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const fieldType = field.type || 'text';
            const isPassword = fieldType === 'password' || fieldKey.includes('secret') || fieldKey.includes('password') || fieldKey.includes('token');
            const placeholder = field.placeholder || `Enter ${fieldLabel}`;
            
            fieldsHTML += `
                <div class="col-md-6 mb-3">
                    <label class="form-label">${fieldLabel} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>
                    <div class="input-group">
                        <input type="${isPassword ? 'password' : fieldType}" 
                               class="form-control network-credential" 
                               name="credentials[${fieldKey}]" 
                               id="field_${fieldKey}"
                               placeholder="${placeholder}" 
                               ${field.required ? 'required' : ''}>
                        ${isPassword ? `
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('field_${fieldKey}')">
                                <i class="ti ti-eye"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });
    } else if (Array.isArray(requiredFields)) {
        // Array format (old style)
        requiredFields.forEach(field => {
            const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const isPassword = field.includes('secret') || field.includes('password') || field.includes('token');
            
            fieldsHTML += `
                <div class="col-md-6 mb-3">
                    <label class="form-label">${fieldName} <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="${isPassword ? 'password' : 'text'}" 
                               class="form-control network-credential" 
                               name="credentials[${field}]" 
                               id="field_${field}"
                               placeholder="Enter ${fieldName}" 
                               required>
                        ${isPassword ? `
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('field_${field}')">
                                <i class="ti ti-eye"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        });
    }
    
    fieldsHTML += '</div>';
    $('#dynamicFieldsContainer').html(fieldsHTML);
}

// Update sidebar with network info
function updateSidebar(selectedOption, config) {
    $('#sidebarNetworkName').text(config.network_info.display_name);
    $('#sidebarApiUrl').text(config.network_info.api_url);
    
    // Display features
    const features = selectedOption.data('features');
    if (features) {
        const featuresArray = features.split(', ');
        let featuresHTML = '';
        featuresArray.forEach(feature => {
            featuresHTML += `<span class="badge bg-primary-subtle text-primary fs-11 p-1 me-1 mb-1">${feature}</span>`;
        });
        $('#sidebarFeatures').html(featuresHTML);
    }
    
    $('#sidebarDetails').fadeIn();
}

// Test connection and submit
function testAndSubmit() {
    const networkId = $('#networkSelect').val();
    const connectionName = $('#connectionName').val();
    
    if (!networkId || !connectionName) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please select a network and enter a connection name'
        });
        return;
    }
    
    // Collect credentials
    const credentials = {};
    $('.network-credential').each(function() {
        const name = $(this).attr('name').replace('credentials[', '').replace(']', '');
        credentials[name] = $(this).val();
    });
    
    // Add API endpoint
    credentials.api_endpoint = $('#apiEndpoint').val();
    
    // Show testing modal
    Swal.fire({
        title: 'Testing Connection',
        html: 'Please wait while we verify your credentials...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Test connection
    $.ajax({
        url: '{{ route("networks.test-connection") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            network_id: networkId,
            credentials: credentials
        },
        success: function(response) {
            if (response.success) {
                // Store connection data if available (for networks like Platformance)
                if (response.data) {
                    // Add token, phpsessid, and cookies to hidden fields
                    if (response.data.token) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'credentials[token]',
                            value: response.data.token
                        }).appendTo('#networkForm');
                    }
                    if (response.data.phpsessid) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'credentials[phpsessid]',
                            value: response.data.phpsessid
                        }).appendTo('#networkForm');
                    }
                    if (response.data.cookies) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'credentials[cookies]',
                            value: response.data.cookies
                        }).appendTo('#networkForm');
                    }
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Connection Successful!',
                    text: response.message,
                    showCancelButton: true,
                    confirmButtonText: 'Save Connection',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitForm();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Connection Failed',
                    html: `<p>${response.message}</p>
                           <small class="text-muted">Please check your credentials and try again.</small>`,
                    showCancelButton: true,
                    confirmButtonText: 'Try Again',
                    cancelButtonText: 'Save Anyway'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        submitForm();
                    }
                });
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'An error occurred while testing the connection';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg
            });
        }
    });
}

// Submit form
function submitForm() {
    // Collect all form data
    const formData = $('#networkForm').serialize();
    
    Swal.fire({
        title: 'Saving Connection',
        html: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '{{ route("networks.store") }}',
        method: 'POST',
        data: formData,
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Network connection created successfully',
                timer: 2000
            }).then(() => {
                window.location.href = '{{ route("networks.index") }}';
            });
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Failed to create connection';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg
            });
        }
    });
}

// Reset form
function resetForm() {
    $('#dynamicFieldsContainer').html(`
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Please select a network above to see required fields
        </div>
    `);
    $('#apiEndpoint').val('');
    $('#networkInfoCard').fadeOut();
    currentNetworkConfig = null;
}

// Toggle password visibility
function togglePasswordField(fieldId) {
    const field = document.getElementById(fieldId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    
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

// Show error message
function showError(message) {
    $('#dynamicFieldsContainer').html(`
        <div class="alert alert-danger">
            <i class="ti ti-alert-circle me-2"></i>
            ${message}
        </div>
    `);
}
</script>
@endsection
