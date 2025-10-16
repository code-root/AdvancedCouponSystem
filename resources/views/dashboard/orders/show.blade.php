@extends('layouts.vertical', ['title' => 'Purchase Details'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Purchases', 'title' => 'Purchase Details'])

    <div class="row">
        <div class="col-lg-8">
            <!-- Purchase Information -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Purchase Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Order ID</label>
                            <p class="mb-0"><strong>{{ $purchase->network_order_id ?: $purchase->order_id ?: $purchase->id ?: 'N/A' }}</strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Purchase Date</label>
                            <p class="mb-0">{{ $purchase->order_date ? $purchase->order_date->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Order Date</label>
                            <p class="mb-0">{{ $purchase->order_date ? $purchase->order_date->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @php
                                    $statusBadges = [
                                        'approved' => 'bg-success',
                                        'pending' => 'bg-warning',
                                        'rejected' => 'bg-danger',
                                        'cancelled' => 'bg-secondary'
                                    ];
                                    $badgeClass = $statusBadges[$purchase->status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ ucfirst($purchase->status) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Campaign & Coupon -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Campaign & Coupon Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Campaign</label>
                            <p class="mb-0">
                                @if($purchase->campaign)
                                    <a href="{{ route('campaigns.show', $purchase->campaign_id) }}">
                                        {{ $purchase->campaign->name }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Network</label>
                            <p class="mb-0">
                                @if($purchase->campaign && $purchase->campaign->network)
                                    <span class="badge bg-info-subtle text-info">
                                        {{ $purchase->campaign->network->display_name }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Purchase Type</label>
                            <p class="mb-0">
                                @if($purchase->purchase_type === 'coupon')
                                    <span class="badge bg-info-subtle text-info">
                                        <i class="ti ti-ticket me-1"></i>Coupon
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">
                                        <i class="ti ti-link me-1"></i>Direct Link
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Coupon</label>
                            <p class="mb-0">
                                @if($purchase->coupon)
                                    <a href="{{ route('coupons.show', $purchase->coupon_id) }}">
                                        {{ $purchase->coupon->code }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">User</label>
                            <p class="mb-0">{{ $purchase->user->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Details -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Financial Details</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Order Value</label>
                            <p class="mb-0">
                                <strong class="text-primary fs-5">
                                    {{ $purchase->currency ?? 'USD' }} {{ number_format($purchase->order_value ?? 0, 2) }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Commission</label>
                            <p class="mb-0">
                                <strong class="text-success fs-5">
                                    {{ $purchase->currency ?? 'USD' }} {{ number_format($purchase->commission ?? 0, 2) }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Revenue</label>
                            <p class="mb-0">
                                <strong class="text-info fs-5">
                                    {{ $purchase->currency ?? 'USD' }} {{ number_format($purchase->revenue ?? 0, 2) }}
                                </strong>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Quantity</label>
                            <p class="mb-0">{{ $purchase->quantity ?? 1 }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Currency</label>
                            <p class="mb-0">{{ $purchase->currency ?? 'USD' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted small">Country</label>
                            <p class="mb-0">{{ $purchase->country_code ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            @if($purchase->metadata)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Additional Information</h4>
                    </div>
                    <div class="card-body">
                        <pre class="mb-0 bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($purchase->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('orders.index') }}" class="btn btn-light">
                            <i class="ti ti-arrow-left me-1"></i> Back to Purchases
                        </a>
                        <a href="{{ route('orders.edit', $purchase->id) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i> Edit Purchase
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deletePurchase({{ $purchase->id }})">
                            <i class="ti ti-trash me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Quick Stats</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Commission Rate</small>
                        <h5 class="mb-0 text-success">
                            @if($purchase->order_value && $purchase->order_value > 0)
                                {{ number_format(($purchase->commission / $purchase->order_value) * 100, 2) }}%
                            @else
                                N/A
                            @endif
                        </h5>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Customer Type</small>
                        <h5 class="mb-0">{{ ucfirst($purchase->customer_type ?? 'N/A') }}</h5>
                    </div>
                    <div>
                        <small class="text-muted">Created At</small>
                        <h6 class="mb-0">{{ $purchase->created_at->format('Y-m-d H:i') }}</h6>
                    </div>
                </div>
            </div>

            <!-- Related Information -->
            @if($purchase->campaign)
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Campaign Info</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Campaign Name</small>
                            <p class="mb-0">
                                <a href="{{ route('campaigns.show', $purchase->campaign_id) }}">
                                    {{ $purchase->campaign->name }}
                                </a>
                            </p>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Campaign Type</small>
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ ucfirst($purchase->campaign->campaign_type ?? 'N/A') }}</span>
                            </p>
                        </div>
                        <div>
                            <small class="text-muted">Campaign Status</small>
                            <p class="mb-0">
                                <span class="badge {{ $purchase->campaign->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ ucfirst($purchase->campaign->status ?? 'N/A') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script>
function deletePurchase(id) {
    if (confirm('Are you sure you want to delete this purchase? This action cannot be undone.')) {
        fetch(`/purchases/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '{{ route("orders.index") }}';
            } else {
                alert('Failed to delete purchase');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting purchase');
        });
    }
}
</script>
@endsection

