@extends('dashboard.layouts.main')

@section('title', 'Edit Broker')

@section('content')
    <div class="pc-container">
        <div class="pc-content">
            <!-- [breadcrumb] start -->
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <ul class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                        <i class="ti ti-home me-1"></i>Dashboard
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('brokers.index') }}" class="text-decoration-none">Brokers</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Edit Broker</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header Section -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1 fw-bold">Edit Broker: {{ $broker->name }}</h4>
                        <p class="text-muted mb-0">Update broker information</p>
                    </div>
                    <a href="{{ route('brokers.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Broker Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('brokers.update', $broker) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Broker Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-building-store text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control" name="name" 
                                                   value="{{ old('name', $broker->name) }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Broker Code <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-barcode text-muted"></i>
                                            </span>
                                            <input type="text" class="form-control" name="code" 
                                                   value="{{ old('code', $broker->code) }}" required readonly>
                                        </div>
                                        <small class="text-muted">Broker code cannot be changed</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">API URL</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-world text-muted"></i>
                                            </span>
                                            <input type="url" class="form-control" name="api_url" 
                                                   value="{{ old('api_url', $broker->api_url) }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">API Key</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-key text-muted"></i>
                                            </span>
                                            <input type="password" class="form-control" name="api_key" 
                                                   placeholder="Leave empty to keep current">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Country</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-flag text-muted"></i>
                                            </span>
                                            <select class="form-select" name="country_id">
                                                <option value="">Select Country</option>
                                                @foreach(\App\Models\Country::all() as $country)
                                                    <option value="{{ $country->id }}" 
                                                            {{ old('country_id', $broker->country_id) == $country->id ? 'selected' : '' }}>
                                                        {{ $country->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="ti ti-toggle-left text-muted"></i>
                                            </span>
                                            <select class="form-select" name="is_active">
                                                <option value="1" {{ old('is_active', $broker->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ old('is_active', $broker->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="4">{{ old('description', $broker->description) }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Update Broker
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="ti ti-refresh me-1"></i> Reset
                                    </button>
                                    <a href="{{ route('brokers.index') }}" class="btn btn-outline-danger">
                                        <i class="ti ti-x me-1"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-0">
                            <h5 class="mb-0 fw-bold">Broker Stats</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Total Campaigns</label>
                                <p class="mb-0">{{ $broker->campaigns()->count() }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Active Connections</label>
                                <p class="mb-0">{{ $broker->connections()->where('is_active', true)->count() }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Last Sync</label>
                                <p class="mb-0">{{ $broker->last_sync ? $broker->last_sync->diffForHumans() : 'Never' }}</p>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-semibold">Created At</label>
                                <p class="mb-0">{{ $broker->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Card -->
                    <div class="card border-danger shadow-sm mt-3">
                        <div class="card-header bg-danger-subtle border-0">
                            <h5 class="mb-0 fw-bold text-danger">
                                <i class="ti ti-alert-triangle me-1"></i> Danger Zone
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Once you delete this broker, all its data will be permanently removed.</p>
                            <form action="{{ route('brokers.destroy', $broker) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to delete this broker? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="ti ti-trash me-1"></i> Delete Broker
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

