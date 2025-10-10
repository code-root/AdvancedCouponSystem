@extends('dashboard.layouts.main')

@section('title', 'Create Broker')

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
                                <li class="breadcrumb-item active" aria-current="page">Create Broker</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">Create New Broker</h4>
                        <p class="text-muted mb-0">Add a new broker to your system</p>
                    </div>
                    <a href="{{ route('brokers.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

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

            <div class="row">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Broker Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('brokers.store') }}" method="POST" id="brokerForm">
                                @csrf
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <label class="form-label">Select Broker <span class="text-danger">*</span></label>
                                        <select class="form-select form-select-lg" name="broker_id" id="brokerSelect" required>
                                            <option value="">Choose a broker...</option>
                                            @foreach($brokers as $broker)
                                                <option value="{{ $broker->id }}" 
                                                        data-name="{{ $broker->name }}"
                                                        data-display="{{ $broker->display_name }}"
                                                        data-description="{{ $broker->description }}"
                                                        data-api-url="{{ $broker->api_url }}"
                                                        {{ old('broker_id') == $broker->id ? 'selected' : '' }}>
                                                    {{ $broker->display_name }} - {{ $broker->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Broker Info Display -->
                                    <div class="col-12 mb-3" id="brokerInfo" style="display: none;">
                                        <div class="alert alert-info">
                                            <h6 class="alert-heading mb-2" id="selectedBrokerName"></h6>
                                            <p class="mb-0" id="selectedBrokerDesc"></p>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Connection Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-tag text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control" name="connection_name" 
                                                   value="{{ old('connection_name') }}" 
                                                   placeholder="e.g., My Boostiny Account" required>
                                        </div>
                                        <small class="text-muted">Give this connection a memorable name</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">API URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-world text-muted"></i>
                                            </span>
                                            <input type="url" class="form-control" name="api_endpoint" 
                                                   id="apiUrlInput" value="{{ old('api_endpoint') }}" readonly>
                                        </div>
                                        <small class="text-muted">Auto-filled based on selected broker</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">API URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-world text-muted"></i>
                                            </span>
                                            <input type="url" class="form-control" name="api_url" 
                                                   value="{{ old('api_url') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">API Key</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-key text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control" name="api_key" 
                                                   value="{{ old('api_key') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Country</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-flag text-muted"></i>
                                            </span>
                                            <select class="form-select" name="country_id">
                                                <option value="">Select Country</option>
                                                @foreach(\App\Models\Country::all() as $country)
                                                    <option value="{{ $country->id }}" 
                                                            {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-toggle-left text-muted"></i>
                                            </span>
                                            <select class="form-select" name="is_active">
                                                <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="4">{{ old('description') }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Create Broker
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="ti ti-refresh me-1"></i> Reset
                                    </button>
                                    <a href="{{ route('brokers.index') }}" class="btn btn-outline-danger">
                                        <i class="ti ti-x me-1"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">
                                <i class="ti ti-info-circle me-1"></i> Help
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-semibold mb-2">Connection Steps</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Select your broker
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Enter API credentials
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Give connection a name
                                </li>
                                <li class="mb-0">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Test & activate
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Selected Broker Info -->
                    <div class="card border-0 shadow-sm mt-3" id="brokerDetailsCard" style="display: none;">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">
                                <i class="ti ti-info-circle me-1"></i> Broker Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <label class="form-label fw-semibold">Name</label>
                                <p class="mb-0" id="detailBrokerName">-</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-semibold">API URL</label>
                                <p class="mb-0 text-break" id="detailApiUrl">-</p>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Features</label>
                                <div id="detailFeatures">-</div>
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
    // Handle broker selection
    $('#brokerSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        
        if (selectedOption.val()) {
            const brokerName = selectedOption.data('display');
            const brokerDesc = selectedOption.data('description');
            const apiUrl = selectedOption.data('api-url');
            
            // Show broker info alert
            $('#selectedBrokerName').text(brokerName);
            $('#selectedBrokerDesc').text(brokerDesc);
            $('#brokerInfo').slideDown();
            
            // Update API URL field
            $('#apiUrlInput').val(apiUrl);
            
            // Show broker details card
            $('#detailBrokerName').text(brokerName);
            $('#detailApiUrl').text(apiUrl);
            $('#brokerDetailsCard').slideDown();
            
            // Auto-fill connection name
            if (!$('input[name="connection_name"]').val()) {
                $('input[name="connection_name"]').val('My ' + brokerName + ' Account');
            }
        } else {
            $('#brokerInfo').slideUp();
            $('#brokerDetailsCard').slideUp();
            $('#apiUrlInput').val('');
        }
    });
    
    // Trigger change if broker is pre-selected
    if ($('#brokerSelect').val()) {
        $('#brokerSelect').trigger('change');
    }
    
    // Form validation
    $('#brokerForm').on('submit', function(e) {
        if (!$('#brokerSelect').val()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Select Broker',
                text: 'Please select a broker to connect',
            });
            return false;
        }
    });
});
</script>
@endpush

