@extends('admin.layouts.app')

@section('admin-content')
<div class="row">
    <div class="col-12">
        <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-semibold m-0">SMTP Settings</h4>
                <p class="text-muted mb-0">Configure email server settings for sending emails</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Email Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.smtp.save') }}">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_mailer" class="form-label">Mail Driver</label>
                                <select class="form-select @error('mail_mailer') is-invalid @enderror" 
                                        id="mail_mailer" name="mail_mailer">
                                    <option value="smtp" {{ old('mail_mailer', $settings['mail_mailer'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                    <option value="sendmail" {{ old('mail_mailer', $settings['mail_mailer'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                    <option value="mailgun" {{ old('mail_mailer', $settings['mail_mailer'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                    <option value="ses" {{ old('mail_mailer', $settings['mail_mailer'] ?? '') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                </select>
                                @error('mail_mailer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_host" class="form-label">SMTP Host <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('mail_host') is-invalid @enderror" 
                                       id="mail_host" name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? '') }}" required>
                                @error('mail_host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_port" class="form-label">SMTP Port <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('mail_port') is-invalid @enderror" 
                                       id="mail_port" name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" required>
                                @error('mail_port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Common ports: 587 (TLS), 465 (SSL), 25 (unencrypted)</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_encryption" class="form-label">Encryption</label>
                                <select class="form-select @error('mail_encryption') is-invalid @enderror" 
                                        id="mail_encryption" name="mail_encryption">
                                    <option value="tls" {{ old('mail_encryption', $settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('mail_encryption', $settings['mail_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="" {{ old('mail_encryption', $settings['mail_encryption'] ?? '') == '' ? 'selected' : '' }}>None</option>
                                </select>
                                @error('mail_encryption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_username" class="form-label">SMTP Username</label>
                                <input type="text" class="form-control @error('mail_username') is-invalid @enderror" 
                                       id="mail_username" name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}">
                                @error('mail_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_password" class="form-label">SMTP Password</label>
                                <input type="password" class="form-control @error('mail_password') is-invalid @enderror" 
                                       id="mail_password" name="mail_password" value="{{ old('mail_password', $settings['mail_password'] ?? '') }}">
                                @error('mail_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_from_address" class="form-label">From Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('mail_from_address') is-invalid @enderror" 
                                       id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" required>
                                @error('mail_from_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mail_from_name" class="form-label">From Name</label>
                                <input type="text" class="form-control @error('mail_from_name') is-invalid @enderror" 
                                       id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? '') }}">
                                @error('mail_from_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="mail_verify_peer" name="mail_verify_peer" 
                                   value="1" {{ old('mail_verify_peer', $settings['mail_verify_peer'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="mail_verify_peer">
                                Verify SSL peer
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Save SMTP Settings
                        </button>
                        <button type="button" class="btn btn-outline-info" id="testEmailBtn">
                            <i class="ti ti-mail me-1"></i>Test Email
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
                <h5 class="card-title mb-0">SMTP Configuration Help</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">Common SMTP Settings</h6>
                    <ul class="mb-0">
                        <li><strong>Gmail:</strong> smtp.gmail.com:587 (TLS)</li>
                        <li><strong>Outlook:</strong> smtp-mail.outlook.com:587 (TLS)</li>
                        <li><strong>Yahoo:</strong> smtp.mail.yahoo.com:587 (TLS)</li>
                        <li><strong>Custom:</strong> Check with your provider</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6 class="alert-heading">Security Notes</h6>
                    <ul class="mb-0">
                        <li>Use TLS/SSL encryption when possible</li>
                        <li>Never share SMTP credentials</li>
                        <li>Test settings before going live</li>
                        <li>Consider using app-specific passwords</li>
                    </ul>
                </div>
                
                <div class="alert alert-success">
                    <h6 class="alert-heading">Test Email</h6>
                    <p class="mb-0">Use the "Test Email" button to verify your configuration works correctly.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Email Modal -->
<div class="modal fade" id="testEmailModal" tabindex="-1" aria-labelledby="testEmailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testEmailModalLabel">Test Email Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="testEmailForm">
                    @csrf
                    <div class="mb-3">
                        <label for="test_email" class="form-label">Test Email Address</label>
                        <input type="email" class="form-control" id="test_email" name="test_email" 
                               value="{{ Auth::guard('admin')->user()->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="test_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="test_subject" name="test_subject" 
                               value="SMTP Configuration Test" required>
                    </div>
                    <div class="mb-3">
                        <label for="test_message" class="form-label">Message</label>
                        <textarea class="form-control" id="test_message" name="test_message" rows="3" required>This is a test email to verify SMTP configuration is working correctly.</textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendTestEmail">Send Test Email</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test email button
    const testEmailBtn = document.getElementById('testEmailBtn');
    const testEmailModal = new bootstrap.Modal(document.getElementById('testEmailModal'));
    const sendTestEmailBtn = document.getElementById('sendTestEmail');
    
    testEmailBtn.addEventListener('click', function() {
        testEmailModal.show();
    });
    
    sendTestEmailBtn.addEventListener('click', function() {
        const form = document.getElementById('testEmailForm');
        const formData = new FormData(form);
        
        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<i class="ti ti-loader me-1"></i>Sending...';
        
        fetch('{{ route("admin.settings.test-email") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Test email sent successfully!');
                testEmailModal.hide();
            } else {
                alert('Failed to send test email: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            alert('Error sending test email: ' + error.message);
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="ti ti-mail me-1"></i>Send Test Email';
        });
    });
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
</script>
@endpush
