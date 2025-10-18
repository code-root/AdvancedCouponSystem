<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.partials.title-meta', ['title' => 'Verify Email'])

    @include('layouts.partials.head-css')
</head>

<body class="h-100">

    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
                    <a href="{{ route('home') }}" class="auth-brand mb-3">
                        <img src="/images/trakifi-m.png" alt="dark logo" height="24" class="logo-dark">
                        <img src="/images/logo-tr.png" alt="logo light" height="24" class="logo-light">
                    </a>

                    <h3 class="fw-semibold mb-2">Verify Your Email Address</h3>

                    <p class="text-muted mb-4">We've sent a verification link to <strong>{{ auth()->user()->email }}</strong>. Please check your inbox and click the link to verify your account.</p>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="bg-body-secondary border border-dashed p-4 rounded my-3">
                        <i class="ti ti-mail fs-48 text-primary mb-3"></i>
                        <p class="mb-0">Check your email inbox for the verification link. If you don't see it, check your spam folder.</p>
                    </div>

                    <form method="POST" action="{{ route('verification.resend') }}" class="mb-3">
                        @csrf
                        <p class="mb-3 text-center">Didn't receive the email?</p>
                        <div class="d-grid">
                            <button class="btn btn-primary" type="submit">Resend Verification Email</button>
                        </div>
                    </form>

                    <p class="text-danger fs-14 mb-4">Back to <a href="{{ route('dashboard') }}" class="fw-semibold text-dark ms-1">Dashboard!</a></p>

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

