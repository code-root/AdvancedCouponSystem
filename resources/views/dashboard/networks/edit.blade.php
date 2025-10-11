@extends('layouts.vertical', ['title' => 'Edit Network Connection'])

@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css', 'node_modules/sweetalert2/dist/sweetalert2.min.css'])
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Networks', 'title' => 'Edit Connection'])

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
                            <button type="button" class="btn btn-sm btn-soft-primary" id="loadFieldsBtn">
                                <i class="ti ti-reload me-1"></i> Load Required Fields
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Note:</strong> Leave fields empty if you don't want to change them. Only fill in the fields you want to update.
                        </div>
                        
                        <div id="credentialsContainer" class="row">
                            <div class="col-12 text-center text-muted py-4">
                                <i class="ti ti-lock fs-48"></i>
                                <p class="mt-2">Click "Load Required Fields" to update credentials</p>
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
                            <button type="button" class="btn btn-soft-primary w-100" onclick="testConnectionNow()">
                                <i class="ti ti-plug-connected me-1"></i> Test Connection
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
let currentCredentials = @json($userConnection->credentials ?? []);

document.addEventListener('DOMContentLoaded', function() {
    // Load fields button
    document.getElementById('loadFieldsBtn')?.addEventListener('click', loadCredentialFields);
});

// Load credential fields based on network
function loadCredentialFields() {
    const btn = document.getElementById('loadFieldsBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';
    
    fetch(`/networks/${networkId}/config`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCredentialFields(data.data.required_fields);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load network configuration'
                });
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-reload me-1"></i> Load Required Fields';
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load configuration'
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-reload me-1"></i> Load Required Fields';
        });
}

// Render credential input fields
function renderCredentialFields(requiredFields) {
    let html = '<div class="row">';
    
    requiredFields.forEach(field => {
        const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        const isPassword = field.includes('secret') || field.includes('password') || field.includes('token') || field.includes('key');
        const currentValue = currentCredentials[field] || '';
        
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
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordField('field_${field}')">
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

// Test connection
function testConnectionNow() {
    const credentials = {};
    
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
</script>
@endsection
