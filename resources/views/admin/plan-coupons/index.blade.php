@extends('admin.layouts.app')

@section('admin-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Plan Coupons</h4>
    <a href="{{ route('admin.plan-coupons.create') }}" class="btn btn-primary btn-sm">Add Coupon</a>
    </div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Redemptions</th>
                    <th>Expires</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($coupons as $coupon)
                <tr>
                    <td>{{ $coupon->code }}</td>
                    <td>{{ ucfirst($coupon->type) }}</td>
                    <td>{{ $coupon->value }}</td>
                    <td>{{ $coupon->redemptions_count }} @if($coupon->max_redemptions)/ {{ $coupon->max_redemptions }}@endif</td>
                    <td>{{ $coupon->expires_at ? $coupon->expires_at->format('Y-m-d') : '-' }}</td>
                    <td>{!! $coupon->active ? '<span class="badge bg-success">On</span>' : '<span class="badge bg-secondary">Off</span>' !!}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.plan-coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('admin.plan-coupons.destroy', $coupon) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete coupon?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $coupons->links() }}
    </div>
    </div>
@endsection


