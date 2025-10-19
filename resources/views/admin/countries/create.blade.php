@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Add New Country</h4>
                <p class="text-muted mb-0">Add a new country to the system</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back to Countries
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Country Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.countries.store') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Country Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="native_name" class="form-label">Native Name</label>
                                <input type="text" class="form-control @error('native_name') is-invalid @enderror" 
                                       id="native_name" name="native_name" value="{{ old('native_name') }}">
                                @error('native_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Country Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" 
                                       placeholder="US, GB, DE, etc." maxlength="2" required>
                                <div class="form-text">ISO 3166-1 alpha-2 country code</div>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone_code" class="form-label">Phone Code</label>
                                <input type="text" class="form-control @error('phone_code') is-invalid @enderror" 
                                       id="phone_code" name="phone_code" value="{{ old('phone_code') }}" 
                                       placeholder="+1, +44, +49, etc.">
                                @error('phone_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="currency_code" class="form-label">Currency Code</label>
                                <input type="text" class="form-control @error('currency_code') is-invalid @enderror" 
                                       id="currency_code" name="currency_code" value="{{ old('currency_code') }}" 
                                       placeholder="USD, EUR, GBP, etc." maxlength="3">
                                <div class="form-text">ISO 4217 currency code</div>
                                @error('currency_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                <input type="text" class="form-control @error('currency_symbol') is-invalid @enderror" 
                                       id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol') }}" 
                                       placeholder="$, €, £, etc." maxlength="5">
                                @error('currency_symbol')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select @error('timezone') is-invalid @enderror" 
                                        id="timezone" name="timezone">
                                    <option value="">Select Timezone</option>
                                    <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                    <option value="America/Los_Angeles" {{ old('timezone') == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                                    <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                    <option value="Europe/Paris" {{ old('timezone') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                                    <option value="Europe/Berlin" {{ old('timezone') == 'Europe/Berlin' ? 'selected' : '' }}>Europe/Berlin</option>
                                    <option value="Asia/Tokyo" {{ old('timezone') == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                                    <option value="Asia/Shanghai" {{ old('timezone') == 'Asia/Shanghai' ? 'selected' : '' }}>Asia/Shanghai</option>
                                    <option value="Asia/Dubai" {{ old('timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                                    <option value="Asia/Riyadh" {{ old('timezone') == 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh</option>
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="flag" class="form-label">Flag URL</label>
                                <input type="url" class="form-control @error('flag') is-invalid @enderror" 
                                       id="flag" name="flag" value="{{ old('flag') }}" 
                                       placeholder="https://example.com/flag.png">
                                @error('flag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="continent" class="form-label">Continent</label>
                                <select class="form-select @error('continent') is-invalid @enderror" 
                                        id="continent" name="continent">
                                    <option value="">Select Continent</option>
                                    <option value="North America" {{ old('continent') == 'North America' ? 'selected' : '' }}>North America</option>
                                    <option value="South America" {{ old('continent') == 'South America' ? 'selected' : '' }}>South America</option>
                                    <option value="Europe" {{ old('continent') == 'Europe' ? 'selected' : '' }}>Europe</option>
                                    <option value="Asia" {{ old('continent') == 'Asia' ? 'selected' : '' }}>Asia</option>
                                    <option value="Africa" {{ old('continent') == 'Africa' ? 'selected' : '' }}>Africa</option>
                                    <option value="Oceania" {{ old('continent') == 'Oceania' ? 'selected' : '' }}>Oceania</option>
                                    <option value="Antarctica" {{ old('continent') == 'Antarctica' ? 'selected' : '' }}>Antarctica</option>
                                </select>
                                @error('continent')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" 
                                        id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Create Country
                        </button>
                        <a href="{{ route('admin.countries.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Tips</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Country Code Guidelines</h6>
                    <ul class="mb-0">
                        <li>Use ISO 3166-1 alpha-2 codes</li>
                        <li>Examples: US, GB, DE, FR, JP</li>
                        <li>Must be exactly 2 characters</li>
                        <li>Must be unique across all countries</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Currency Information</h6>
                    <ul class="mb-0">
                        <li>Use ISO 4217 currency codes</li>
                        <li>Examples: USD, EUR, GBP, JPY</li>
                        <li>Currency symbol is optional</li>
                        <li>Common symbols: $, €, £, ¥</li>
                    </ul>
                </div>
                
                <div class="alert alert-success">
                    <h6 class="alert-heading">Flag Images</h6>
                    <ul class="mb-0">
                        <li>Use high-quality flag images</li>
                        <li>Recommended size: 32x24px</li>
                        <li>Common formats: PNG, SVG</li>
                        <li>Ensure proper aspect ratio</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Country code validation
    const codeInput = document.getElementById('code');
    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        if (this.value.length > 2) {
            this.value = this.value.substring(0, 2);
        }
    });
    
    // Currency code validation
    const currencyCodeInput = document.getElementById('currency_code');
    currencyCodeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        if (this.value.length > 3) {
            this.value = this.value.substring(0, 3);
        }
    });
    
    // URL validation for flag
    const flagInput = document.getElementById('flag');
    flagInput.addEventListener('blur', function() {
        if (this.value && !this.value.match(/^https?:\/\/.+/)) {
            this.setCustomValidity('Please enter a valid URL starting with http:// or https://');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
@endpush

