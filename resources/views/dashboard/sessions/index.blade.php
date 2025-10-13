@extends('layouts.vertical', ['title' => 'Login Sessions'])

@section('css')
<style>
    /* Blinking animation for online indicator */
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    .blink-animation {
        animation: blink 2s infinite;
    }
    
    /* Pulse animation for online status */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }
    
    .online-pulse {
        animation: pulse 2s infinite;
    }
</style>
@endsection

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Security', 'title' => 'Login Sessions'])

    <!-- Statistics Cards -->
    <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 text-center mb-3">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Total Sessions</h5>
                    <h3 class="mb-0 fw-bold">{{ $stats['total_sessions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">
                        <i class="ti ti-circle-filled blink-animation text-success"></i> Online
                    </h5>
                    <h3 class="mb-0 fw-bold text-success">{{ $stats['online_sessions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Active Now</h5>
                    <h3 class="mb-0 fw-bold text-primary">{{ $stats['active_sessions'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Devices</h5>
                    <h3 class="mb-0 fw-bold text-info">{{ $stats['devices']->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="text-muted fs-13 text-uppercase">Locations</h5>
                    <h3 class="mb-0 fw-bold text-warning">{{ $stats['locations']->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Session Card -->
    @if($currentSession)
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card border-success">
                <div class="card-header bg-success-subtle border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0 text-success">
                            <i class="ti ti-circle-check me-2"></i>Current Session
                        </h4>
                        <div class="d-flex gap-2">
                            <span class="badge bg-success">
                                <i class="ti ti-circle-filled blink-animation"></i> Online
                            </span>
                            <span class="badge bg-success">Active Now</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Device Info -->
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-primary-subtle rounded me-3">
                                    <span class="avatar-title bg-primary-subtle text-primary">
                                        <i class="{{ $currentSession->device_icon }} fs-20"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-muted mb-1 fs-12">Device</p>
                                    <h6 class="mb-0">{{ $currentSession->device_info }}</h6>
                                    <small class="text-muted">{{ $currentSession->browser_info }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-info-subtle rounded me-3">
                                    <span class="avatar-title bg-info-subtle text-info">
                                        <i class="ti ti-map-pin fs-20"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-muted mb-1 fs-12">Location</p>
                                    <h6 class="mb-0">{{ $currentSession->city ?? 'Unknown' }}</h6>
                                    <small class="text-muted">{{ $currentSession->country ?? 'Unknown Country' }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- IP Address -->
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-warning-subtle rounded me-3">
                                    <span class="avatar-title bg-warning-subtle text-warning">
                                        <i class="ti ti-network fs-20"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-muted mb-1 fs-12">IP Address</p>
                                    <h6 class="mb-0"><code>{{ $currentSession->ip_address }}</code></h6>
                                    <small class="text-muted">{{ $currentSession->timezone ?? 'UTC' }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Login Time -->
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm bg-success-subtle rounded me-3">
                                    <span class="avatar-title bg-success-subtle text-success">
                                        <i class="ti ti-clock fs-20"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="text-muted mb-1 fs-12">Active Since</p>
                                    <h6 class="mb-0">{{ $currentSession->login_at?->format('H:i') }}</h6>
                                    <small class="text-muted">{{ $currentSession->login_at?->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($currentSession->referrer_url)
                    <div class="mt-3 pt-3 border-top">
                        <p class="text-muted mb-1 fs-12">Came From (Referrer):</p>
                        <a href="{{ $currentSession->referrer_url }}" target="_blank" class="text-primary">
                            <i class="ti ti-external-link me-1"></i>{{ \Illuminate\Support\Str::limit($currentSession->referrer_url, 60) }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Actions Bar -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Other Sessions ({{ $otherSessions->where('is_active', true)->count() }})</h5>
                <div class="d-flex gap-2">
                    @if($otherSessions->where('is_active', true)->count() > 0)
                    <button type="button" class="btn btn-danger btn-sm" onclick="logoutAllOthers()">
                        <i class="ti ti-logout me-1"></i> Logout All Other Devices
                    </button>
                    @endif
                    <button type="button" class="btn btn-light btn-sm" onclick="cleanupExpired()">
                        <i class="ti ti-trash me-1"></i> Cleanup Expired
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" id="searchInput" placeholder="IP, City, Country...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="select2 form-control" id="statusFilter" data-toggle="select2">
                                <option value="">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Device</label>
                            <select class="select2 form-control" id="deviceFilter" data-toggle="select2">
                                <option value="">All Devices</option>
                                <option value="desktop">Desktop</option>
                                <option value="mobile">Mobile</option>
                                <option value="tablet">Tablet</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary me-2" onclick="applyFilters()">
                                <i class="ti ti-filter me-1"></i> Apply
                            </button>
                            <button type="button" class="btn btn-light" onclick="resetFilters()">
                                <i class="ti ti-x me-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <h4 class="header-title mb-0">All Login Sessions</h4>
                </div>
                <div class="card-body">
                    <div id="sessionsContainer">
                        @forelse($otherSessions as $session)
                        <div class="session-item border rounded p-3 mb-3 {{ $session->is_active ? 'border-success-subtle' : 'border-secondary' }}" data-session-id="{{ $session->id }}">
                            <div class="row align-items-center">
                                <!-- Device & Browser -->
                                <div class="col-md-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-md bg-{{ $session->is_active ? 'success' : 'secondary' }}-subtle rounded me-3">
                                            <span class="avatar-title bg-{{ $session->is_active ? 'success' : 'secondary' }}-subtle text-{{ $session->is_active ? 'success' : 'secondary' }}">
                                                <i class="{{ $session->device_icon }} fs-24"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $session->device_info }}</h6>
                                            <small class="text-muted">{{ $session->browser_info }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location & IP -->
                                <div class="col-md-3">
                                    <p class="text-muted mb-1 fs-12">
                                        <i class="ti ti-map-pin me-1"></i>Location
                                    </p>
                                    <h6 class="mb-0">{{ $session->location }}</h6>
                                    <small class="text-muted"><code>{{ $session->ip_address }}</code></small>
                                </div>

                                <!-- Time & Status -->
                                <div class="col-md-3">
                                    <p class="text-muted mb-1 fs-12">
                                        <i class="ti ti-clock me-1"></i>Last Activity
                                    </p>
                                    <h6 class="mb-0">{{ $session->last_activity?->diffForHumans() }}</h6>
                                    <div class="d-flex gap-1 session-status-badge">
                                        @if($session->is_active)
                                            @if($session->isOnline())
                                                <span class="badge bg-success-subtle text-success online-pulse">
                                                    <i class="ti ti-circle-filled blink-animation"></i> Online
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="ti ti-circle"></i> Away
                                                </span>
                                            @endif
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="ti ti-circle"></i> {{ ucfirst($session->logout_reason ?? 'Offline') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="col-md-3 text-end">
                                    <button class="btn btn-soft-info btn-sm" onclick="viewSession({{ $session->id }})">
                                        <i class="ti ti-eye"></i> Details
                                    </button>
                                    @if($session->is_active)
                                    <button class="btn btn-soft-danger btn-sm" onclick="logoutSession({{ $session->id }})">
                                        <i class="ti ti-logout"></i> Logout
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Additional Info (Collapsed) -->
                            @if($session->referrer_url || $session->utm_source)
                            <div class="mt-2 pt-2 border-top">
                                <small class="text-muted">
                                    @if($session->referrer_url)
                                        <i class="ti ti-external-link me-1"></i>
                                        From: <a href="{{ $session->referrer_url }}" target="_blank">{{ \Illuminate\Support\Str::limit($session->referrer_url, 50) }}</a>
                                    @endif
                                    @if($session->utm_source)
                                        | <i class="ti ti-tag me-1"></i>Source: {{ $session->utm_source }}
                                    @endif
                                </small>
                            </div>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <i class="ti ti-login fs-64 text-muted"></i>
                            <h5 class="text-muted mt-3">No Other Sessions</h5>
                            <p class="text-muted">You are only logged in from this device</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Details Modal -->
    <div class="modal fade" id="sessionDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Session Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="sessionDetailsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
let filters = {};

window.addEventListener('load', function() {
    // Initialize Select2
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('[data-toggle="select2"]').select2();
    }

    // Search with debounce
    let searchTimeout;
    document.getElementById('searchInput')?.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filters.search = e.target.value;
            applyFilters();
        }, 500);
    });
    
    // Setup real-time updates with Pusher
    setupRealtimeUpdates();
    
    // Auto-update online status every 30 seconds
    setInterval(updateOnlineStatus, 30000);
});

// Setup Pusher for real-time session updates
function setupRealtimeUpdates() {
    // Check if Pusher is configured
    const pusherKey = '{{ config("broadcasting.connections.pusher.key") }}';
    const pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
    
    if (!pusherKey || pusherKey === '') {
        console.log('Pusher not configured - real-time updates disabled');
        return;
    }
    
    try {
        const currentSessionId = '{{ session()->getId() }}';
        
        // Initialize Pusher directly
        const pusher = new Pusher(pusherKey, {
            cluster: pusherCluster,
            encrypted: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            }
        });

        const userChannel = pusher.subscribe('private-user.{{ auth()->id() }}');
        const sessionChannel = pusher.subscribe('private-session.' + currentSessionId);

        // Listen for new session events
        userChannel.bind('session.created', function(data) {
            console.log('New session detected:', data);
            
            // Show toast notification
            showNewSessionNotification(data);
            
            // Reload sessions list after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        });
        
        // Listen for session termination (real-time logout)
        userChannel.bind('session.terminated', function(data) {
            console.log('Session terminated event received:', data);
            handleSessionTerminated(data);
        });
        
        sessionChannel.bind('session.terminated', function(data) {
            console.log('This session was terminated:', data);
            handleSessionTerminated(data);
        });
        
        // Connection monitoring
        pusher.connection.bind('connected', function() {
            console.log('✅ Pusher connected to cluster: ' + pusherCluster);
        });
        
        pusher.connection.bind('error', function(err) {
            console.error('❌ Pusher error:', err);
        });
        
    } catch (error) {
        console.error('Pusher setup error:', error);
    }
}

// Handle session termination (force logout)
function handleSessionTerminated(data) {
    const currentSessionId = '{{ session()->getId() }}';
    
    // Check if this is our current session
    if (data.device_session_id === currentSessionId) {
        Swal.fire({
            icon: 'warning',
            title: 'Session Terminated!',
            html: `
                <p>Your session has been terminated.</p>
                <p><strong>Reason:</strong> ${getTerminationReason(data.reason)}</p>
                <p class="text-muted">You will be logged out now.</p>
            `,
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(() => {
            // Force logout
            performForceLogout();
        });
        
        // Auto logout after 3 seconds even if modal is closed
        setTimeout(() => {
            performForceLogout();
        }, 3000);
    }
}

// Get user-friendly termination reason
function getTerminationReason(reason) {
    const reasons = {
        'forced': 'Terminated by you from another device',
        'forced_by_user': 'You logged out all other devices',
        'self_logout': 'You logged out',
        'expired': 'Session expired',
        'admin': 'Terminated by administrator'
    };
    
    return reasons[reason] || 'Session terminated';
}

// Perform force logout
function performForceLogout() {
    // Use fetch API for logout (more reliable)
    fetch('{{ route("logout") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        credentials: 'same-origin'
    })
    .then(() => {
        // Clear storage after logout
        if (localStorage) localStorage.clear();
        if (sessionStorage) sessionStorage.clear();
        
        // Redirect to login
        window.location.href = '{{ route("login") }}';
    })
    .catch(error => {
        console.error('Logout error:', error);
        
        // Clear storage anyway
        if (localStorage) localStorage.clear();
        if (sessionStorage) sessionStorage.clear();
        
        // Force redirect
        window.location.href = '{{ route("login") }}';
    });
}

// Show notification for new session
function showNewSessionNotification(data) {
    Swal.fire({
        title: 'تسجيل دخول جديد!',
        html: `
            <div class="text-start">
                <p class="mb-2"><strong>${data.message}</strong></p>
                <hr>
                <p class="mb-1"><i class="ti ti-device-desktop me-2"></i><strong>الجهاز:</strong> ${data.session.device}</p>
                <p class="mb-1"><i class="ti ti-world me-2"></i><strong>المتصفح:</strong> ${data.session.browser}</p>
                <p class="mb-1"><i class="ti ti-map-pin me-2"></i><strong>الموقع:</strong> ${data.session.location}</p>
                <p class="mb-0"><i class="ti ti-network me-2"></i><strong>IP:</strong> <code>${data.session.ip_address}</code></p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'حسناً',
        showCancelButton: true,
        cancelButtonText: 'عرض السيشنات',
        timer: 10000,
        timerProgressBar: true
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.reload();
        }
    });
}

// Apply filters
function applyFilters() {
    filters.status = $('#statusFilter').val();
    filters.device_type = $('#deviceFilter').val();
    
    loadSessions();
}

// Reset filters
function resetFilters() {
    filters = {};
    document.getElementById('searchInput').value = '';
    $('#statusFilter').val('').trigger('change');
    $('#deviceFilter').val('').trigger('change');
    
    loadSessions();
}

// Load sessions with filters
function loadSessions() {
    const params = new URLSearchParams();
    
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            params.append(key, filters[key]);
        }
    });
    
    window.location.href = '{{ route("sessions.index") }}?' + params.toString();
}

// View session details
function viewSession(sessionId) {
    const modal = new bootstrap.Modal(document.getElementById('sessionDetailsModal'));
    modal.show();
    
    // Show loading
    document.getElementById('sessionDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    $.ajax({
        url: `/dashboard/sessions/${sessionId}`,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        success: function(data) {
            if (data.success) {
                renderSessionDetails(data.session);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading session:', {xhr, status, error});
            document.getElementById('sessionDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Error loading session details</div>';
        }
    });
}

// Render session details in modal
function renderSessionDetails(session) {
    const html = `
        <div class="row g-3">
            <div class="col-md-6">
                <label class="text-muted small">Session ID</label>
                <p class="mb-0"><code>${session.session_id}</code></p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">IP Address</label>
                <p class="mb-0"><code>${session.ip_address}</code></p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Device</label>
                <p class="mb-0">${session.device_name || 'Unknown'} (${session.device_type})</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Platform</label>
                <p class="mb-0">${session.platform || 'Unknown'}</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Browser</label>
                <p class="mb-0">${session.browser || 'Unknown'} ${session.browser_version || ''}</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Status</label>
                <p class="mb-0">
                    ${session.is_active 
                        ? (session.is_online 
                            ? '<span class="badge bg-success"><i class="ti ti-circle-filled blink-animation"></i> Online</span>' 
                            : '<span class="badge bg-warning"><i class="ti ti-circle"></i> Away</span>')
                        : '<span class="badge bg-secondary"><i class="ti ti-circle"></i> Offline</span>'}
                </p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Country</label>
                <p class="mb-0">${session.country || 'Unknown'} (${session.country_code || 'N/A'})</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">City</label>
                <p class="mb-0">${session.city || 'Unknown'}</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Region</label>
                <p class="mb-0">${session.region || 'Unknown'}</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Timezone</label>
                <p class="mb-0">${session.timezone || 'UTC'}</p>
            </div>
            ${session.latitude && session.longitude ? `
            <div class="col-12">
                <label class="text-muted small">Coordinates</label>
                <p class="mb-0">
                    <a href="https://www.google.com/maps?q=${session.latitude},${session.longitude}" target="_blank" class="text-primary">
                        <i class="ti ti-map me-1"></i>${session.latitude}, ${session.longitude}
                    </a>
                </p>
            </div>
            ` : ''}
            <div class="col-md-6">
                <label class="text-muted small">Login Time</label>
                <p class="mb-0">${session.login_at || 'Unknown'}</p>
            </div>
            <div class="col-md-6">
                <label class="text-muted small">Last Activity</label>
                <p class="mb-0">${session.last_activity || 'Unknown'}</p>
            </div>
            ${session.referrer_url ? `
            <div class="col-12">
                <label class="text-muted small">Referrer URL</label>
                <p class="mb-0 text-break">
                    <a href="${session.referrer_url}" target="_blank">${session.referrer_url}</a>
                </p>
            </div>
            ` : ''}
            ${session.landing_page ? `
            <div class="col-12">
                <label class="text-muted small">Landing Page</label>
                <p class="mb-0 text-break">${session.landing_page}</p>
            </div>
            ` : ''}
            ${session.utm_source || session.utm_medium || session.utm_campaign ? `
            <div class="col-12">
                <label class="text-muted small">UTM Parameters</label>
                <p class="mb-0">
                    ${session.utm_source ? `Source: <span class="badge bg-primary">${session.utm_source}</span> ` : ''}
                    ${session.utm_medium ? `Medium: <span class="badge bg-info">${session.utm_medium}</span> ` : ''}
                    ${session.utm_campaign ? `Campaign: <span class="badge bg-success">${session.utm_campaign}</span>` : ''}
                </p>
            </div>
            ` : ''}
            <div class="col-12">
                <label class="text-muted small">User Agent</label>
                <p class="mb-0 text-break"><small><code>${session.user_agent}</code></small></p>
            </div>
        </div>
    `;
    
    document.getElementById('sessionDetailsContent').innerHTML = html;
}

// Logout specific session
function logoutSession(sessionId) {
    Swal.fire({
        title: 'Logout This Session?',
        text: 'This will terminate the session and log you out if it\'s your current session',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f1556c',
        confirmButtonText: 'Yes, Logout!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/dashboard/sessions/${sessionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Check if this was current session
                    if (data.is_current && data.redirect) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Logged Out!',
                            text: 'You have been logged out successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        // Redirect to login page after 2 seconds
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    } else {
                        // Just a remote session termination
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000
                        });
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to logout session'
                });
            });
        }
    });
}

// Logout all other sessions
function logoutAllOthers() {
    Swal.fire({
        title: 'Logout All Other Devices?',
        text: 'This will terminate all sessions except your current one',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f1556c',
        confirmButtonText: 'Yes, Logout All!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/dashboard/sessions/logout-others', {
                method: 'POST',
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
                    setTimeout(() => location.reload(), 2000);
                }
            });
        }
    });
}

// Cleanup expired sessions
function cleanupExpired() {
    fetch('/dashboard/sessions/cleanup', {
        method: 'POST',
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
            setTimeout(() => location.reload(), 2000);
        }
    });
}

// Update online status indicators
function updateOnlineStatus() {
    // Update badge indicators for all sessions
    document.querySelectorAll('[data-session-id]').forEach(element => {
        const sessionId = element.dataset.sessionId;
        const badge = element.querySelector('.session-status-badge');
        
        if (badge) {
            // Visual feedback that status is being checked
            badge.classList.add('opacity-50');
            
            setTimeout(() => {
                badge.classList.remove('opacity-50');
            }, 500);
        }
    });
    
    // Optionally reload data from server
    // Uncomment if you want to fetch fresh data:
    // fetch('/dashboard/sessions/data')
    //     .then(response => response.json())
    //     .then(data => {
    //         // Update UI with fresh data
    //     });
}
</script>
@endsection

