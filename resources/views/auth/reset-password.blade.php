<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.title-meta', ['title' => 'Create New Password'])

    @include('layouts.partials.head-css')
</head>

<body class="h-100">

    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
                    <a href="{{ route('dashboard') }}" class="auth-brand mb-3">
                        <img src="/images/logo-dark.png" alt="dark logo" height="24" class="logo-dark">
                        <img src="/images/logo.png" alt="logo light" height="24" class="logo-light">
                    </a>

                    <h3 class="fw-semibold mb-2">Create New Password</h3>

                    <p class="text-muted mb-4">Please create your new password.</p>

                    <form method="POST" action="{{ route('password.update') }}" class="text-start mb-3">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

                        @if ($errors->any())
                            @foreach ($errors->all() as $error)
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $error }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endforeach
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email', $email ?? '') }}" placeholder="Enter your email" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Create New Password <small
                                    class="text-primary ms-1">Must be at least 8 characters</small></label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                placeholder="New Password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="password_confirmation">Reenter New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                                placeholder="Reenter Password" required>
                        </div>
                        <div class="mb-2 d-grid">
                            <button class="btn btn-primary" type="submit">Create New Password</button>
                        </div>

                    </form>

                    <p class="text-danger fs-14 mb-4">Back To <a href="{{ route('login') }}"
                            class="fw-semibold text-dark ms-1">Login!</a></p>

                    <p class="mt-auto mb-0">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © AdvancedCouponSystem - Affiliate Marketing Platform
                    </p>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.footer-scripts')

</body>

</html>

