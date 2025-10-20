@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Site Settings Dashboard</h4>
                <p class="text-muted mb-0">Overview of all site settings and configurations</p>
            </div>
            <div class="mt-3 mt-sm-0">
                <a href="{{ route('admin.settings.index') }}" class="btn btn-primary">
                    <i class="ti ti-settings me-1"></i> Manage Settings
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Settings</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $totalSettings }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Active Settings</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $activeSettings }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Recent Changes</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $recentChangesCount }}</h3>
                <small class="text-muted">Last 7 days</small>
            </div>
        </div>
    </div>
    
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Languages</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $languagesCount }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Changes -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Recent Changes</h4>
            </div>
            <div class="card-body">
                @if($recentChanges->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Setting</th>
                                    <th>Group</th>
                                    <th>Modified</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentChanges as $setting)
                                <tr>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $setting->key }}</h6>
                                            <small class="text-muted">{{ Str::limit($setting->value, 50) }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $setting->group ?? 'General' }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $setting->last_modified_at ? $setting->last_modified_at->diffForHumans() : 'N/A' }}</small>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="ti ti-settings-off fs-48 mb-3"></i>
                        <p>No recent changes found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Settings by Group -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Settings by Group</h4>
            </div>
            <div class="card-body">
                @if($settingsByGroup->count() > 0)
                    <div class="row">
                        @foreach($settingsByGroup as $group => $settings)
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <h6 class="mb-2">{{ $group ?: 'General' }}</h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">{{ $settings->count() }} settings</span>
                                    <span class="badge bg-primary">{{ $settings->where('is_active', true)->count() }} active</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="ti ti-settings-off fs-48 mb-3"></i>
                        <p>No settings found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Quick Actions</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.settings.general.index') }}" class="btn btn-outline-primary w-100">
                            <i class="ti ti-settings me-2"></i>
                            General Settings
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.settings.branding.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="ti ti-palette me-2"></i>
                            Branding
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.settings.smtp.index') }}" class="btn btn-outline-info w-100">
                            <i class="ti ti-mail me-2"></i>
                            SMTP Settings
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.settings.seo.index') }}" class="btn btn-outline-success w-100">
                            <i class="ti ti-search me-2"></i>
                            SEO Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh statistics every 30 seconds
    setInterval(function() {
        // You can add AJAX refresh logic here if needed
    }, 30000);
});
</script>
@endpush


