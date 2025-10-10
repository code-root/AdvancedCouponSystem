@extends('layouts.vertical', ['title' => 'Campaigns'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Marketing', 'title' => 'Campaigns'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end">
                <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Create Campaign
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-3">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Campaigns</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-primary rounded-circle fs-22">
                                <i class="ti ti-speakerphone"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">All campaigns</span>
                    </p>
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
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Running now</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Scheduled</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-warning rounded-circle fs-22">
                                <i class="ti ti-clock"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Coming soon</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Completed</h5>
                    <div class="d-flex align-items-center justify-content-center gap-2 my-2 py-1">
                        <div class="user-img fs-42 flex-shrink-0">
                            <span class="avatar-title text-bg-info rounded-circle fs-22">
                                <i class="ti ti-checks"></i>
                            </span>
                        </div>
                        <h3 class="mb-0 fw-bold">0</h3>
                    </div>
                    <p class="mb-0 text-muted">
                        <span class="text-nowrap">Finished</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h4 class="header-title mb-0">All Campaigns</h4>
                        <div class="position-relative">
                            <input type="text" class="form-control ps-4" placeholder="Search Campaign">
                            <i class="ti ti-search position-absolute top-50 translate-middle-y ms-2"></i>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">Campaign Name</th>
                                <th>Network</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Budget</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="ti ti-speakerphone fs-64 text-muted mb-3"></i>
                                        <h5 class="text-muted mb-3">No Campaigns Found</h5>
                                        <p class="text-muted mb-4">Create your first marketing campaign to start promoting your affiliate links.</p>
                                        <a href="{{ route('campaigns.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i> Create Your First Campaign
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

