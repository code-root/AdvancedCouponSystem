@extends('admin.layouts.app')

@section('admin-content')
<h4 class="mb-3">Edit Plan Coupon</h4>
<form method="POST" action="{{ route('admin.plan-coupons.update', $coupon) }}" class="card card-body">
    @csrf
    @method('PUT')
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Code</label>
            <input type="text" name="code" class="form-control" value="{{ old('code', $coupon->code) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
                <option value="percent" @selected(old('type', $coupon->type)==='percent')>Percent</option>
                <option value="fixed" @selected(old('type', $coupon->type)==='fixed')>Fixed</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Value</label>
            <input type="number" step="0.01" name="value" class="form-control" value="{{ old('value', $coupon->value) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Max Redemptions</label>
            <input type="number" name="max_redemptions" class="form-control" value="{{ old('max_redemptions', $coupon->max_redemptions) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Expires At</label>
            <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : null) }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="active" id="active" @checked(old('active', $coupon->active))>
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


