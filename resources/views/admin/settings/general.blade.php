@extends('admin.layouts.ajax-wrapper')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">General Settings</h4>
                <p class="text-muted mb-0">Configure general site settings and preferences</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Site Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.general.save') }}" 
                      data-ajax="true" 
                      data-ajax-url="{{ route('admin.settings.general.save') }}"
                      data-success-message="General settings updated successfully"
                      data-real-time-validation="true">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                       id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" 
                                       required maxlength="255" data-validation="required">
                                @error('site_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_url" class="form-label">Site URL</label>
                                <input type="url" class="form-control @error('site_url') is-invalid @enderror" 
                                       id="site_url" name="site_url" value="{{ old('site_url', $settings['site_url'] ?? '') }}"
                                       maxlength="255" data-validation="url">
                                @error('site_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Default Timezone</label>
                                <select class="form-select @error('timezone') is-invalid @enderror" 
                                        id="timezone" name="timezone">
                                    <option value="UTC" {{ old('timezone', $settings['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ old('timezone', $settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                    <option value="Europe/London" {{ old('timezone', $settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                    <option value="Asia/Dubai" {{ old('timezone', $settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                                    <option value="Asia/Riyadh" {{ old('timezone', $settings['timezone'] ?? '') == 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh</option>
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="locale" class="form-label">Default Language</label>
                                <select class="form-select @error('locale') is-invalid @enderror" 
                                        id="locale" name="locale">
                                    <option value="en" {{ old('locale', $settings['locale'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="ar" {{ old('locale', $settings['locale'] ?? '') == 'ar' ? 'selected' : '' }}>العربية</option>
                                    <option value="fr" {{ old('locale', $settings['locale'] ?? '') == 'fr' ? 'selected' : '' }}>Français</option>
                                    <option value="es" {{ old('locale', $settings['locale'] ?? '') == 'es' ? 'selected' : '' }}>Español</option>
                                </select>
                                @error('locale')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                                <select class="form-select @error('maintenance_mode') is-invalid @enderror" 
                                        id="maintenance_mode" name="maintenance_mode">
                                    <option value="0" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : '' }}>Disabled</option>
                                    <option value="1" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? '') == '1' ? 'selected' : '' }}>Enabled</option>
                                </select>
                                @error('maintenance_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="registration_enabled" class="form-label">User Registration</label>
                                <select class="form-select @error('registration_enabled') is-invalid @enderror" 
                                        id="registration_enabled" name="registration_enabled">
                                    <option value="1" {{ old('registration_enabled', $settings['registration_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                                    <option value="0" {{ old('registration_enabled', $settings['registration_enabled'] ?? '') == '0' ? 'selected' : '' }}>Disabled</option>
                                </select>
                                @error('registration_enabled')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="maintenance_message" class="form-label">Maintenance Message</label>
                        <textarea class="form-control @error('maintenance_message') is-invalid @enderror" 
                                  id="maintenance_message" name="maintenance_message" rows="3" 
                                  placeholder="Message to display when site is in maintenance mode">{{ old('maintenance_message', $settings['maintenance_message'] ?? '') }}</textarea>
                        @error('maintenance_message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" data-loading-text="Saving...">
                            <i class="ti ti-device-floppy me-1"></i>Save Settings
                        </button>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
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
                    <h6 class="alert-heading">General Settings Guidelines</h6>
                    <ul class="mb-0">
                        <li>Site name appears in browser title</li>
                        <li>Timezone affects all date displays</li>
                        <li>Maintenance mode blocks public access</li>
                        <li>Registration can be disabled for private sites</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Important Notes</h6>
                    <ul class="mb-0">
                        <li>Changes take effect immediately</li>
                        <li>Maintenance mode affects all users</li>
                        <li>Backup settings before major changes</li>
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
    // Maintenance mode warning
    const maintenanceMode = document.getElementById('maintenance_mode');
    if (maintenanceMode) {
        maintenanceMode.addEventListener('change', function() {
            if (this.checked) {
                if (!confirm('Are you sure you want to enable maintenance mode? This will block all public access to the site.')) {
                    this.checked = false;
                }
            }
        });
    }
    
    // Form success handler
    document.addEventListener('form:success', function(e) {
        // Additional success handling if needed
        console.log('General settings saved successfully');
    });
    
    // Form error handler
    document.addEventListener('form:error', function(e) {
        // Additional error handling if needed
        console.error('Error saving general settings:', e.detail.error);
    });
});
</script>
@endpush
