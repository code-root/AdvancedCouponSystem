@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Admin Profile</h4>
                <p class="text-muted mb-0">Manage your admin account settings and preferences</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select @error('timezone') is-invalid @enderror" 
                                        id="timezone" name="timezone">
                                    <option value="UTC" {{ old('timezone', $user->timezone ?? 'UTC') == 'UTC' ? 'selected' : '' }}>UTC</option>
                                    <option value="America/New_York" {{ old('timezone', $user->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                    <option value="America/Los_Angeles" {{ old('timezone', $user->timezone ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>America/Los_Angeles</option>
                                    <option value="Europe/London" {{ old('timezone', $user->timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                    <option value="Europe/Paris" {{ old('timezone', $user->timezone ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris</option>
                                    <option value="Europe/Berlin" {{ old('timezone', $user->timezone ?? '') == 'Europe/Berlin' ? 'selected' : '' }}>Europe/Berlin</option>
                                    <option value="Asia/Tokyo" {{ old('timezone', $user->timezone ?? '') == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                                    <option value="Asia/Dubai" {{ old('timezone', $user->timezone ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai</option>
                                    <option value="Asia/Riyadh" {{ old('timezone', $user->timezone ?? '') == 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh</option>
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control @error('bio') is-invalid @enderror" 
                                  id="bio" name="bio" rows="3" 
                                  placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Update Profile
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.profile.password') }}" id="password-form">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                    onclick="togglePassword('current_password')">
                                <i class="ti ti-eye" id="current_password_icon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                   id="new_password" name="new_password" required>
                            <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                    onclick="togglePassword('new_password')">
                                <i class="ti ti-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        @error('new_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <input type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" 
                                   id="new_password_confirmation" name="new_password_confirmation" required>
                            <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                    onclick="togglePassword('new_password_confirmation')">
                                <i class="ti ti-eye" id="new_password_confirmation_icon"></i>
                            </button>
                        </div>
                        @error('new_password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="ti ti-key me-1"></i>Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Profile Preview</h5>
            </div>
            <div class="card-body text-center">
                <div class="avatar-lg bg-primary-subtle rounded mx-auto mb-3">
                    <span class="avatar-title bg-primary-subtle text-primary fs-24">
                        {{ substr($user->name, 0, 1) }}
                    </span>
                </div>
                
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                
                @if($user->phone)
                    <div class="mb-2">
                        <i class="ti ti-phone me-2"></i>
                        <span class="text-muted">{{ $user->phone }}</span>
                    </div>
                @endif
                
                @if($user->bio)
                    <div class="mb-3">
                        <p class="text-muted">{{ $user->bio }}</p>
                    </div>
                @endif
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="fw-bold text-primary">{{ $user->created_at->format('M Y') }}</div>
                        <small class="text-muted">Member since</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold text-success">{{ $user->active ? 'Active' : 'Inactive' }}</div>
                        <small class="text-muted">Status</small>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Account Security</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Security Tips</h6>
                    <ul class="mb-0">
                        <li>Use a strong, unique password</li>
                        <li>Enable two-factor authentication</li>
                        <li>Keep your email address updated</li>
                        <li>Log out from shared computers</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Password Requirements</h6>
                    <ul class="mb-0">
                        <li>At least 8 characters long</li>
                        <li>Mix of letters, numbers, and symbols</li>
                        <li>Not similar to your email</li>
                        <li>Not used in previous passwords</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
    } else {
        input.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Password confirmation validation
    const passwordForm = document.getElementById('password-form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('new_password_confirmation');
    
    function validatePasswordMatch() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    newPassword.addEventListener('input', validatePasswordMatch);
    confirmPassword.addEventListener('input', validatePasswordMatch);
    
    // Password strength indicator
    newPassword.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        updatePasswordStrengthIndicator(strength);
    });
    
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        return strength;
    }
    
    function updatePasswordStrengthIndicator(strength) {
        // This could be enhanced with a visual strength indicator
        console.log('Password strength:', strength);
    }
});
</script>
@endpush

