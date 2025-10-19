@extends('admin.layouts.app')

@section('admin-content')
<h4 class="mb-3">Edit Plan</h4>
<form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="card card-body">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
        </div>
        <div class="col-md-8">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" value="{{ old('description', $plan->description) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $plan->price) }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" value="{{ old('currency', $plan->currency) }}" class="form-control" maxlength="3" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Trial Days</label>
            <input type="number" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Max Networks</label>
            <input type="number" name="max_networks" value="{{ old('max_networks', $plan->max_networks) }}" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Daily Sync Limit</label>
            <input type="number" name="daily_sync_limit" value="{{ old('daily_sync_limit', $plan->daily_sync_limit) }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Monthly Sync Limit</label>
            <input type="number" name="monthly_sync_limit" value="{{ old('monthly_sync_limit', $plan->monthly_sync_limit) }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Revenue Cap</label>
            <input type="number" step="0.01" name="revenue_cap" value="{{ old('revenue_cap', $plan->revenue_cap) }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Orders Cap</label>
            <input type="number" name="orders_cap" value="{{ old('orders_cap', $plan->orders_cap) }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Window Unit</label>
            <select name="sync_window_unit" class="form-select" required>
                <option value="minute" @selected(old('sync_window_unit', $plan->sync_window_unit)==='minute')>Minute</option>
                <option value="hour" @selected(old('sync_window_unit', $plan->sync_window_unit)==='hour')>Hour</option>
                <option value="day" @selected(old('sync_window_unit', $plan->sync_window_unit)==='day')>Day</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Window Size</label>
            <input type="number" name="sync_window_size" value="{{ old('sync_window_size', $plan->sync_window_size) }}" class="form-control" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Allowed From</label>
            <input type="time" name="sync_allowed_from_time" value="{{ old('sync_allowed_from_time', $plan->sync_allowed_from_time ? \Carbon\Carbon::parse($plan->sync_allowed_from_time)->format('H:i') : null) }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label class="form-label">Allowed To</label>
            <input type="time" name="sync_allowed_to_time" value="{{ old('sync_allowed_to_time', $plan->sync_allowed_to_time ? \Carbon\Carbon::parse($plan->sync_allowed_to_time)->format('H:i') : null) }}" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" @checked(old('is_active', $plan->is_active))>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('admin.plans.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@endsection


