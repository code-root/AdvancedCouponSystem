@extends('layouts.vertical', ['title' => 'Purchase Statistics'])

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.css">
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Purchases', 'title' => 'Statistics & Analytics'])

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <input type="text" id="dateRange" class="form-control" placeholder="Select date range">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Networks</label>
                            <select id="networkFilter" class="form-select" multiple="multiple" data-toggle="select2">
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Campaigns</label>
                            <select id="campaignFilter" class="form-select" multiple="multiple" data-toggle="select2">
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Purchase Type</label>
                            <select id="purchaseTypeFilter" class="form-select" data-toggle="select2">
                                <option value="">All Types</option>
                                <option value="coupon">Coupon</option>
                                <option value="link">Direct Link</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" id="applyFilters" class="btn btn-primary w-100">
                                <i class="ti ti-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle text-primary rounded fs-3">
                                <i class="ti ti-shopping-cart"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0" id="totalPurchases">0</h4>
                            <p class="text-muted mb-0">Total Purchases</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="avatar-title bg-success-subtle text-success rounded fs-3">
                                <i class="ti ti-check"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0" id="approvedPurchases">0</h4>
                            <p class="text-muted mb-0">Approved</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="avatar-title bg-info-subtle text-info rounded fs-3">
                                <i class="ti ti-currency-dollar"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0" id="totalRevenue">$0</h4>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle text-warning rounded fs-3">
                                <i class="ti ti-receipt"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0" id="totalOrderValue">$0</h4>
                            <p class="text-muted mb-0">Order Value</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Average Purchase</h5>
                    <h3 class="text-primary mb-0" id="averagePurchase">$0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Average Revenue</h5>
                    <h3 class="text-success mb-0" id="averageRevenue">$0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Pending</h5>
                    <h3 class="text-warning mb-0" id="pendingPurchases">0</h3>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Rejected</h5>
                    <h3 class="text-danger mb-0" id="rejectedPurchases">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Type Breakdown -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Coupon Purchases</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-info mb-0" id="couponPurchases">0</h4>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                        <div class="col-4">
                            <h4 class="text-success mb-0" id="couponRevenue">$0</h4>
                            <p class="text-muted mb-0">Revenue</p>
                        </div>
                        <div class="col-4">
                            <h4 class="text-primary mb-0" id="couponOrderValue">$0</h4>
                            <p class="text-muted mb-0">Order Value</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Direct Link Purchases</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="text-warning mb-0" id="directLinkPurchases">0</h4>
                            <p class="text-muted mb-0">Total</p>
                        </div>
                        <div class="col-4">
                            <h4 class="text-success mb-0" id="directLinkRevenue">$0</h4>
                            <p class="text-muted mb-0">Revenue</p>
                        </div>
                        <div class="col-4">
                            <h4 class="text-primary mb-0" id="directLinkOrderValue">$0</h4>
                            <p class="text-muted mb-0">Order Value</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Daily Trend -->
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Daily Performance Trend (Last 30 Days)</h4>
                </div>
                <div class="card-body">
                    <div id="dailyTrendChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Type Comparison -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Sales by Purchase Type</h4>
                </div>
                <div class="card-body">
                    <div id="purchaseTypeSalesChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Revenue by Purchase Type</h4>
                </div>
                <div class="card-body">
                    <div id="purchaseTypeRevenueChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Network Comparison Charts -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Sales by Network</h4>
                </div>
                <div class="card-body">
                    <div id="networkSalesChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Revenue by Network</h4>
                </div>
                <div class="card-body">
                    <div id="networkRevenueChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Order Value by Network</h4>
                </div>
                <div class="card-body">
                    <div id="networkOrderValueChart" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Stats -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">Monthly Performance (Last 12 Months)</h4>
                </div>
                <div class="card-body">
                    <div id="monthlyStatsChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    
    <script>
        let dailyChart, networkSalesChart, networkRevenueChart, networkOrderChart, monthlyChart;
        
        window.addEventListener('load', function() {
            // Initialize date picker
            flatpickr("#dateRange", {
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: [
                    new Date(new Date().getFullYear(), new Date().getMonth(), 1),
                    new Date()
                ]
            });
            
            // Select2 is automatically initialized by the theme via data-toggle="select2"
            
            // Load initial data
            loadStatistics();
            loadNetworks();
            loadCampaigns();
            
            // Apply filters
            $('#applyFilters').on('click', loadStatistics);
        });
        
        function loadStatistics() {
            const dateRange = $('#dateRange').val().split(' to ');
            const networkIds = $('#networkFilter').val() || [];
            const campaignIds = $('#campaignFilter').val() || [];
            const purchaseType = $('#purchaseTypeFilter').val();
            
            const params = {
                date_from: dateRange[0] || '',
                date_to: dateRange[1] || dateRange[0] || '',
                network_ids: networkIds,
                campaign_ids: campaignIds,
                purchase_type: purchaseType
            };
            
            $.ajax({
                url: '{{ route("orders.statistics-data") }}',
                method: 'GET',
                data: params,
                traditional: true, // Important for array parameters
                success: function(data) {
                    updateSummaryStats(data);
                    renderDailyTrendChart(data.daily_stats);
                    renderMonthlyChart(data.monthly_stats);
                    renderPurchaseTypeCharts(data.purchase_type_breakdown);
                    loadNetworkComparison();
                },
                error: function() {
                    console.error('Error loading statistics');
                }
            });
        }
        
        function updateSummaryStats(data) {
            $('#totalPurchases').text((data.total_orders || 0).toLocaleString('en-US'));
            $('#approvedPurchases').text((data.approved_orders || 0).toLocaleString('en-US'));
            $('#pendingPurchases').text((data.pending_orders || 0).toLocaleString('en-US'));
            $('#rejectedPurchases').text((data.rejected_orders || 0).toLocaleString('en-US'));
            $('#totalRevenue').text('$' + parseFloat(data.total_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#totalOrderValue').text('$' + parseFloat(data.total_sales_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#averagePurchase').text('$' + parseFloat(data.average_purchase || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#averageRevenue').text('$' + parseFloat(data.average_revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Update purchase type breakdown
            if (data.purchase_type_breakdown) {
                $('#couponPurchases').text((data.purchase_type_breakdown.coupon?.count || 0).toLocaleString('en-US'));
                $('#couponRevenue').text('$' + parseFloat(data.purchase_type_breakdown.coupon?.revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#couponOrderValue').text('$' + parseFloat(data.purchase_type_breakdown.coupon?.sales_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                
                $('#directLinkPurchases').text((data.purchase_type_breakdown.link?.count || 0).toLocaleString('en-US'));
                $('#directLinkRevenue').text('$' + parseFloat(data.purchase_type_breakdown.link?.revenue || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#directLinkOrderValue').text('$' + parseFloat(data.purchase_type_breakdown.link?.sales_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }
        }
        
        function renderDailyTrendChart(dailyData) {
            const container = document.querySelector("#dailyTrendChart");
            if (!container || !dailyData || dailyData.length === 0) {
                if (container) container.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
                return;
            }
            
            if (dailyChart) {
                dailyChart.destroy();
                dailyChart = null;
            }
            
            container.innerHTML = '';
            
            const dates = dailyData.map(d => d.date);
            const revenues = dailyData.map(d => parseFloat(d.revenue || 0));
            const orderValues = dailyData.map(d => parseFloat(d.sales_amount || 0));
            const counts = dailyData.map(d => parseInt(d.count || 0));
            
            const options = {
                series: [{
                    name: 'Revenue ($)',
                    type: 'line',
                    data: revenues
                }, {
                    name: 'Order Value ($)',
                    type: 'line',
                    data: orderValues
                }, {
                    name: 'Purchases',
                    type: 'column',
                    data: counts
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: { show: true }
                },
                colors: ['#6ac75a', '#465dff', '#ffc107'],
                stroke: {
                    width: [3, 3, 0],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        columnWidth: '50%'
                    }
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
                        text: 'Amount ($)'
                    },
                    labels: {
                        formatter: function(val) {
                            return '$' + val.toFixed(2);
                        }
                    }
                }, {
                    opposite: true,
                    title: {
                        text: 'Purchases'
                    },
                    labels: {
                        formatter: function(val) {
                            return Math.floor(val);
                        }
                    }
                }],
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                tooltip: {
                    shared: true,
                    intersect: false
                }
            };
            
            dailyChart = new ApexCharts(container, options);
            dailyChart.render();
        }
        
        function renderMonthlyChart(monthlyData) {
            const container = document.querySelector("#monthlyStatsChart");
            if (!container || !monthlyData || monthlyData.length === 0) {
                if (container) container.innerHTML = '<p class="text-center text-muted py-5">No data available</p>';
                return;
            }
            
            if (monthlyChart) {
                monthlyChart.destroy();
                monthlyChart = null;
            }
            
            container.innerHTML = '';
            
            const months = monthlyData.map(d => d.month);
            const revenues = monthlyData.map(d => parseFloat(d.revenue || 0));
            const orderValues = monthlyData.map(d => parseFloat(d.sales_amount || 0));
            const revenues = monthlyData.map(d => parseFloat(d.revenue || 0));
            
            const options = {
                series: [{
                    name: 'Revenue',
                    data: revenues
                }, {
                    name: 'Order Value',
                    data: orderValues
                }, {
                    name: 'revenue',
                    data: revenues
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: { show: true }
                },
                colors: ['#6ac75a', '#465dff', '#ffc107'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: months
                },
                yaxis: {
                    title: {
                        text: 'Amount ($)'
                    },
                    labels: {
                        formatter: function(val) {
                            return '$' + val.toFixed(0);
                        }
                    }
                },
                legend: {
                    position: 'top'
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return '$' + val.toFixed(2);
                        }
                    }
                }
            };
            
            monthlyChart = new ApexCharts(container, options);
            monthlyChart.render();
        }
        
        function renderPurchaseTypeCharts(breakdown) {
            if (!breakdown) return;
            
            // Sales by Purchase Type
            const salesContainer = document.querySelector("#purchaseTypeSalesChart");
            if (salesContainer) {
                const salesData = [
                    breakdown.coupon?.count || 0,
                    breakdown.link?.count || 0
                ];
                
                const salesOptions = {
                    series: salesData,
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: ['Coupon', 'Direct Link'],
                    colors: ['#6ac75a', '#ffc107'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.config.series[opts.seriesIndex];
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + ' sales';
                            }
                        }
                    }
                };
                
                new ApexCharts(salesContainer, salesOptions).render();
            }
            
            // Revenue by Purchase Type
            const revenueContainer = document.querySelector("#purchaseTypeRevenueChart");
            if (revenueContainer) {
                const revenueData = [
                    parseFloat(breakdown.coupon?.revenue || 0),
                    parseFloat(breakdown.link?.revenue || 0)
                ];
                
                const revenueOptions = {
                    series: revenueData,
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: ['Coupon', 'Direct Link'],
                    colors: ['#6ac75a', '#ffc107'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return '$' + val.toFixed(1);
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return '$' + val.toFixed(2);
                            }
                        }
                    }
                };
                
                new ApexCharts(revenueContainer, revenueOptions).render();
            }
        }
        
        function loadNetworkComparison() {
            $.ajax({
                url: '{{ route("orders.network-comparison") }}',
                method: 'GET',
                success: function(data) {
                    renderNetworkSalesChart(data);
                    renderNetworkRevenueChart(data);
                    renderNetworkOrderValueChart(data);
                },
                error: function() {
                    console.error('Error loading network comparison');
                }
            });
        }
        
        function renderNetworkSalesChart(data) {
            const container = document.querySelector("#networkSalesChart");
            if (!container) return;
            
            if (networkSalesChart) networkSalesChart.destroy();
            container.innerHTML = '';
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-center text-muted py-5">No connected networks</p>';
                return;
            }
            
            // Filter out networks with 0 sales for cleaner chart
            const dataWithSales = data.filter(n => parseInt(n.count) > 0);
            
            if (dataWithSales.length === 0) {
                container.innerHTML = '<p class="text-center text-muted py-5">No sales data available</p>';
                return;
            }
            
            const options = {
                series: dataWithSales.map(n => parseInt(n.count)),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: dataWithSales.map(n => n.network_name),
                colors: ['#6ac75a', '#465dff', '#ffc107', '#dc3545', '#20c997', '#17a2b8', '#6f42c1'],
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        return opts.w.config.series[opts.seriesIndex];
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val + ' sales';
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            
            networkSalesChart = new ApexCharts(container, options);
            networkSalesChart.render();
        }
        
        function renderNetworkRevenueChart(data) {
            const container = document.querySelector("#networkRevenueChart");
            if (!container) return;
            
            if (networkRevenueChart) networkRevenueChart.destroy();
            container.innerHTML = '';
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-center text-muted py-5">No connected networks</p>';
                return;
            }
            
            // Filter out networks with 0 revenue for cleaner chart
            const dataWithRevenue = data.filter(n => parseFloat(n.revenue) > 0);
            
            if (dataWithRevenue.length === 0) {
                container.innerHTML = '<p class="text-center text-muted py-5">No revenue data available</p>';
                return;
            }
            
            const options = {
                series: dataWithRevenue.map(n => parseFloat(n.revenue)),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: dataWithRevenue.map(n => n.network_name),
                colors: ['#6ac75a', '#465dff', '#ffc107', '#dc3545', '#20c997', '#17a2b8', '#6f42c1'],
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return '$' + val.toFixed(1);
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return '$' + val.toFixed(2);
                        }
                    }
                }
            };
            
            networkRevenueChart = new ApexCharts(container, options);
            networkRevenueChart.render();
        }
        
        function renderNetworkOrderValueChart(data) {
            const container = document.querySelector("#networkOrderValueChart");
            if (!container) return;
            
            if (networkOrderChart) networkOrderChart.destroy();
            container.innerHTML = '';
            
            if (!data || data.length === 0) {
                container.innerHTML = '<p class="text-center text-muted py-5">No connected networks</p>';
                return;
            }
            
            // Filter out networks with 0 order value for cleaner chart
            const dataWithOrders = data.filter(n => parseFloat(n.sales_amount) > 0);
            
            if (dataWithOrders.length === 0) {
                container.innerHTML = '<p class="text-center text-muted py-5">No order data available</p>';
                return;
            }
            
            const options = {
                series: dataWithOrders.map(n => parseFloat(n.sales_amount)),
                chart: {
                    type: 'donut',
                    height: 300
                },
                labels: dataWithOrders.map(n => n.network_name),
                colors: ['#6ac75a', '#465dff', '#ffc107', '#dc3545', '#20c997', '#17a2b8', '#6f42c1'],
                legend: {
                    position: 'bottom'
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return '$' + val.toFixed(1);
                    }
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return '$' + val.toFixed(2);
                        }
                    }
                }
            };
            
            networkOrderChart = new ApexCharts(container, options);
            networkOrderChart.render();
        }
        
        function loadNetworks() {
            // Load connected networks for filter
            $.ajax({
                url: '{{ route("orders.network-comparison") }}',
                method: 'GET',
                success: function(networks) {
                    $('#networkFilter').empty();
                    if (networks && networks.length > 0) {
                        networks.forEach(network => {
                            $('#networkFilter').append(
                                `<option value="${network.id}">${network.network_name}</option>`
                            );
                        });
                    }
                },
                error: function() {
                    console.error('Error loading networks');
                }
            });
        }
        
        function loadCampaigns() {
            // Load all campaigns for the user
            $.ajax({
                url: '{{ route("campaigns.index") }}',
                method: 'GET',
                data: { ajax: true },
                success: function(response) {
                    $('#campaignFilter').empty();
                    const campaigns = response.campaigns || response.data || [];
                    if (campaigns.length > 0) {
                        campaigns.forEach(campaign => {
                            $('#campaignFilter').append(
                                `<option value="${campaign.id}">${campaign.name}</option>`
                            );
                        });
                    }
                },
                error: function() {
                    console.error('Error loading campaigns');
                }
            });
        }
    </script>
@endsection

