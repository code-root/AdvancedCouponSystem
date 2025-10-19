@extends('dashboard.layouts.vertical', ['title' => 'Network Details'])

@section('content')
    @include('dashboard.layouts.partials.page-title', ['subtitle' => 'Networks', 'title' => 'Network Details'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('networks.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>
                <a href="{{ route('networks.create') }}" class="btn btn-primary">
                    <i class="ti ti-plug-connected me-1"></i> Connect This Network
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar-xl bg-primary-subtle mx-auto mb-3">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-42">
                            <i class="ti ti-building-store"></i>
                        </span>
                    </div>
                    
                    <h4 class="mb-1">{{ $network->display_name }}</h4>
                    <p class="text-muted mb-3">{{ $network->name }}</p>
                    
                    <div class="flex-grow-1 d-inline-flex align-items-center fs-18 mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <span class="ti ti-star-filled text-warning"></span>
                        @endfor
                        <span class="ms-1 fs-14">5.0 Rating</span>
                    </div>

                    <div class="d-flex justify-content-center gap-2">
                        @if($network->is_active)
                            <span class="badge bg-success-subtle text-success">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        @endif
                        @if($network->is_connected)
                            <span class="badge bg-primary-subtle text-primary">Connected</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h5 class="card-title mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="border border-end-0 border-start-0 border-dashed p-2 mx-n3">
                        <div class="row text-center g-2">
                            <div class="col-6 border-end">
                                <h5 class="mb-1">{{ count($network->supported_features ?? []) }}</h5>
                                <p class="text-muted mb-0 fs-13">Features</p>
                            </div>
                            <div class="col-6">
                                <h5 class="mb-1">{{ $network->connections()->count() }}</h5>
                                <p class="text-muted mb-0 fs-13">Connections</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Network Information</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1">Display Name</label>
                            <p class="fw-semibold mb-0">{{ $network->display_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1">System Name</label>
                            <p class="fw-semibold mb-0">{{ $network->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1">API URL</label>
                            <p class="fw-semibold mb-0">{{ $network->api_url }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted mb-1">Status</label>
                            <p class="fw-semibold mb-0">
                                @if($network->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="text-muted mb-1">Description</label>
                            <p class="mb-0">{{ $network->description }}</p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="text-muted mb-1">API URL</label>
                            <p class="mb-0"><code>{{ $network->api_url }}</code></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="card-title mb-0">Features & Services</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if($network->features && is_array($network->features))
                            @foreach($network->features as $feature)
                                <span class="badge bg-primary-subtle text-primary p-2">
                                    <i class="ti ti-check me-1"></i>{{ $feature }}
                                </span>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">No features listed</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
