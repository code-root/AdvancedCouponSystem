@extends('layouts.vertical', ['title' => 'Reports'])

@section('css')
    @vite(['node_modules/flatpickr/dist/flatpickr.min.css'])
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Analytics', 'title' => 'Reports'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end gap-2">
                <div class="input-group" style="width: auto;">
                    <input type="text" class="form-control" data-provider="flatpickr" 
                           data-deafult-date="01 Jan to 31 Dec" data-date-format="d M Y" 
                           data-range-date="true" placeholder="Select date range">
                    <span class="input-group-text bg-primary border-primary text-white">
                        <i class="ti ti-calendar fs-15"></i>
                    </span>
                </div>
                <button type="button" class="btn btn-success">
                    <i class="ti ti-download me-1"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                <i class="ti ti-coin"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">$0.00</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i>0%</span>
                        <span class="text-nowrap">vs last month</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Sales</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-success rounded-circle fs-22">
                                <i class="ti ti-shopping-cart"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Confirmed purchases</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Conversion Rate</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                <i class="ti ti-chart-line"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0%</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Average rate</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Active Campaigns</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-info rounded-circle fs-22">
                                <i class="ti ti-speakerphone"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Running now</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="header-title">Revenue Overview</h4>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-light btn-sm">All</button>
                        <button type="button" class="btn btn-light active btn-sm">1M</button>
                        <button type="button" class="btn btn-light btn-sm">6M</button>
                        <button type="button" class="btn btn-light btn-sm">1Y</button>
                    </div>
                </div>

                <div class="bg-light bg-opacity-50">
                    <div class="row text-center">
                        <div class="col-md-3 col-6">
                            <p class="text-muted mt-3 mb-1">Total Sales</p>
                            <h4 class="mb-3">
                                <span class="ti ti-square-rounded-arrow-down text-success me-1"></span>
                                <span>$0</span>
                            </h4>
                        </div>
                        <div class="col-md-3 col-6">
                            <p class="text-muted mt-3 mb-1">Commissions</p>
                            <h4 class="mb-3">
                                <span class="ti ti-coin me-1"></span>
                                <span>$0</span>
                            </h4>
                        </div>
                        <div class="col-md-3 col-6">
                            <p class="text-muted mt-3 mb-1">Coupons Used</p>
                            <h4 class="mb-3">
                                <span class="ti ti-ticket me-1"></span>
                                <span>0</span>
                            </h4>
                        </div>
                        <div class="col-md-3 col-6">
                            <p class="text-muted mt-3 mb-1">Avg. Order Value</p>
                            <h4 class="mb-3">
                                <span class="ti ti-chart-infographic me-1"></span>
                                <span>$0</span>
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-0">
                    <div dir="ltr">
                        <div id="revenue-chart" class="apex-charts" data-colors="#6ac75a,#465dff,#783bff,#f7577e"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Top Performing Campaigns</h4>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="ti ti-speakerphone fs-48 text-muted"></i>
                        <p class="text-muted mt-3 mb-0">No campaign data available</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Top Performing Brokers</h4>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="ti ti-affiliate fs-48 text-muted"></i>
                        <p class="text-muted mt-3 mb-0">No broker data available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/pages/dashboard-sales.js'])
@endsection

