@extends('dashboard.layouts.main')

@section('title', 'Change Password')

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
                                    <a href="{{ route('dashboard.profile') }}" class="text-decoration-none">My Profile</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Change Password</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-3 text-end">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">Change Password</h4>
                        <p class="text-muted mb-0">Update your password to keep your account secure.</p>
                    </div>
                    <a href="{{ route('dashboard.profile') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Back to Profile
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Password Settings</h5>
                        </div>
                        <div class="card-body">
                            <form id="passwordForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="ti ti-lock text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0 border-0 bg-light" 
                                               name="current_password" id="currentPassword" required>
                                        <button class="btn btn-outline-secondary border-start-0" type="button" 
                                                onclick="togglePassword('currentPassword')">
                                            <i class="ti ti-eye" id="currentPasswordIcon"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="ti ti-key text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0 border-0 bg-light" 
                                               name="password" id="newPassword" required minlength="8">
                                        <button class="btn btn-outline-secondary border-start-0" type="button" 
                                                onclick="togglePassword('newPassword')">
                                            <i class="ti ti-eye" id="newPasswordIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted">Password must be at least 8 characters long.</small>
                                    </div>
                                    
                                    <!-- Password Strength Indicator -->
                                    <div class="mt-2">
                                        <div class="progress" style="height: 4px;">
                                            <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted" id="passwordStrengthText">Password strength</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="ti ti-key text-muted"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0 border-0 bg-light" 
                                               name="password_confirmation" id="confirmPassword" required>
                                        <button class="btn btn-outline-secondary border-start-0" type="button" 
                                                onclick="togglePassword('confirmPassword')">
                                            <i class="ti ti-eye" id="confirmPasswordIcon"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        <small class="text-muted" id="passwordMatchText"></small>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="logoutOtherDevices">
                                        <label class="form-check-label" for="logoutOtherDevices">
                                            Logout from all other devices after password change
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="ti ti-device-floppy me-1"></i> Update Password
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                        <i class="ti ti-refresh me-1"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Security Tips -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">
                                <i class="ti ti-shield-check me-1"></i> Security Tips
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Use a combination of uppercase and lowercase letters
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Include numbers and special characters
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Avoid using personal information
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Don't reuse passwords from other accounts
                                </li>
                                <li class="mb-0">
                                    <i class="ti ti-check text-success me-2"></i>
                                    Consider using a password manager
                                </li>
                            </ul>
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
    // Password strength checker
    $('#newPassword').on('input', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        updatePasswordStrengthIndicator(strength);
    });

    // Password match checker
    $('#confirmPassword').on('input', function() {
        const newPassword = $('#newPassword').val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword === '') {
            $('#passwordMatchText').text('').removeClass('text-success text-danger');
        } else if (newPassword === confirmPassword) {
            $('#passwordMatchText').text('Passwords match').removeClass('text-danger').addClass('text-success');
        } else {
            $('#passwordMatchText').text('Passwords do not match').removeClass('text-success').addClass('text-danger');
        }
    });

    // Form submission
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        
        const newPassword = $('#newPassword').val();
        const confirmPassword = $('#confirmPassword').val();
        
        if (newPassword !== confirmPassword) {
            dashboardUtils.showError('Passwords do not match');
            return;
        }
        
        const formData = new FormData(this);
        
        dashboardUtils.showLoading('#submitBtn');
        
        fetch('{{ route("dashboard.password.update") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                dashboardUtils.showSuccess(data.message);
                resetForm();
            } else {
                dashboardUtils.showError(data.message);
            }
        })
        .catch(error => {
            dashboardUtils.showError('Failed to update password. Please try again.');
        })
        .finally(() => {
            dashboardUtils.hideLoading('#submitBtn', '<i class="ti ti-device-floppy me-1"></i> Update Password');
        });
    });
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + 'Icon');
    
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

function checkPasswordStrength(password) {
    let score = 0;
    
    // Length check
    if (password.length >= 8) score += 1;
    if (password.length >= 12) score += 1;
    
    // Character variety checks
    if (/[a-z]/.test(password)) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/[0-9]/.test(password)) score += 1;
    if (/[^A-Za-z0-9]/.test(password)) score += 1;
    
    return Math.min(score, 5);
}

function updatePasswordStrengthIndicator(strength) {
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');
    
    let percentage = (strength / 5) * 100;
    let color = 'bg-danger';
    let text = 'Very Weak';
    
    if (strength >= 2) {
        color = 'bg-warning';
        text = 'Weak';
    }
    if (strength >= 3) {
        color = 'bg-info';
        text = 'Fair';
    }
    if (strength >= 4) {
        color = 'bg-success';
        text = 'Good';
    }
    if (strength >= 5) {
        color = 'bg-success';
        text = 'Strong';
    }
    
    strengthBar.style.width = percentage + '%';
    strengthBar.className = 'progress-bar ' + color;
    strengthText.textContent = text + ' (' + Math.round(percentage) + '%)';
}

function resetForm() {
    document.getElementById('passwordForm').reset();
    $('#passwordMatchText').text('').removeClass('text-success text-danger');
    updatePasswordStrengthIndicator(0);
}
</script>
@endpush