@extends('layouts.vertical', ['title' => 'Session Details'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Security', 'title' => 'Session Details'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('sessions.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back to Sessions
                </a>
                @if($session->is_active && !$session->isCurrent())
                <button class="btn btn-danger" onclick="logoutThisSession()">
                    <i class="ti ti-logout me-1"></i> Terminate This Session
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Session Status Banner -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="alert {{ $session->is_active ? 'alert-success' : 'alert-secondary' }} mb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="alert-heading mb-1">
                            <i class="ti {{ $session->is_active ? 'ti-circle-check' : 'ti-circle-x' }} me-2"></i>
                            {{ $session->is_active ? 'Active Session' : 'Inactive Session' }}
                        </h5>
                        <p class="mb-0">
                            @if($session->isCurrent())
                                <strong>This is your current session</strong>
                            @elseif($session->is_active)
                                Last activity: {{ $session->last_activity?->diffForHumans() }}
                            @else
                                Terminated: {{ $session->updated_at->diffForHumans() }} ({{ ucfirst($session->logout_reason) }})
                            @endif
                        </p>
                    </div>
                    @if($session->isCurrent())
                    <span class="badge bg-success fs-14 px-3 py-2">
                        <i class="ti ti-circle-filled me-1"></i>YOU ARE HERE
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Main Information -->
    <div class="row">
        <!-- Device & Browser Info -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-device-desktop me-2"></i>Device & Browser Information
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Device Type</label>
                        <p class="mb-0">
                            <i class="{{ $session->device_icon }} me-1"></i>
                            {{ ucfirst($session->device_type ?? 'Unknown') }}
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Device Name</label>
                        <p class="mb-0">{{ $session->device_name ?? 'Unknown Device' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Platform / OS</label>
                        <p class="mb-0">{{ $session->platform ?? 'Unknown' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Browser</label>
                        <p class="mb-0">{{ $session->browser ?? 'Unknown' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Browser Version</label>
                        <p class="mb-0">{{ $session->browser_version ?? 'Unknown' }}</p>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small">User Agent</label>
                        <p class="mb-0 text-break"><small><code>{{ $session->user_agent }}</code></small></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Info -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-map-pin me-2"></i>Location Information
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">IP Address</label>
                        <p class="mb-0"><code class="fs-16">{{ $session->ip_address }}</code></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Country</label>
                        <p class="mb-0">
                            @if($session->country_code)
                            <img src="https://flagcdn.com/24x18/{{ strtolower($session->country_code) }}.png" 
                                 alt="{{ $session->country }}" class="me-2">
                            @endif
                            {{ $session->country ?? 'Unknown' }}
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Region</label>
                        <p class="mb-0">{{ $session->region ?? 'Unknown' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">City</label>
                        <p class="mb-0">{{ $session->city ?? 'Unknown' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Timezone</label>
                        <p class="mb-0">{{ $session->timezone ?? 'UTC' }}</p>
                    </div>
                    @if($session->latitude && $session->longitude)
                    <div class="mb-0">
                        <label class="text-muted small">Map Location</label>
                        <p class="mb-0">
                            <a href="https://www.google.com/maps?q={{ $session->latitude }},{{ $session->longitude }}" 
                               target="_blank" class="btn btn-soft-primary btn-sm">
                                <i class="ti ti-map me-1"></i>View on Google Maps
                            </a>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Session Timeline -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-clock me-2"></i>Session Timeline
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small">Login Time</label>
                            <p class="mb-0 fw-semibold">{{ $session->login_at?->format('M d, Y H:i:s') ?? 'Unknown' }}</p>
                            <small class="text-muted">{{ $session->login_at?->diffForHumans() }}</small>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Last Activity</label>
                            <p class="mb-0 fw-semibold">{{ $session->last_activity?->format('M d, Y H:i:s') ?? 'Unknown' }}</p>
                            <small class="text-muted">{{ $session->last_activity?->diffForHumans() }}</small>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Session Duration</label>
                            <p class="mb-0 fw-semibold">{{ $session->duration }}</p>
                        </div>
                        @if($session->expires_at)
                        <div class="col-md-4">
                            <label class="text-muted small">Expires At</label>
                            <p class="mb-0 fw-semibold">{{ $session->expires_at->format('M d, Y H:i:s') }}</p>
                            <small class="text-muted">{{ $session->expires_at->diffForHumans() }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referrer & Entry Information -->
    @if($session->referrer_url || $session->landing_page || $session->utm_source)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="card-title mb-0">
                        <i class="ti ti-external-link me-2"></i>Entry & Referrer Information
                    </h4>
                </div>
                <div class="card-body">
                    @if($session->referrer_url)
                    <div class="mb-3">
                        <label class="text-muted small">Came From (Referrer URL)</label>
                        <p class="mb-0 text-break">
                            <a href="{{ $session->referrer_url }}" target="_blank" class="text-primary">
                                <i class="ti ti-external-link me-1"></i>{{ $session->referrer_url }}
                            </a>
                        </p>
                    </div>
                    @endif

                    @if($session->landing_page)
                    <div class="mb-3">
                        <label class="text-muted small">First Page Visited (Landing Page)</label>
                        <p class="mb-0 text-break">{{ $session->landing_page }}</p>
                    </div>
                    @endif

                    @if($session->utm_source || $session->utm_medium || $session->utm_campaign)
                    <div class="mb-0">
                        <label class="text-muted small">Marketing Campaign (UTM Parameters)</label>
                        <div class="d-flex gap-2 flex-wrap mt-2">
                            @if($session->utm_source)
                            <div>
                                <small class="text-muted">Source:</small>
                                <span class="badge bg-primary-subtle text-primary">{{ $session->utm_source }}</span>
                            </div>
                            @endif
                            @if($session->utm_medium)
                            <div>
                                <small class="text-muted">Medium:</small>
                                <span class="badge bg-info-subtle text-info">{{ $session->utm_medium }}</span>
                            </div>
                            @endif
                            @if($session->utm_campaign)
                            <div>
                                <small class="text-muted">Campaign:</small>
                                <span class="badge bg-success-subtle text-success">{{ $session->utm_campaign }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('scripts')
<script>
function logoutThisSession() {
    Swal.fire({
        title: 'Terminate This Session?',
        text: 'This will force logout on this device',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f1556c',
        confirmButtonText: 'Yes, Terminate!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/dashboard/sessions/{{ $session->id }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000
                    });
                    setTimeout(() => {
                        window.location.href = '{{ route("sessions.index") }}';
                    }, 2000);
                }
            });
        }
    });
}
</script>
@endsection

