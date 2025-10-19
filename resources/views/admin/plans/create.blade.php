@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Create New Plan</h4>
                <p class="text-muted mb-0">Add a new subscription plan to the system</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Plans
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Plan Information</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.plans.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <input type="text" name="currency" value="{{ old('currency', 'USD') }}" class="form-control @error('currency') is-invalid @enderror" maxlength="3" required>
                            @error('currency')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Trial Days <span class="text-danger">*</span></label>
                            <input type="number" name="trial_days" value="{{ old('trial_days', 14) }}" class="form-control @error('trial_days') is-invalid @enderror" required>
                            @error('trial_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Networks <span class="text-danger">*</span></label>
                            <input type="number" name="max_networks" value="{{ old('max_networks', 1) }}" class="form-control @error('max_networks') is-invalid @enderror" required>
                            @error('max_networks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Daily Sync Limit</label>
                            <input type="number" name="daily_sync_limit" class="form-control @error('daily_sync_limit') is-invalid @enderror" value="{{ old('daily_sync_limit') }}">
                            @error('daily_sync_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Monthly Sync Limit</label>
                            <input type="number" name="monthly_sync_limit" class="form-control @error('monthly_sync_limit') is-invalid @enderror" value="{{ old('monthly_sync_limit') }}">
                            @error('monthly_sync_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Revenue Cap</label>
                            <input type="number" step="0.01" name="revenue_cap" class="form-control @error('revenue_cap') is-invalid @enderror" value="{{ old('revenue_cap') }}">
                            @error('revenue_cap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Orders Cap</label>
                            <input type="number" name="orders_cap" class="form-control @error('orders_cap') is-invalid @enderror" value="{{ old('orders_cap') }}">
                            @error('orders_cap')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Window Unit <span class="text-danger">*</span></label>
                            <select name="sync_window_unit" class="form-select @error('sync_window_unit') is-invalid @enderror" required>
                                <option value="minute" {{ old('sync_window_unit') == 'minute' ? 'selected' : '' }}>Minute</option>
                                <option value="hour" {{ old('sync_window_unit') == 'hour' ? 'selected' : '' }}>Hour</option>
                                <option value="day" {{ old('sync_window_unit', 'day') == 'day' ? 'selected' : '' }}>Day</option>
                            </select>
                            @error('sync_window_unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Window Size <span class="text-danger">*</span></label>
                            <input type="number" name="sync_window_size" value="{{ old('sync_window_size', 1) }}" class="form-control @error('sync_window_size') is-invalid @enderror" required>
                            @error('sync_window_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Allowed From</label>
                            <input type="time" name="sync_allowed_from_time" class="form-control @error('sync_allowed_from_time') is-invalid @enderror" value="{{ old('sync_allowed_from_time') }}">
                            @error('sync_allowed_from_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Allowed To</label>
                            <input type="time" name="sync_allowed_to_time" class="form-control @error('sync_allowed_to_time') is-invalid @enderror" value="{{ old('sync_allowed_to_time') }}">
                            @error('sync_allowed_to_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active Plan</label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Create Plan
                        </button>
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-light">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


