@extends('layouts.vertical', ['title' => 'Edit Network Connection'])

@section('css')
    @vite(['node_modules/select2/dist/css/select2.min.css'])
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Networks', 'title' => 'Edit Connection'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end">
                <a href="{{ route('networks.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Networks
                </a>
            </div>
        </div>
    </div>

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

    <form action="{{ route('networks.update', $network->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-bottom border-dashed">
                        <h4 class="card-title mb-0">Edit Network Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Display Name</label>
                                <input type="text" class="form-control" name="display_name" 
                                       value="{{ old('display_name', $network->display_name) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">System Name</label>
                                <input type="text" class="form-control" name="name" 
                                       value="{{ old('name', $network->name) }}" required readonly>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Commission Rate (%)</label>
                                <input type="number" step="0.01" class="form-control" name="commission_rate" 
                                       value="{{ old('commission_rate', $network->commission_rate) }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country" 
                                       value="{{ old('country', $network->country) }}" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">API URL</label>
                                <input type="url" class="form-control" name="api_url" 
                                       value="{{ old('api_url', $network->api_url) }}" required>
                            </div>

                            <div class="col-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3">{{ old('description', $network->description) }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="isActive" 
                                           value="1" {{ old('is_active', $network->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isActive">
                                        Active Network
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card bg-primary-subtle border-primary border-dashed">
                    <div class="card-body">
                        <h5 class="text-uppercase text-muted mb-3">Quick Info</h5>
                        <p class="text-muted fs-13">Update the network information carefully. Changes will affect all users connected to this network.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('networks.index') }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Update Network
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection
