@extends('dashboard.layouts.vertical', ['title' => 'Edit Network Connection'])

@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css', 'node_modules/sweetalert2/dist/sweetalert2.min.css'])
@endsection

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Networks', 'title' => 'Edit Connection'])

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

    <form action="{{ route('networks.update', $userConnection->id) }}" method="POST" id="editNetworkForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Connection Info Card -->
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="card-title mb-0">Connection Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Network (Read-only) -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Network</label>
                                <input type="text" class="form-control bg-light" 
                                       value="{{ $network->display_name }}" readonly>
                                <small class="text-muted">Cannot be changed</small>
                            </div>

                            <!-- Connection Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Connection Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="connection_name" 
                                       value="{{ old('connection_name', $userConnection->connection_name) }}" 
                                       placeholder="e.g., My Main Account" required>
                                <small class="text-muted">Give this connection a memorable name</small>
                            </div>

                            <!-- API Endpoint -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">API Endpoint <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="api_endpoint" 
                                       value="{{ old('api_endpoint', $userConnection->api_endpoint) }}" 
                                       placeholder="https://api.network.com" required>
                                <small class="text-muted">Network's API URL</small>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" required>
                                    <option value="active" {{ old('status', $userConnection->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ old('status', $userConnection->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="inactive" {{ old('status', $userConnection->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <!-- Connected Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">Connection Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_connected" id="isConnected" 
                                           value="1" {{ old('is_connected', $userConnection->is_connected) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isConnected">
                                        Mark as connected
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Credentials Card -->
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">API Credentials</h4>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-soft-info" id="showCredentialsBtn">
                                    <i class="ti ti-eye me-1"></i> Show Credentials
                                </button>
                                <button type="button" class="btn btn-sm btn-soft-primary" id="loadFieldsBtn">
                                    <i class="ti ti-reload me-1"></i> Edit Credentials
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Note:</strong> Click "Show Credentials" to view current credentials (requires password). Click "Edit Credentials" to update them.
                        </div>
                        
                        <!-- Password Verification Modal -->
                        <div id="passwordVerificationSection" class="mb-3" style="display: none;">
                            <div class="alert alert-warning">
                                <i class="ti ti-shield-lock me-2"></i>
                                <strong>Security Check:</strong> Enter your account password to view credentials
                            </div>
                            <div class="input-group">
                                <input type="password" class="form-control" id="verifyPassword" 
                                       placeholder="Enter your password">
                                <button type="button" class="btn btn-primary" id="verifyPasswordBtn">
                                    <i class="ti ti-check me-1"></i> Verify
                                </button>
                                <button type="button" class="btn btn-secondary" id="cancelVerifyBtn">
                                    <i class="ti ti-x"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div id="credentialsContainer" class="row">
                            <div class="col-12 text-center text-muted py-4">
                                <i class="ti ti-lock fs-48"></i>
                                <p class="mt-2">Click "Show Credentials" to view or "Edit Credentials" to update</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card bg-primary-subtle border-primary border-dashed">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <div class="avatar-lg bg-primary-subtle mx-auto mb-3">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-32">
                                    <i class="ti ti-building-store"></i>
                                </span>
                            </div>
                            <h5 class="fw-semibold mb-1">{{ $network->display_name }}</h5>
                            <p class="text-muted mb-0">{{ $network->name }}</p>
                        </div>

                        <div class="border-top border-dashed border-primary pt-3">
                            <h6 class="text-uppercase text-muted mb-3">Connection Details</h6>
                            
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Status:</span>
                                    <span class="fw-medium">
                                        @if($userConnection->is_connected)
                                            <span class="badge bg-success">Connected</span>
                                        @else
                                            <span class="badge bg-warning">Not Connected</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Connected At:</span>
                                    <span class="fw-medium">{{ $userConnection->connected_at ? $userConnection->connected_at->format('M d, Y') : 'Never' }}</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Last Sync:</span>
                                    <span class="fw-medium">{{ $userConnection->last_sync ? $userConnection->last_sync->diffForHumans() : 'Never' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top border-dashed border-primary">
                            <button type="button" class="btn btn-soft-primary w-100 mb-2" onclick="testConnectionNow()">
                                <i class="ti ti-plug-connected me-1"></i> Test Connection
                            </button>
                            <button type="button" class="btn btn-soft-success w-100" onclick="reconnectNetwork()">
                                <i class="ti ti-refresh me-1"></i> Reconnect & Update
                            </button>
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
                        <i class="ti ti-device-floppy me-1"></i> Update Connection
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
const networkId = {{ $network->id }};
const connectionId = {{ $userConnection->id }};
let currentCredentials = @json($userConnection->credentials ?? []);
let isEditMode = false;

document.addEventListener('DOMContentLoaded', function() {
    // Load fields button
    document.getElementById('loadFieldsBtn')?.addEventListener('click', loadCredentialFields);
    
    // Show credentials button
    document.getElementById('showCredentialsBtn')?.addEventListener('click', showPasswordVerification);
    
    // Verify password button
    document.getElementById('verifyPasswordBtn')?.addEventListener('click', verifyPasswordAndShowCredentials);
    
    // Cancel verify button
    document.getElementById('cancelVerifyBtn')?.addEventListener('click', hidePasswordVerification);
    
    // Enter key in password field
    document.getElementById('verifyPassword')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            verifyPasswordAndShowCredentials();
        }
    });
});

// Show password verification section
function showPasswordVerification() {
    document.getElementById('passwordVerificationSection').style.display = 'block';
    document.getElementById('verifyPassword').focus();
    hidePasswordVerification.cancelMode = false;
}

// Hide password verification section
function hidePasswordVerification() {
    document.getElementById('passwordVerificationSection').style.display = 'none';
    document.getElementById('verifyPassword').value = '';
}

// Verify password and show credentials
function verifyPasswordAndShowCredentials() {
    const password = document.getElementById('verifyPassword').value;
    
    if (!password) {
        Swal.fire({
            icon: 'warning',
            title: 'Password Required',
            text: 'Please enter your password to continue'
        });
        return;
    }
    
    const btn = document.getElementById('verifyPasswordBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';
    
    fetch('{{ route("networks.verify-password") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ password: password })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hidePasswordVerification();
            showCurrentCredentials();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Verification Failed',
                text: data.message || 'Invalid password'
            });
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Verify';
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to verify password'
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Verify';
    });
}

// Show current credentials (read-only)
function showCurrentCredentials() {
    fetch(`/networks/${networkId}/config`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCredentialFieldsReadOnly(data.data.required_fields);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load credentials'
                });
            }
        });
}

// Render credentials as read-only
function renderCredentialFieldsReadOnly(requiredFields) {
    // Convert to array if it's an object
    if (!Array.isArray(requiredFields)) {
        if (typeof requiredFields === 'object' && requiredFields !== null) {
            requiredFields = Object.keys(requiredFields);
        } else {
            console.error('Invalid requiredFields:', requiredFields);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid network configuration'
            });
            return;
        }
    }
    
    let html = '<div class="alert alert-success mb-3">';
    html += '<i class="ti ti-shield-check me-2"></i>';
    html += '<strong>Current Credentials (Read-Only)</strong>';
    html += '</div>';
    html += '<div class="row">';
    
    requiredFields.forEach(field => {
        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const isPassword = field.includes('secret') || field.includes('password') || field.includes('token') || field.includes('key');
        const currentValue = currentCredentials[field] || 'Not set';
        
        html += `
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">${fieldName}</label>
                <div class="input-group">
                    <input type="${isPassword ? 'password' : 'text'}" 
                           class="form-control bg-light" 
                           value="${currentValue}" 
                           readonly>
                    ${isPassword ? `
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('readonly_${field}', event)">
                            <i class="ti ti-eye"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    html += '<div class="alert alert-info mt-3">';
    html += '<i class="ti ti-info-circle me-2"></i>';
    html += 'To edit these credentials, click the "Edit Credentials" button above.';
    html += '</div>';
    
    document.getElementById('credentialsContainer').innerHTML = html;
    isEditMode = false;
}

// Load credential fields for editing
function loadCredentialFields() {
    isEditMode = true;
    const btn = document.getElementById('loadFieldsBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';
    
    fetch(`/networks/${networkId}/config`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCredentialFieldsEditable(data.data.required_fields);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load network configuration'
                });
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-reload me-1"></i> Edit Credentials';
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load configuration'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-reload me-1"></i> Edit Credentials';
        });
}

// Render credential input fields for editing
function renderCredentialFieldsEditable(requiredFields) {
    // Convert to array if it's an object
    if (!Array.isArray(requiredFields)) {
        if (typeof requiredFields === 'object' && requiredFields !== null) {
            requiredFields = Object.keys(requiredFields);
        } else {
            console.error('Invalid requiredFields:', requiredFields);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid network configuration'
            });
            return;
        }
    }
    
    let html = '<div class="alert alert-warning mb-3">';
    html += '<i class="ti ti-edit me-2"></i>';
    html += '<strong>Edit Mode:</strong> Leave fields empty to keep current values. Only fill in fields you want to update.';
    html += '</div>';
    html += '<div class="row">';
    
    requiredFields.forEach(field => {
        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const isPassword = field.includes('secret') || field.includes('password') || field.includes('token') || field.includes('key');
        
        html += `
            <div class="col-md-6 mb-3">
                <label class="form-label">${fieldName}</label>
                <div class="input-group">
                    <input type="${isPassword ? 'password' : 'text'}" 
                           class="form-control network-credential" 
                           name="credentials[${field}]" 
                           id="field_${field}"
                           placeholder="Enter new ${fieldName} or leave empty"
                           value="">
                    ${isPassword ? `
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('field_${field}', event)">
                            <i class="ti ti-eye"></i>
                        </button>
                    ` : ''}
                </div>
                <small class="text-muted">Leave empty to keep current value</small>
            </div>
        `;
    });
    
    html += '</div>';
    document.getElementById('credentialsContainer').innerHTML = html;
}

