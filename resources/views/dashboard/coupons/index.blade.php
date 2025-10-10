@extends('layouts.vertical', ['title' => 'Coupons'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Marketing', 'title' => 'Coupons'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('coupons.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Create Coupon
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkGenerateModal">
                    <i class="ti ti-layers-linked me-1"></i> Bulk Generate
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 text-center">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Coupons</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                <i class="ti ti-ticket"></i>
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
                    <h5 class="text-muted fs-13 text-uppercase">Active</h5>
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
                    <h5 class="text-muted fs-13 text-uppercase">Used</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                <i class="ti ti-discount-check"></i>
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
                    <h5 class="text-muted fs-13 text-uppercase">Expired</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-danger rounded-circle fs-22">
                                <i class="ti ti-clock-off"></i>
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
                    <h5 class="text-muted fs-13 text-uppercase">Usage Rate</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-info rounded-circle fs-22">
                                <i class="ti ti-chart-line"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0%</h3>
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
                        <h4 class="header-title mb-0">All Coupons</h4>
                        <div class="d-flex gap-2">
                            <div class="position-relative">
                                <input type="text" class="form-control ps-4" placeholder="Search Coupon">
                                <i class="ti ti-search position-absolute top-50 translate-middle-y ms-2"></i>
                            </div>
                            <select class="form-select" style="width: auto;">
                                <option>All Status</option>
                                <option>Active</option>
                                <option>Used</option>
                                <option>Expired</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">Coupon Code</th>
                                <th>Campaign</th>
                                <th>Discount</th>
                                <th>Usage</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="ti ti-ticket-off fs-64 text-muted mb-3"></i>
                                        <h5 class="text-muted mb-3">No Coupons Found</h5>
                                        <p class="text-muted mb-4">Create coupon codes to boost your affiliate sales.</p>
                                        <a href="{{ route('coupons.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i> Create Your First Coupon
                                        </a>
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

