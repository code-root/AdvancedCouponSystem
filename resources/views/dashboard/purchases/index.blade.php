@extends('layouts.vertical', ['title' => 'Purchases'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Sales', 'title' => 'Purchases'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> New Purchase
                </a>
                <a href="{{ route('purchases.statistics') }}" class="btn btn-info">
                    <i class="ti ti-chart-bar me-1"></i> Statistics
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Purchases</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                <i class="ti ti-shopping-cart"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Confirmed</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-success rounded-circle fs-22">
                                <i class="ti ti-check"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Pending</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                <i class="ti ti-clock"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Revenue</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-info rounded-circle fs-22">
                                <i class="ti ti-coin"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">$0.00</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h4 class="header-title mb-0">All Purchases</h4>
                        <div class="d-flex gap-2">
                            <div class="position-relative">
                                <input type="text" class="form-control ps-4" placeholder="Search Purchase">
                                <i class="ti ti-search position-absolute top-50 translate-middle-y ms-2"></i>
                            </div>
                            <select class="form-select" style="width: auto;">
                                <option>All Status</option>
                                <option>Pending</option>
                                <option>Confirmed</option>
                                <option>Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">Purchase ID</th>
                                <th>Customer</th>
                                <th>Coupon</th>
                                <th>Amount</th>
                                <th>Commission</th>
                                <th>Purchase Date</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="ti ti-shopping-cart-off fs-64 text-muted mb-3"></i>
                                        <h5 class="text-muted mb-3">No Purchases Found</h5>
                                        <p class="text-muted mb-4">Track your affiliate sales and commissions here.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

