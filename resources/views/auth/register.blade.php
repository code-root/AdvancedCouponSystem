<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register | Advanced Coupon System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Advanced Coupon System - Register">
    <meta name="author" content="Advanced Coupon System">
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    
    <!-- Bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- App css -->
    <link href="https://coderthemes.com/greeva/layouts/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />
    <link href="https://coderthemes.com/greeva/layouts/assets/css/app.min.css" rel="stylesheet" type="text/css" />
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/1.34.0/iconfont/tabler-icons.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .auth-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .user-type-card {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .user-type-card:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        .user-type-card.selected {
            border-color: #667eea;
            background-color: #667eea;
            color: white;
        }
    </style>
</head>

<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8 col-md-10">
                <div class="auth-card">
                    <!-- Header -->
                    <div class="auth-header">
                        <div class="mb-3">
                            <div class="avatar-lg bg-white bg-opacity-20 rounded-circle mx-auto d-flex align-items-center justify-content-center">
                                <i class="ti ti-user-plus fs-30 text-white"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-1">Create Account</h4>
                        <p class="mb-0 opacity-75">Join our affiliate marketing platform</p>
                    </div>

                    <!-- Registration Form -->
                    <div class="card-body p-4">
                        <form method="POST" action="{{ route('register') }}" id="registerForm">
                            @csrf
                            
                            <!-- Full Name -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="ti ti-user text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 border-0 bg-light @error('name') is-invalid @enderror" 
                                           name="name" value="{{ old('name') }}" placeholder="Enter your full name" required>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="ti ti-mail text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 border-0 bg-light @error('email') is-invalid @enderror" 
                                           name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- User Type Selection -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Account Type</label>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <div class="user-type-card text-center" onclick="selectUserType('network')" data-type="network">
                                            <i class="ti ti-building fs-24 mb-2 d-block"></i>
                                            <h6 class="mb-1">Network</h6>
                                            <small>Manage multiple affiliates</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="user-type-card text-center" onclick="selectUserType('affiliate')" data-type="affiliate">
                                            <i class="ti ti-users fs-24 mb-2 d-block"></i>
                                            <h6 class="mb-1">Affiliate</h6>
                                            <small>Promote products & earn</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <div class="user-type-card text-center" onclick="selectUserType('influencer')" data-type="influencer">
                                            <i class="ti ti-star fs-24 mb-2 d-block"></i>
                                            <h6 class="mb-1">Influencer</h6>
                                            <small>Create content & influence</small>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="user_type" id="userTypeInput" value="{{ old('user_type') }}" required>
                                @error('user_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="ti ti-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 border-0 bg-light @error('password') is-invalid @enderror" 
                                           name="password" placeholder="Create a password" required minlength="8">
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('password')">
                                        <i class="ti ti-eye" id="passwordToggleIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
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

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="ti ti-lock text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 border-0 bg-light @error('password_confirmation') is-invalid @enderror" 
                                           name="password_confirmation" placeholder="Confirm your password" required>
                                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePassword('password_confirmation')">
                                        <i class="ti ti-eye" id="confirmPasswordToggleIcon"></i>
                                    </button>
                                </div>
                                @error('password_confirmation')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted" id="passwordMatchText"></small>
                                </div>
                            </div>

                            <!-- Terms and Conditions -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a> and <a href="#" class="text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>

                            <!-- Register Button -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="registerBtn">
                                    <i class="ti ti-user-plus me-1"></i> Create Account
                                </button>
                            </div>

                            <!-- Divider -->
                            <div class="text-center mb-3">
                                <span class="text-muted">Already have an account?</span>
                            </div>

                            <!-- Login Link -->
                            <div class="d-grid">
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="ti ti-login me-1"></i> Sign In
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-4">
                    <p class="text-white-50">
                        &copy; {{ date('Y') }} Advanced Coupon System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Select user type
        function selectUserType(type) {
            // Remove selected class from all cards
            document.querySelectorAll('.user-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            document.querySelector(`[data-type="${type}"]`).classList.add('selected');
            
            // Set hidden input value
            document.getElementById('userTypeInput').value = type;
        }

        // Toggle password visibility
        function togglePassword(inputName) {
            const input = document.querySelector(`input[name="${inputName}"]`);
            const iconId = inputName === 'password' ? 'passwordToggleIcon' : 'confirmPasswordToggleIcon';
            const icon = document.getElementById(iconId);
            
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

        // Password strength checker
        function checkPasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score += 1;
            if (password.length >= 12) score += 1;
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
            
            if (strength >= 2) { color = 'bg-warning'; text = 'Weak'; }
            if (strength >= 3) { color = 'bg-info'; text = 'Fair'; }
            if (strength >= 4) { color = 'bg-success'; text = 'Good'; }
            if (strength >= 5) { color = 'bg-success'; text = 'Strong'; }
            
            strengthBar.style.width = percentage + '%';
            strengthBar.className = 'progress-bar ' + color;
            strengthText.textContent = text + ' (' + Math.round(percentage) + '%)';
        }

        // Password match checker
        document.querySelector('input[name="password_confirmation"]').addEventListener('input', function() {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = this.value;
            const matchText = document.getElementById('passwordMatchText');
            
            if (confirmPassword === '') {
                matchText.textContent = '';
                matchText.className = 'text-muted';
            } else if (password === confirmPassword) {
                matchText.textContent = 'Passwords match';
                matchText.className = 'text-success';
            } else {
                matchText.textContent = 'Passwords do not match';
                matchText.className = 'text-danger';
            }
        });

        // Password strength indicator
        document.querySelector('input[name="password"]').addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            updatePasswordStrengthIndicator(strength);
        });

        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const userType = document.getElementById('userTypeInput').value;
            if (!userType) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Account Type Required',
                    text: 'Please select an account type.',
                    confirmButtonColor: '#667eea'
                });
                return;
            }

            const submitBtn = document.getElementById('registerBtn');
            submitBtn.innerHTML = '<i class="ti ti-loader-2 me-1 spinner-border spinner-border-sm"></i> Creating Account...';
            submitBtn.disabled = true;
        });

        // Initialize user type selection if old value exists
        @if(old('user_type'))
            selectUserType('{{ old('user_type') }}');
        @endif

        // Show errors if any
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Registration Failed',
                text: 'Please check the form and try again.',
                confirmButtonColor: '#667eea'
            });
        @endif
    </script>
</body>
</html>