// Reconnect to network and update credentials
function reconnectNetwork() {
    const credentials = {};
    
    if (isEditMode) {
        // Collect credentials from edit fields
        document.querySelectorAll('.network-credential').forEach(input => {
            const name = input.name.replace('credentials[', '').replace(']', '');
            if (input.value) {
                credentials[name] = input.value;
            } else if (currentCredentials[name]) {
                credentials[name] = currentCredentials[name];
            }
        });
    } else {
        // Use current credentials
        Object.assign(credentials, currentCredentials);
    }
    
    // Use current credentials if no new ones provided
    if (Object.keys(credentials).length === 0) {
        Object.assign(credentials, currentCredentials);
    }
    
    credentials.api_endpoint = document.querySelector('[name="api_endpoint"]').value;
    
    Swal.fire({
        title: 'Reconnecting',
        html: 'Attempting to reconnect and update credentials...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('{{ route("networks.reconnect") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            connection_id: connectionId,
            network_id: networkId,
            credentials: credentials
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update current credentials
            if (data.updated_credentials) {
                currentCredentials = data.updated_credentials;
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Reconnected Successfully!',
                html: data.message + '<br><small class="text-muted">Credentials have been updated.</small>',
                showConfirmButton: true
            }).then(() => {
                // Reload page to show updated data
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Reconnection Failed',
                html: `<p>${data.message}</p>${data.data?.hint ? '<small class="text-muted">' + data.data.hint + '</small>' : ''}`
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to reconnect to network'
        });
    });
}

// Test connection
function testConnectionNow() {
    let credentials = {};
    
    // Collect current credentials
    document.querySelectorAll('.network-credential').forEach(input => {
        const name = input.name.replace('credentials[', '').replace(']', '');
        if (input.value) {
            credentials[name] = input.value;
        } else if (currentCredentials[name]) {
            credentials[name] = currentCredentials[name];
        }
    });
    
    // Use current credentials if no new ones provided
    if (Object.keys(credentials).length === 0) {
        credentials = currentCredentials;
    }
    
    credentials.api_endpoint = document.querySelector('[name="api_endpoint"]').value;
    
    Swal.fire({
        title: 'Testing Connection',
        html: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    fetch('{{ route("networks.test-connection") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            network_id: networkId,
            credentials: credentials
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Connection Successful!',
                text: data.message
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Connection Failed',
                html: `<p>${data.message}</p>${data.data?.hint ? '<small class="text-muted">' + data.data.hint + '</small>' : ''}`
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to test connection'
        });
    });
}

// Toggle password visibility
function togglePasswordField(fieldId, event) {
    // Find the field - could be by ID or by finding sibling input
    let field = document.getElementById(fieldId);
    
    if (!field) {
        // Try to find the input field in the same group
        const button = event.currentTarget;
        field = button.closest('.input-group').querySelector('input');
    }
    
    if (!field) return;
    
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
</script>
@endsection
