@extends('admin.layouts.ajax-wrapper')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">Payment Settings</h4>
                <p class="text-muted mb-0">Configure payment gateway settings for Stripe and PayPal</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment Gateway Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.payment.save') }}">
                    @csrf
                    
                    <!-- Stripe Settings -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-credit-card me-2"></i>
                            Stripe Configuration
                        </h6>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stripe_public_key" class="form-label">Stripe Public Key</label>
                                <input type="text" class="form-control @error('stripe_public_key') is-invalid @enderror" 
                                       id="stripe_public_key" name="stripe_public_key" 
                                       value="{{ old('stripe_public_key', $settings['stripe_public_key'] ?? '') }}"
                                       placeholder="pk_test_...">
                                @error('stripe_public_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="stripe_secret_key" class="form-label">Stripe Secret Key</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control @error('stripe_secret_key') is-invalid @enderror" 
                                           id="stripe_secret_key" name="stripe_secret_key" 
                                           value="{{ old('stripe_secret_key', $settings['stripe_secret_key'] ?? '') }}"
                                           placeholder="sk_test_...">
                                    <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                            onclick="togglePassword('stripe_secret_key')">
                                        <i class="ti ti-eye" id="stripe_secret_key_icon"></i>
                                    </button>
                                </div>
                                @error('stripe_secret_key')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="stripe_webhook_secret" class="form-label">Stripe Webhook Secret</label>
                        <div class="position-relative">
                            <input type="password" class="form-control @error('stripe_webhook_secret') is-invalid @enderror" 
                                   id="stripe_webhook_secret" name="stripe_webhook_secret" 
                                   value="{{ old('stripe_webhook_secret', $settings['stripe_webhook_secret'] ?? '') }}"
                                   placeholder="whsec_...">
                            <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                    onclick="togglePassword('stripe_webhook_secret')">
                                <i class="ti ti-eye" id="stripe_webhook_secret_icon"></i>
                            </button>
                        </div>
                        @error('stripe_webhook_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">

                    <!-- PayPal Settings -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-brand-paypal me-2"></i>
                            PayPal Configuration
                        </h6>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paypal_client_id" class="form-label">PayPal Client ID</label>
                                <input type="text" class="form-control @error('paypal_client_id') is-invalid @enderror" 
                                       id="paypal_client_id" name="paypal_client_id" 
                                       value="{{ old('paypal_client_id', $settings['paypal_client_id'] ?? '') }}"
                                       placeholder="Client ID">
                                @error('paypal_client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paypal_client_secret" class="form-label">PayPal Client Secret</label>
                                <div class="position-relative">
                                    <input type="password" class="form-control @error('paypal_client_secret') is-invalid @enderror" 
                                           id="paypal_client_secret" name="paypal_client_secret" 
                                           value="{{ old('paypal_client_secret', $settings['paypal_client_secret'] ?? '') }}"
                                           placeholder="Client Secret">
                                    <button type="button" class="btn btn-outline-secondary position-absolute end-0 top-50 translate-middle-y me-2" 
                                            onclick="togglePassword('paypal_client_secret')">
                                        <i class="ti ti-eye" id="paypal_client_secret_icon"></i>
                                    </button>
                                </div>
                                @error('paypal_client_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paypal_mode" class="form-label">PayPal Mode</label>
                                <select class="form-select @error('paypal_mode') is-invalid @enderror" 
                                        id="paypal_mode" name="paypal_mode">
                                    <option value="sandbox" {{ old('paypal_mode', $settings['paypal_mode'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>
                                        Sandbox (Testing)
                                    </option>
                                    <option value="live" {{ old('paypal_mode', $settings['paypal_mode'] ?? '') == 'live' ? 'selected' : '' }}>
                                        Live (Production)
                                    </option>
                                </select>
                                @error('paypal_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paypal_webhook_id" class="form-label">PayPal Webhook ID</label>
                                <input type="text" class="form-control @error('paypal_webhook_id') is-invalid @enderror" 
                                       id="paypal_webhook_id" name="paypal_webhook_id" 
                                       value="{{ old('paypal_webhook_id', $settings['paypal_webhook_id'] ?? '') }}"
                                       placeholder="Webhook ID">
                                @error('paypal_webhook_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Save Payment Settings
                        </button>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment Gateway Information</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Stripe Features</h6>
                    <ul class="mb-0">
                        <li>Credit/debit card processing</li>
                        <li>Global payment support</li>
                        <li>Real-time notifications</li>
                        <li>Advanced fraud protection</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">PayPal Features</h6>
                    <ul class="mb-0">
                        <li>PayPal account payments</li>
                        <li>Credit card processing</li>
                        <li>Buyer protection</li>
                        <li>Mobile payment support</li>
                    </ul>
                </div>
                
                <div class="alert alert-success">
                    <h6 class="alert-heading">Security Notes</h6>
                    <ul class="mb-0">
                        <li>Keep API keys secure</li>
                        <li>Use test keys for development</li>
                        <li>Enable webhook verification</li>
                        <li>Monitor payment logs</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
    } else {
        input.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // PayPal mode warning
    const paypalMode = document.getElementById('paypal_mode');
    paypalMode.addEventListener('change', function() {
        if (this.value === 'live') {
            if (!confirm('Are you sure you want to switch to live mode? This will process real payments.')) {
                this.value = 'sandbox';
            }
        }
    });
});
</script>
@endpush