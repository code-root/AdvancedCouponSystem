@extends('dashboard.layouts.vertical', ['title' => 'Countries'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Configuration', 'title' => 'Countries'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end">
                <a href="{{ route('countries.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Add Country
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom border-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h4 class="header-title mb-0">All Countries</h4>
                        <div class="position-relative">
                            <input type="text" class="form-control ps-4" placeholder="Search Country">
                            <i class="ti ti-search position-absolute top-50 translate-middle-y ms-2"></i>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover text-nowrap mb-0">
                        <thead class="bg-light-subtle">
                            <tr>
                                <th class="ps-3">Country</th>
                                <th>Code</th>
                                <th>Active Networks</th>
                                <th>Total Campaigns</th>
                                <th>Status</th>
                                <th class="text-center pe-3" style="width: 120px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="ti ti-world-off fs-64 text-muted mb-3"></i>
                                        <h5 class="text-muted mb-3">No Countries Configured</h5>
                                        <p class="text-muted mb-4">Add countries to organize your brokers by location.</p>
                                        <a href="{{ route('countries.create') }}" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i> Add Your First Country
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

