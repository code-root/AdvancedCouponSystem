@extends('layouts.vertical', ['title' => 'Create Sync Schedule'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Data Sync', 'title' => 'Create New Schedule'])

    <form action="{{ route('sync.schedules.store') }}" method="POST">
        @csrf
        
        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Basic Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Schedule Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   name="name" value="{{ old('name') }}" 
                                   placeholder="e.g., Daily Morning Sync" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Networks <span class="text-danger">*</span></label>
                            <select class="form-select @error('network_ids') is-invalid @enderror" 
                                    name="network_ids[]" id="networkSelect" multiple="multiple" 
                                    data-toggle="select2" required>
                                @foreach($networks as $network)
                                    <option value="{{ $network->id }}" 
                                        {{ in_array($network->id, old('network_ids', [])) ? 'selected' : '' }}>
                                        {{ $network->display_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('network_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select one or more networks to sync data from</small>
                        </div>

                        <div class="mb-0">
                            <label class="form-label">Sync Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('sync_type') is-invalid @enderror" 
                                    name="sync_type" required>
                                <option value="all" {{ old('sync_type') == 'all' ? 'selected' : '' }}>
                                    All (Campaigns, Coupons & Purchases)
                                </option>
                                <option value="campaigns" {{ old('sync_type') == 'campaigns' ? 'selected' : '' }}>
                                    Campaigns Only
                                </option>
                                <option value="coupons" {{ old('sync_type') == 'coupons' ? 'selected' : '' }}>
                                    Coupons Only
                                </option>
                                <option value="purchases" {{ old('sync_type') == 'purchases' ? 'selected' : '' }}>
                                    Purchases Only
                                </option>
                            </select>
                            @error('sync_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Schedule Settings -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Schedule Settings</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Interval <span class="text-danger">*</span></label>
                                <select class="form-select @error('interval_minutes') is-invalid @enderror" 
                                        name="interval_minutes" required>
                                    <option value="10" {{ old('interval_minutes') == 10 ? 'selected' : '' }}>Every 10 Minutes</option>
                                    <option value="30" {{ old('interval_minutes') == 30 ? 'selected' : '' }}>Every 30 Minutes</option>
                                    <option value="60" {{ old('interval_minutes', 60) == 60 ? 'selected' : '' }}>Every Hour</option>
                                    <option value="120" {{ old('interval_minutes') == 120 ? 'selected' : '' }}>Every 2 Hours</option>
                                    <option value="360" {{ old('interval_minutes') == 360 ? 'selected' : '' }}>Every 6 Hours</option>
                                    <option value="720" {{ old('interval_minutes') == 720 ? 'selected' : '' }}>Every 12 Hours</option>
                                    <option value="1440" {{ old('interval_minutes') == 1440 ? 'selected' : '' }}>Daily (24 Hours)</option>
                                </select>
                                @error('interval_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Runs Per Day <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('max_runs_per_day') is-invalid @enderror" 
                                       name="max_runs_per_day" value="{{ old('max_runs_per_day', 24) }}" 
                                       min="1" max="1440" required>
                                @error('max_runs_per_day')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maximum number of times this schedule can run in a day</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date Range <span class="text-danger">*</span></label>
                            <select class="form-select @error('date_range_type') is-invalid @enderror" 
                                    name="date_range_type" id="dateRangeType" required>
                                <option value="today" {{ old('date_range_type', 'today') == 'today' ? 'selected' : '' }}>
                                    Today
                                </option>
                                <option value="yesterday" {{ old('date_range_type') == 'yesterday' ? 'selected' : '' }}>
                                    Yesterday
                                </option>
                                <option value="last_7_days" {{ old('date_range_type') == 'last_7_days' ? 'selected' : '' }}>
                                    Last 7 Days
                                </option>
                                <option value="last_30_days" {{ old('date_range_type') == 'last_30_days' ? 'selected' : '' }}>
                                    Last 30 Days
                                </option>
                                <option value="current_month" {{ old('date_range_type') == 'current_month' ? 'selected' : '' }}>
                                    Current Month
                                </option>
                                <option value="previous_month" {{ old('date_range_type') == 'previous_month' ? 'selected' : '' }}>
                                    Previous Month
                                </option>
                                <option value="custom" {{ old('date_range_type') == 'custom' ? 'selected' : '' }}>
                                    Custom Range
                                </option>
                            </select>
                            @error('date_range_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="customDateRange" class="row" style="display: {{ old('date_range_type') == 'custom' ? 'flex' : 'none' }};">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-control @error('custom_date_from') is-invalid @enderror" 
                                       name="custom_date_from" value="{{ old('custom_date_from') }}">
                                @error('custom_date_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-control @error('custom_date_to') is-invalid @enderror" 
                                       name="custom_date_to" value="{{ old('custom_date_to') }}">
                                @error('custom_date_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Status -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Status</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" 
                                   name="is_active" id="isActive" value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">
                                Activate schedule immediately
                            </label>
                        </div>
                        <small class="text-muted">If enabled, the schedule will start running automatically</small>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Quick Guide</h4>
                    </div>
                    <div class="card-body">
                        <ul class="ps-3 mb-0">
                            <li class="mb-2"><small>Select one or more networks to sync</small></li>
                            <li class="mb-2"><small>Choose what data to sync (all or specific type)</small></li>
                            <li class="mb-2"><small>Set interval between syncs</small></li>
                            <li class="mb-2"><small>Limit maximum runs per day</small></li>
                            <li class="mb-0"><small>Choose date range for data fetching</small></li>
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Create Schedule
                            </button>
                            <a href="{{ route('sync.schedules.index') }}" class="btn btn-light">
                                <i class="ti ti-arrow-left me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateRangeType = document.getElementById('dateRangeType');
    const customDateRange = document.getElementById('customDateRange');

    dateRangeType.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateRange.style.display = 'flex';
        } else {
            customDateRange.style.display = 'none';
        }
    });
});
</script>
@endsection

