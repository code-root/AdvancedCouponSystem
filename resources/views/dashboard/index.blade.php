@extends('layouts.vertical',['title' => 'Dashboard'])

@section('css')
    @vite(['node_modules/flatpickr/dist/flatpickr.min.css'])
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Welcome back, {{ auth()->user()->name }} 👋</h4>
            </div>
            <div class="mt-3 mt-sm-0">
                <form action="javascript:void(0);">
                    <div class="row g-2 mb-0 align-items-center">
                        <div class="col-auto">
                            <a href="{{ route('networks.create') }}" class="btn btn-primary">
                                <i class="ti ti-plug-connected me-1"></i> Connect Network
                            </a>
                        </div>
                        <!--end col-->
                        <div class="col-sm-auto">
                            <div class="input-group">
                                <input type="text" class="form-control border-0 shadow"
                                    data-provider="flatpickr" data-deafult-date="01 Jan to 31 Dec"
                                    data-date-format="d M" data-range-date="true">
                                <span class="input-group-text bg-primary border-primary text-white">
                                    <i class="ti ti-calendar fs-15"></i>
                                </span>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </form>
            </div>
        </div><!-- end card header -->
    </div>
    <!--end col-->
</div> <!-- end row-->

<div class="row">
    <div class="col">
        <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-muted fs-13 text-uppercase" title="Total Networks">Connected Networks</h5>
                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                            <div class="user-img fs-42 flex-shrink-0">
                                <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                    <i class="ti ti-affiliate"></i>
                                </span>
                            </div>
                            <h3 class="mb-0 fw-bold">{{ auth()->user()->getActiveNetworkConnectionsCount() ?? 0 }}</h3>
                        </div>
                        <p class="mb-0 text-muted">
                            <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i>Active</span>
                            <span class="text-nowrap">Network connections</span>
                        </p>
                    </div>
                </div>
            </div><!-- end col -->

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-muted fs-13 text-uppercase" title="Total Campaigns">Active Campaigns</h5>
                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                            <div class="user-img fs-42 flex-shrink-0">
                                <span class="avatar-title text-bg-success rounded-circle fs-22">
                                    <i class="ti ti-speakerphone"></i>
                                </span>
                            </div>
                            <h3 class="mb-0 fw-bold">0</h3>
                        </div>
                        <p class="mb-0 text-muted">
                            <span class="text-primary me-2"><i class="ti ti-minus"></i>Running</span>
                            <span class="text-nowrap">Marketing campaigns</span>
                        </p>
                    </div>
                </div>
            </div><!-- end col -->

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-muted fs-13 text-uppercase" title="Total Coupons">Total Coupons</h5>
                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                            <div class="user-img fs-42 flex-shrink-0">
                                <span class="avatar-title text-bg-info rounded-circle fs-22">
                                    <i class="ti ti-ticket"></i>
                                </span>
                            </div>
                            <h3 class="mb-0 fw-bold">0</h3>
                        </div>
                        <p class="mb-0 text-muted">
                            <span class="text-warning me-2"><i class="ti ti-point-filled"></i>Generated</span>
                            <span class="text-nowrap">Coupon codes</span>
                        </p>
                    </div>
                </div>
            </div><!-- end col -->

            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="text-muted fs-13 text-uppercase" title="Total Revenue">Total Revenue</h5>
                        <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                            <div class="user-img fs-42 flex-shrink-0">
                                <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                    <i class="ti ti-coin"></i>
                                </span>
                            </div>
                            <h3 class="mb-0 fw-bold">$0.00</h3>
                        </div>
                        <p class="mb-0 text-muted">
                            <span class="text-success me-2"><i class="ti ti-caret-up-filled"></i>0%</span>
                            <span class="text-nowrap">Since last month</span>
                        </p>
                    </div>
                </div>
            </div><!-- end col -->
        </div><!-- end row -->

        <div class="row">
            <div class="col-xxl-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="header-title">Revenue Overview</h4>
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle drop-arrow-none card-drop"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:void(0);" class="dropdown-item">Sales Report</a>
                                <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                                <a href="javascript:void(0);" class="dropdown-item">Statistics</a>
                            </div>
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
                                    <span class="ti ti-square-rounded-arrow-up text-danger me-1"></span>
                                    <span>$0</span>
                                </h4>
                            </div>
                            <div class="col-md-3 col-6">
                                <p class="text-muted mt-3 mb-1">Active Coupons</p>
                                <h4 class="mb-3">
                                    <span class="ti ti-ticket me-1"></span>
                                    <span>0</span>
                                </h4>
                            </div>
                            <div class="col-md-3 col-6">
                                <p class="text-muted mt-3 mb-1">Conversions</p>
                                <h4 class="mb-3">
                                    <span class="ti ti-chart-line me-1"></span>
                                    <span>0%</span>
                                </h4>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <div dir="ltr">
                            <div id="revenue-chart" class="apex-charts" data-colors="#6ac75a,#465dff,#783bff,#f7577e"></div>
                        </div>
                    </div>
                </div> <!-- end card-->
            </div> <!-- end col-->

            <div class="col-xxl-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center border-bottom border-dashed">
                        <h4 class="header-title">Top Performing Networks</h4>
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle drop-arrow-none card-drop p-0"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-dots-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="javascript:void(0);" class="dropdown-item">View All</a>
                                <a href="javascript:void(0);" class="dropdown-item">Export Report</a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="text-center py-5">
                            <i class="ti ti-affiliate fs-48 text-muted"></i>
                            <p class="text-muted mt-3 mb-2">No brokers connected yet</p>
                            <a href="{{ route('networks.create') }}" class="btn btn-sm btn-primary">
                                <i class="ti ti-plug-connected me-1"></i> Connect Your First Network
                            </a>
                        </div>
                    </div>
                </div> <!-- end card-->
            </div> <!-- end col-->
        </div> <!-- end row-->

    </div> <!-- end col-->

    <div class="col-auto info-sidebar d-none d-xxl-block">
        <div class="alert alert-primary d-flex align-items-center">
            <i class="ti ti-help fs-24 me-1"></i> <b>Help line:</b> <span class="fw-medium ms-1">+(012) 123 456 78</span>
        </div>

        <div class="card bg-primary">
            <div class="card-body"
                style="background-image: url(/images/png/arrows.svg); background-size: contain; background-repeat: no-repeat; background-position: right bottom;">
                <h1><i class="ti ti-rocket text-white"></i></h1>
                <h4 class="text-white">Get Started with Affiliate Marketing</h4>
                <p class="text-white text-opacity-75">Connect your first broker and start earning commissions today!</p>
                <a href="{{ route('networks.create') }}" class="btn btn-sm rounded-pill btn-info">Connect Now</a>
            </div> <!-- end card-body-->
        </div> <!-- end card-->

        <div class="card">
            <div class="card-body">
                <div class="d-flex mb-3 justify-content-between align-items-center">
                    <h4 class="header-title">Quick Actions:</h4>
                </div>
                
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('networks.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-affiliate me-1"></i> Manage Networks
                    </a>
                    <a href="{{ route('campaigns.index') }}" class="btn btn-outline-success btn-sm">
                        <i class="ti ti-speakerphone me-1"></i> View Campaigns
                    </a>
                    <a href="{{ route('coupons.index') }}" class="btn btn-outline-info btn-sm">
                        <i class="ti ti-ticket me-1"></i> Generate Coupons
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-warning btn-sm">
                        <i class="ti ti-report me-1"></i> View Reports
                    </a>
                </div>

                <div class="mt-4 pt-3 border-top border-dashed">
                    <h4 class="header-title mb-3">Recent Activity:</h4>
                    <div class="text-center py-4">
                        <i class="ti ti-activity fs-48 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No recent activity</p>
                    </div>
                </div>
            </div>
        </div> <!-- end card-->
    </div> <!-- end col-->
</div> <!-- end row-->
@endsection

@section('scripts')
    @vite(['resources/js/pages/dashboard-sales.js'])
@endsection
