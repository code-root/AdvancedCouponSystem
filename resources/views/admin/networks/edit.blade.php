@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Edit Network</h4>
                <p class="text-muted mb-0">Update network information and settings</p>
            </div>
            <div class="text-end">
                <a href="{{ route('admin.legacy.networks.show', $network->id) }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Network
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                <h3 class="mb-0 fw-bold text-primary">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Connections</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['active_connections'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Campaigns</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['total_campaigns'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">This Month Revenue</h5>
                <h3 class="mb-0 fw-bold text-warning">${{ number_format($stats['this_month_revenue'] ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Network Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.legacy.networks.update', $network->id) }}" method="POST" id="networkEditForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Network Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $network->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('display_name') is-invalid @enderror" 
                                       id="display_name" name="display_name" value="{{ old('display_name', $network->display_name) }}" required>
                                @error('display_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="website_url" class="form-label">Website URL</label>
                                <input type="url" class="form-control @error('website_url') is-invalid @enderror" 
                                       id="website_url" name="website_url" value="{{ old('website_url', $network->website_url) }}">
                                @error('website_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="api_endpoint" class="form-label">API Endpoint</label>
                                <input type="url" class="form-control @error('api_endpoint') is-invalid @enderror" 
                                       id="api_endpoint" name="api_endpoint" value="{{ old('api_endpoint', $network->api_endpoint) }}">
                                @error('api_endpoint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $network->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="commission_rate" class="form-label">Commission Rate (%)</label>
                                <input type="number" class="form-control @error('commission_rate') is-invalid @enderror" 
                                       id="commission_rate" name="commission_rate" 
                                       value="{{ old('commission_rate', $network->commission_rate) }}" 
                                       min="0" max="100" step="0.01">
                                @error('commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="payout_threshold" class="form-label">Payout Threshold ($)</label>
                                <input type="number" class="form-control @error('payout_threshold') is-invalid @enderror" 
                                       id="payout_threshold" name="payout_threshold" 
                                       value="{{ old('payout_threshold', $network->payout_threshold) }}" 
                                       min="0" step="0.01">
                                @error('payout_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="payout_frequency" class="form-label">Payout Frequency</label>
                                <select class="form-select @error('payout_frequency') is-invalid @enderror" 
                                        id="payout_frequency" name="payout_frequency">
                                    <option value="">Select Frequency</option>
                                    <option value="weekly" {{ old('payout_frequency', $network->payout_frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('payout_frequency', $network->payout_frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ old('payout_frequency', $network->payout_frequency) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                </select>
                                @error('payout_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Contact Email</label>
                                <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                       id="contact_email" name="contact_email" value="{{ old('contact_email', $network->contact_email) }}">
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_phone" class="form-label">Contact Phone</label>
                                <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                       id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $network->contact_phone) }}">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="4">{{ old('notes', $network->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   value="1" {{ old('is_active', $network->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active Network
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Update Network
                        </button>
                        <a href="{{ route('admin.legacy.networks.show', $network->id) }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('networkEditForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Disable button and show loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ti ti-loader me-1"></i>Updating...';
        
        // Submit form
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.notificationManager.success(data.message);
                setTimeout(() => {
                    window.location.href = "{{ route('admin.legacy.networks.show', $network->id) }}";
                }, 1000);
            } else {
                window.notificationManager.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.notificationManager.error('An error occurred while updating the network');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>
@endpush

