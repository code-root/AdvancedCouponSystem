<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted mb-3">Session Information</h6>
        <table class="table table-sm">
            <tr>
                <th>Session ID:</th>
                <td><code>{{ $session->session_id }}</code></td>
            </tr>
            <tr>
                <th>User:</th>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary-subtle rounded me-2">
                            <span class="avatar-title bg-primary-subtle text-primary">
                                {{ substr($session->user->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <div class="fw-medium">{{ $session->user->name }}</div>
                            <small class="text-muted">{{ $session->user->email }}</small>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>Status:</th>
                <td>
                    <span class="badge bg-{{ $session->is_active ? 'success' : 'danger' }}-subtle text-{{ $session->is_active ? 'success' : 'danger' }}">
                        {{ $session->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>IP Address:</th>
                <td><code>{{ $session->ip_address ?? 'N/A' }}</code></td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-muted mb-3">Device Information</h6>
        <table class="table table-sm">
            <tr>
                <th>Device:</th>
                <td>
                    <i class="ti ti-{{ $session->device_type == 'mobile' ? 'device-mobile' : ($session->device_type == 'tablet' ? 'device-tablet' : 'device-desktop') }} me-1"></i>
                    {{ $session->device_name ?? 'Unknown' }}
                </td>
            </tr>
            <tr>
                <th>Platform:</th>
                <td>{{ $session->platform ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Browser:</th>
                <td>
                    {{ $session->browser ?? 'N/A' }}
                    @if($session->browser_version)
                        <small class="text-muted">({{ $session->browser_version }})</small>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Location:</th>
                <td>
                    @if($session->city && $session->country)
                        {{ $session->city }}, {{ $session->country }}
                    @else
                        <span class="text-muted">Unknown</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <h6 class="text-muted mb-3">Timing Information</h6>
        <table class="table table-sm">
            <tr>
                <th>Login Time:</th>
                <td>
                    @if($session->login_at)
                        {{ $session->login_at->format('M d, Y H:i:s') }}
                        <br><small class="text-muted">{{ $session->login_at->diffForHumans() }}</small>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Last Activity:</th>
                <td>
                    @if($session->last_activity)
                        {{ $session->last_activity->format('M d, Y H:i:s') }}
                        <br><small class="text-muted">{{ $session->last_activity->diffForHumans() }}</small>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Logout Time:</th>
                <td>
                    @if($session->logout_at)
                        {{ $session->logout_at->format('M d, Y H:i:s') }}
                        <br><small class="text-muted">{{ $session->logout_at->diffForHumans() }}</small>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-muted mb-3">Session Duration</h6>
        <table class="table table-sm">
            <tr>
                <th>Duration:</th>
                <td>
                    @if($session->login_at)
                        @if($session->logout_at)
                            {{ $session->login_at->diffForHumans($session->logout_at, true) }}
                        @else
                            {{ $session->login_at->diffForHumans(now(), true) }} (ongoing)
                        @endif
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Created:</th>
                <td>
                    {{ $session->created_at->format('M d, Y H:i:s') }}
                    <br><small class="text-muted">{{ $session->created_at->diffForHumans() }}</small>
                </td>
            </tr>
        </table>
    </div>
</div>

@if($session->user_agent)
<div class="row mt-3">
    <div class="col-12">
        <h6 class="text-muted mb-3">User Agent</h6>
        <pre class="bg-light p-3 rounded"><code>{{ $session->user_agent }}</code></pre>
    </div>
</div>
@endif