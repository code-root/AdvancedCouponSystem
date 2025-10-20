@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Edit Subscription #{{ $subscription->id }}</h4>
                <p class="text-muted mb-0">Update subscription details and settings</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Subscriptions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subscription Statistics -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Current Status</h5>
                <h3 class="mb-0 fw-bold text-{{ $subscription->status === 'active' ? 'success' : ($subscription->status === 'trialing' ? 'info' : 'warning') }}">
                    {{ ucfirst($subscription->status) }}
                </h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Plan Price</h5>
                <h3 class="mb-0 fw-bold text-primary">${{ number_format($subscription->plan->price ?? 0, 2) }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Days Active</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $subscription->starts_at ? $subscription->starts_at->diffInDays(now()) : 0 }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Gateway</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ ucfirst($subscription->gateway ?? 'Manual') }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Subscription Information</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.subscriptions.update', $subscription->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">User <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="{{ $subscription->user->name ?? 'N/A' }} ({{ $subscription->user->email ?? 'N/A' }})" disabled>
                            <small class="text-muted">User cannot be changed</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Plan <span class="text-danger">*</span></label>
                            <select name="plan_id" class="form-select @error('plan_id') is-invalid @enderror" required>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id', $subscription->plan_id) == $plan->id)>
                                        {{ $plan->name }} (${{ number_format($plan->price, 2) }} {{ $plan->currency }})
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(['active','trialing','canceled','past_due','expired'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $subscription->status) == $status)>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="datetime-local" name="starts_at" class="form-control @error('starts_at') is-invalid @enderror" 
                                   value="{{ old('starts_at', $subscription->starts_at ? $subscription->starts_at->format('Y-m-d\TH:i') : '') }}">
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="datetime-local" name="ends_at" class="form-control @error('ends_at') is-invalid @enderror" 
                                   value="{{ old('ends_at', $subscription->ends_at ? $subscription->ends_at->format('Y-m-d\TH:i') : '') }}">
                            @error('ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trial End Date</label>
                            <input type="datetime-local" name="trial_ends_at" class="form-control @error('trial_ends_at') is-invalid @enderror" 
                                   value="{{ old('trial_ends_at', $subscription->trial_ends_at ? $subscription->trial_ends_at->format('Y-m-d\TH:i') : '') }}">
                            @error('trial_ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gateway</label>
                            <select name="gateway" class="form-select @error('gateway') is-invalid @enderror">
                                <option value="">Select Gateway</option>
                                <option value="stripe" @selected(old('gateway', $subscription->gateway) == 'stripe')>Stripe</option>
                                <option value="paypal" @selected(old('gateway', $subscription->gateway) == 'paypal')>PayPal</option>
                                <option value="manual" @selected(old('gateway', $subscription->gateway) == 'manual')>Manual</option>
                            </select>
                            @error('gateway')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Update Subscription
                        </button>
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-light">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


