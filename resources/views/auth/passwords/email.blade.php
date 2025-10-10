<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset Password | Advanced Coupon System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Advanced Coupon System - Reset Password">
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
    </style>
</head>

<body class="d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-5 col-lg-6 col-md-8">
                <div class="auth-card">
                    <!-- Header -->
                    <div class="auth-header">
                        <div class="mb-3">
                            <div class="avatar-lg bg-white bg-opacity-20 rounded-circle mx-auto d-flex align-items-center justify-content-center">
                                <i class="ti ti-key fs-30 text-white"></i>
                            </div>
                        </div>
                        <h4 class="fw-bold mb-1">Reset Password</h4>
                        <p class="mb-0 opacity-75">Enter your email to receive reset instructions</p>
                    </div>

                    <!-- Reset Form -->
                    <div class="card-body p-4">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="ti ti-check-circle me-2"></i>
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" id="resetForm">
                            @csrf
                            
                            <!-- Email Field -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="ti ti-mail text-muted"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 border-0 bg-light @error('email') is-invalid @enderror" 
                                           name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autofocus>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="resetBtn">
                                    <i class="ti ti-send me-1"></i> Send Reset Link
                                </button>
                            </div>

                            <!-- Back to Login -->
                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-decoration-none">
                                    <i class="ti ti-arrow-left me-1"></i> Back to Login
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
        // Form submission with loading state
        document.getElementById('resetForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('resetBtn');
            submitBtn.innerHTML = '<i class="ti ti-loader-2 me-1 spinner-border spinner-border-sm"></i> Sending...';
            submitBtn.disabled = true;
        });

        // Show errors if any
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Reset Failed',
                text: 'Please check your email and try again.',
                confirmButtonColor: '#667eea'
            });
        @endif
    </script>
</body>
</html>
