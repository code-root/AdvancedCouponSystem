@extends('admin.layouts.app')

@section('admin-content')
<h4 class="mb-3">Create Plan Coupon</h4>
<form method="POST" action="{{ route('admin.plan-coupons.store') }}" class="card card-body">
    @csrf
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Code</label>
            <input type="text" name="code" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
                <option value="percent">Percent</option>
                <option value="fixed">Fixed</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Value</label>
            <input type="number" step="0.01" name="value" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Max Redemptions</label>
            <input type="number" name="max_redemptions" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Expires At</label>
            <input type="date" name="expires_at" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="active" id="active" checked>
                <label class="form-check-label" for="active">Active</label>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('admin.plan-coupons.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@endsection


