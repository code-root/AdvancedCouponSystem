@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">{{ $network->display_name }}</h4>
                <p class="text-muted mb-0">Network details and performance</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <div class="row g-2 mb-0 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.networks.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to Networks
                        </a>
                    </div>
                    <div class="col-auto">
                        <form action="{{ route('admin.networks.toggle-status', $network->id) }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="is_active" value="{{ $network->is_active ? 0 : 1 }}">
                            <button type="submit" class="btn btn-{{ $network->is_active ? 'warning' : 'success' }}">
                                <i class="ti ti-{{ $network->is_active ? 'ban' : 'check' }} me-1"></i>
                                {{ $network->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Network Overview -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Network Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Display Name</label>
                            <p class="text-muted">{{ $network->display_name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Network Name</label>
                            <p class="text-muted">{{ $network->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <p>
                                <span class="badge bg-{{ $network->is_active ? 'success' : 'danger' }}-subtle text-{{ $network->is_active ? 'success' : 'danger' }}">
                                    {{ $network->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">API URL</label>
                            <p class="text-muted">{{ $network->api_url ?? 'Not configured' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Created</label>
                            <p class="text-muted">{{ $network->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Last Updated</label>
                            <p class="text-muted">{{ $network->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Quick Stats</h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Connected Users</span>
                    <span class="fw-semibold text-primary">{{ $network->connections->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Campaigns</span>
                    <span class="fw-semibold text-info">{{ $network->campaigns->count() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Orders</span>
                    <span class="fw-semibold text-warning">{{ number_format($network->total_orders) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total Revenue</span>
                    <span class="fw-semibold text-success">${{ number_format($network->total_revenue, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Connected Users -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Connected Users</h4>
            </div>
            <div class="card-body">
                @if($network->connections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Connection Name</th>
                                <th>Status</th>
                                <th>Connected At</th>
                                <th>Last Sync</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($network->connections as $connection)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <div class="avatar-xs">
                                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                                    {{ substr($connection->user->name, 0, 1) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $connection->user->name }}</h6>
                                            <small class="text-muted">{{ $connection->user->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $connection->connection_name }}</td>
                                <td>
                                    <span class="badge bg-{{ $connection->is_connected ? 'success' : 'danger' }}-subtle text-{{ $connection->is_connected ? 'success' : 'danger' }}">
                                        {{ $connection->is_connected ? 'Connected' : 'Disconnected' }}
                                    </span>
                                </td>
                                <td>{{ $connection->connected_at ? $connection->connected_at->format('M d, Y') : 'Never' }}</td>
                                <td>
                                    @php
                                        $lastSync = \App\Models\SyncLog::where('user_id', $connection->user_id)
                                            ->where('network_id', $network->id)
                                            ->latest('started_at')
                                            ->first();
                                    @endphp
                                    {{ $lastSync ? $lastSync->started_at->format('M d, Y H:i') : 'Never' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="ti ti-users fs-48 mb-3"></i>
                        <h5>No Connected Users</h5>
                        <p>No users have connected to this network yet.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Monthly Revenue Trend -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Monthly Revenue Trend</h4>
            </div>
            <div class="card-body">
                @if($monthlyRevenue->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Revenue</th>
                                <th>Orders</th>
                                <th>Avg Order Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyRevenue as $month)
                            <tr>
                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $month->month)->format('M Y') }}</td>
                                <td><span class="fw-semibold text-success">${{ number_format($month->revenue, 2) }}</span></td>
                                <td><span class="fw-semibold">{{ number_format($month->orders) }}</span></td>
                                <td><span class="fw-semibold">${{ number_format($month->revenue / max($month->orders, 1), 2) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="ti ti-chart-line fs-48 mb-3"></i>
                        <h5>No Revenue Data</h5>
                        <p>No revenue data available for this network.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Purchases -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Recent Purchases</h4>
            </div>
            <div class="card-body">
                @if($recentPurchases->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Campaign</th>
                                <th>Amount</th>
                                <th>Revenue</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPurchases as $purchase)
                            <tr>
                                <td><code>{{ $purchase->order_id }}</code></td>
                                <td>{{ $purchase->user->name }}</td>
                                <td>{{ $purchase->campaign->name ?? 'N/A' }}</td>
                                <td>${{ number_format($purchase->sales_amount, 2) }}</td>
                                <td><span class="fw-semibold text-success">${{ number_format($purchase->revenue, 2) }}</span></td>
                                <td>{{ $purchase->order_date ? \Carbon\Carbon::parse($purchase->order_date)->format('M d, Y') : 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="ti ti-shopping-cart fs-48 mb-3"></i>
                        <h5>No Recent Purchases</h5>
                        <p>No recent purchases found for this network.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection



