@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Branding Settings</h4>
                <p class="text-muted mb-0">Customize your site's visual identity and branding</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Logo & Favicon</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.branding.save') }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Site Name -->
                    <div class="mb-4">
                        <label for="site_name" class="form-label">Site Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                               id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" required>
                        @error('site_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Favicon -->
                    <div class="mb-4">
                        <label for="favicon" class="form-label">Favicon</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control @error('favicon') is-invalid @enderror" 
                                       id="favicon" name="favicon" accept="image/*">
                                @error('favicon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Recommended: 32x32px, PNG or ICO format</div>
                            </div>
                            <div class="col-md-6">
                                @if(isset($settings['favicon']) && $settings['favicon'])
                                    <img src="{{ $settings['favicon'] }}" alt="Current favicon" class="img-thumbnail" style="width: 32px; height: 32px;">
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Main Logo -->
                    <div class="mb-4">
                        <label for="logo" class="form-label">Main Logo</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                       id="logo" name="logo" accept="image/*">
                                @error('logo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Recommended: 200x60px, PNG format with transparent background</div>
                            </div>
                            <div class="col-md-6">
                                @if(isset($settings['logo']) && $settings['logo'])
                                    <img src="{{ $settings['logo'] }}" alt="Current logo" class="img-thumbnail" style="max-height: 60px;">
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Light Logo -->
                    <div class="mb-4">
                        <label for="logo_light" class="form-label">Light Logo (for dark backgrounds)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control @error('logo_light') is-invalid @enderror" 
                                       id="logo_light" name="logo_light" accept="image/*">
                                @error('logo_light')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Light version of your logo for dark themes</div>
                            </div>
                            <div class="col-md-6">
                                @if(isset($settings['logo_light']) && $settings['logo_light'])
                                    <img src="{{ $settings['logo_light'] }}" alt="Current light logo" class="img-thumbnail" style="max-height: 60px;">
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dark Logo -->
                    <div class="mb-4">
                        <label for="logo_dark" class="form-label">Dark Logo (for light backgrounds)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control @error('logo_dark') is-invalid @enderror" 
                                       id="logo_dark" name="logo_dark" accept="image/*">
                                @error('logo_dark')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Dark version of your logo for light themes</div>
                            </div>
                            <div class="col-md-6">
                                @if(isset($settings['logo_dark']) && $settings['logo_dark'])
                                    <img src="{{ $settings['logo_dark'] }}" alt="Current dark logo" class="img-thumbnail" style="max-height: 60px;">
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Small Logo -->
                    <div class="mb-4">
                        <label for="logo_sm" class="form-label">Small Logo (for mobile/sidebar)</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control @error('logo_sm') is-invalid @enderror" 
                                       id="logo_sm" name="logo_sm" accept="image/*">
                                @error('logo_sm')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Recommended: 40x40px, square format</div>
                            </div>
                            <div class="col-md-6">
                                @if(isset($settings['logo_sm']) && $settings['logo_sm'])
                                    <img src="{{ $settings['logo_sm'] }}" alt="Current small logo" class="img-thumbnail" style="width: 40px; height: 40px;">
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Save Branding
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
                <h5 class="card-title mb-0">Brand Guidelines</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Logo Requirements</h6>
                    <ul class="mb-0">
                        <li>Main logo: 200x60px max</li>
                        <li>Small logo: 40x40px square</li>
                        <li>Favicon: 32x32px</li>
                        <li>Formats: PNG, JPG, SVG</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Best Practices</h6>
                    <ul class="mb-0">
                        <li>Use transparent backgrounds</li>
                        <li>Keep file sizes under 500KB</li>
                        <li>Test on different backgrounds</li>
                        <li>Ensure readability at small sizes</li>
                    </ul>
                </div>
                
                <div class="alert alert-success">
                    <h6 class="alert-heading">Preview</h6>
                    <p class="mb-0">Changes will be visible immediately after saving.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (500KB max)
                if (file.size > 500 * 1024) {
                    alert('File size must be less than 500KB');
                    this.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF, SVG)');
                    this.value = '';
                    return;
                }
            }
        });
    });
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
@endpush
