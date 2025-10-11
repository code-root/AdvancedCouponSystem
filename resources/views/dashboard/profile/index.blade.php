@extends('layouts.vertical', ['title' => 'Profile'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Dashboard', 'title' => 'Profile'])

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar-xl mx-auto mb-3">
                        <img src="/images/users/avatar-1.jpg" alt="user-image" class="img-fluid rounded-circle">
                    </div>
                    <h4 class="mb-1">{{ auth()->user()->name }}</h4>
                    <p class="text-muted mb-3">{{ auth()->user()->email }}</p>
                    
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-primary-subtle text-primary">{{ auth()->user()->roles->pluck('name')->join(', ') }}</span>
                    </div>

                    <div class="mt-4">
                        <h5 class="text-muted fs-13 text-uppercase mb-2">Account Statistics</h5>
                        <div class="row text-center">
                            <div class="col-4">
                                <p class="text-muted mb-1">Networks</p>
                                <h4 class="mb-0">{{ auth()->user()->getActiveNetworkConnectionsCount() }}</h4>
                            </div>
                            <div class="col-4">
                                <p class="text-muted mb-1">Campaigns</p>
                                <h4 class="mb-0">{{ auth()->user()->campaigns()->count() }}</h4>
                            </div>
                            <div class="col-4">
                                <p class="text-muted mb-1">Coupons</p>
                                <h4 class="mb-0">{{ auth()->user()->coupons()->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Profile Information</h4>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('dashboard.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                        id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                        id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password (leave blank to keep unchanged)</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                        id="current_password" name="current_password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                                <a href="{{ route('dashboard.password.change') }}" class="btn btn-secondary">Change Password</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
