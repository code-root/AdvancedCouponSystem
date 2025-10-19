@extends('dashboard.layouts.vertical', ['title' => 'Coupon Details'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endsection

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Coupons', 'title' => 'Coupon: ' . $coupon->code])

    <!-- Coupon Header -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h3 class="mb-2"><code class="text-primary">{{ $coupon->code }}</code></h3>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="ti ti-speakerphone me-1"></i>{{ $coupon->campaign->name }}
                                </span>
                                <span class="badge bg-info-subtle text-info">
                                    <i class="ti ti-affiliate me-1"></i>{{ $coupon->campaign->network->display_name }}
                                </span>
                                @if($coupon->status === 'active')
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="ti ti-check me-1"></i>Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="ti ti-x me-1"></i>{{ ucfirst($coupon->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('coupons.edit', $coupon->id) }}" class="btn btn-primary">
                                <i class="ti ti-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('coupons.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                    <h3 class="mb-0 fw-bold text-success">${{ number_format($stats['total_revenue'] ?? 0, 2, '.', ',') }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Purchases</h5>
                    <h3 class="mb-0 fw-bold text-primary">{{ number_format($stats['total_orders'] ?? 0, 0, '.', ',') }}</h3>
                    <small class="text-success">{{ $stats['approved_orders'] ?? 0 }} Approved</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Used Count</h5>
                    <h3 class="mb-0 fw-bold text-warning">{{ $stats['total_uses'] ?? 0 }}</h3>
                    @if($stats['remaining_uses'])
                        <small class="text-muted">{{ $stats['remaining_uses'] }} Remaining</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Unique Users</h5>
                    <h3 class="mb-0 fw-bold text-info">{{ $stats['unique_users'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Daily Performance Trend</h4>
                </div>
                <div class="card-body">
                    <div id="dailyPerformanceChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Row -->
    <div class="row">
        <div class="col-lg-8">
            <!-- All Orders -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">All Orders ({{ $coupon->purchases->count() }})</h4>
                </div>
                <div class="card-body">
                    @if($coupon->purchases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="purchasesTable">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Revenue</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($coupon->purchases as $purchase)
                                    <tr>
                                        <td><code>{{ $purchase->order_id ?? 'N/A' }}</code></td>
                                        <td class="text-end">${{ number_format($purchase->sales_amount, 2) }}</td>
                                        <td class="text-end text-success fw-semibold">${{ number_format($purchase->revenue, 2) }}</td>
                                        <td>
                                            @if($purchase->status === 'approved')
                                                <span class="badge bg-success-subtle text-success">Approved</span>
                                            @elseif($purchase->status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">{{ ucfirst($purchase->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $purchase->order_date }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-shopping-cart-off fs-48 text-muted"></i>
                            <p class="text-muted mt-3">No Orders yet for this coupon</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Coupon Info -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Coupon Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Code</label>
                        <p class="mb-0 fw-semibold"><code class="fs-16">{{ $coupon->code }}</code></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Campaign</label>
                        <p class="mb-0">
                            <a href="{{ route('campaigns.show', $coupon->campaign->id) }}" class="text-decoration-none">
                                {{ $coupon->campaign->name }}
                            </a>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Network</label>
                        <p class="mb-0">{{ $coupon->campaign->network->display_name }}</p>
                    </div>
                    @if($coupon->description)
                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <p class="mb-0">{{ $coupon->description }}</p>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <p class="mb-0">
                            @if($coupon->status === 'active')
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($coupon->status) }}</span>
                            @endif
                        </p>
                    </div>
                    @if($coupon->usage_limit)
                    <div class="mb-3">
                        <label class="text-muted small">Usage Limit</label>
                        <p class="mb-0">{{ $coupon->usage_limit }}</p>
                    </div>
                    @endif
                    @if($coupon->expires_at)
                    <div class="mb-3">
                        <label class="text-muted small">Expires At</label>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($coupon->expires_at)->format('M d, Y') }}</p>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="text-muted small">Created</label>
                        <p class="mb-0">{{ $coupon->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0">{{ $coupon->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('orders.index') }}?coupon_id={{ $coupon->id }}" class="btn btn-info">
                            <i class="ti ti-shopping-cart me-1"></i> View All Purchases
                        </a>
                        @if($coupon->status === 'active')
                            <form action="{{ route('coupons.update', $coupon->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="inactive">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="ti ti-ban me-1"></i> Deactivate Coupon
                                </button>
                            </form>
                        @else
                            <form action="{{ route('coupons.update', $coupon->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="ti ti-check me-1"></i> Activate Coupon
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('coupons.destroy', $coupon->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this coupon?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="ti ti-trash me-1"></i> Delete Coupon
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
<script>
let dailyChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadDailyPerformance();
    
    // Initialize DataTable
    if ($('#purchasesTable').length) {
        $('#purchasesTable').DataTable({
            order: [[4, 'desc']], // Sort by date column (descending)
            pageLength: 25,
            language: {
                search: "Search purchases:",
                lengthMenu: "Show _MENU_ purchases",
                info: "Showing _START_ to _END_ of _TOTAL_ purchases",
                infoEmpty: "No Orders available",
                infoFiltered: "(filtered from _MAX_ total purchases)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
});

function loadDailyPerformance() {
    fetch(`{{ route('coupons.daily-stats', $coupon->id) }}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderDailyChart(data.daily_data);
        }
    })
    .catch(error => {
        console.error('Error loading daily performance:', error);
        document.getElementById('dailyPerformanceChart').innerHTML = 
            '<p class="text-center text-danger py-5">Error loading chart</p>';
    });
}

function renderDailyChart(dailyData) {
    const container = document.querySelector("#dailyPerformanceChart");
    
    if (!container) return;
    
    if (!dailyData || dailyData.length === 0) {
        container.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
        return;
    }
    
    if (dailyChart) {
        dailyChart.destroy();
        dailyChart = null;
    }
    
    container.innerHTML = '';
    
    const dates = dailyData.map(d => d.date);
    const revenues = dailyData.map(d => parseFloat(d.revenue || 0));
    const orders = dailyData.map(d => parseInt(d.orders || 0));
    
    const options = {
        series: [{
            name: 'Revenue ($)',
            type: 'line',
            data: revenues
        }, {
            name: 'Orders',
            type: 'line',
            data: orders
        }],
        chart: {
            height: 350,
            type: 'line',
            toolbar: {
                show: true
            }
        },
        colors: ['#6ac75a', '#465dff'],
        stroke: {
            width: [3, 3],
            curve: 'smooth'
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: dates,
            labels: {
                rotate: -45
            }
        },
        yaxis: [{
            title: {
                text: 'Revenue ($)',
                style: {
                    color: '#6ac75a'
                }
            },
            labels: {
                formatter: function(val) {
                    return '$' + val.toFixed(2);
                },
                style: {
                    colors: '#6ac75a'
                }
            }
        }, {
            opposite: true,
            title: {
                text: 'Orders',
                style: {
                    color: '#465dff'
                }
            },
            labels: {
                formatter: function(val) {
                    return Math.floor(val);
                },
                style: {
                    colors: '#465dff'
                }
            }
        }],
        legend: {
            position: 'top',
            horizontalAlign: 'left'
        },
        markers: {
            size: 5,
            hover: {
                size: 7
            }
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: [{
                formatter: function(val) {
                    return '$' + val.toFixed(2);
                }
            }, {
                formatter: function(val) {
                    return val + ' orders';
                }
            }]
        }
    };
    
    dailyChart = new ApexCharts(container, options);
    dailyChart.render();
}
</script>
@endsection


