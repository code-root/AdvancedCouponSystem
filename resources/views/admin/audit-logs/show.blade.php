@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Audit Log Details #{{ $log->id }}</h4>
        <p class="text-muted mb-0">Detailed information about this audit log entry</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.audit-logs.index') }}">Audit Logs</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Log Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Log Information</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 200px;">Log ID</th>
                                <td>{{ $log->id }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Admin</th>
                                <td>
                                    @if($log->admin)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded me-3">
                                                <span class="avatar-title bg-primary-subtle text-primary fs-16">
                                                    {{ substr($log->admin->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $log->admin->name }}</h6>
                                                <small class="text-muted">{{ $log->admin->email }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Unknown Admin (ID: {{ $log->admin_id }})</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Action</th>
                                <td>
                                    <span class="badge bg-{{ $log->action == 'created' ? 'success' : ($log->action == 'updated' ? 'warning' : ($log->action == 'deleted' ? 'danger' : 'info')) }}-subtle text-{{ $log->action == 'created' ? 'success' : ($log->action == 'updated' ? 'warning' : ($log->action == 'deleted' ? 'danger' : 'info')) }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Description</th>
                                <td>{{ $log->description ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Model Type</th>
                                <td>
                                    @if($log->model_type)
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ class_basename($log->model_type) }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Model ID</th>
                                <td>{{ $log->model_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Timestamp</th>
                                <td>
                                    {{ $log->created_at->format('M d, Y H:i:s') }}
                                    <br><small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Changes -->
        @if($log->old_values || $log->new_values)
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Changes</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Old Values</h6>
                        @if($log->old_values)
                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                        @else
                            <div class="text-center py-3">
                                <i class="ti ti-minus fs-48 text-muted mb-3"></i>
                                <p class="text-muted">No old values</p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">New Values</h6>
                        @if($log->new_values)
                            <pre class="bg-light p-3 rounded"><code>{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                        @else
                            <div class="text-center py-3">
                                <i class="ti ti-minus fs-48 text-muted mb-3"></i>
                                <p class="text-muted">No new values</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <div class="col-lg-4">
        <!-- Request Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Request Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 120px;">IP Address</th>
                                <td>
                                    <span class="text-muted">{{ $log->ip_address ?? 'N/A' }}</span>
                                    @if($log->ip_address)
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $log->ip_address }}')">
                                            <i class="ti ti-copy"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">User Agent</th>
                                <td>
                                    @if($log->user_agent)
                                        <small class="text-muted">{{ Str::limit($log->user_agent, 50) }}</small>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="showFullUserAgent()">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">URL</th>
                                <td>
                                    @if($log->url)
                                        <small class="text-muted">{{ Str::limit($log->url, 40) }}</small>
                                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="copyToClipboard('{{ $log->url }}')">
                                            <i class="ti ti-copy"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Method</th>
                                <td>
                                    @if($log->method)
                                        <span class="badge bg-{{ $log->method == 'GET' ? 'info' : ($log->method == 'POST' ? 'success' : ($log->method == 'PUT' ? 'warning' : ($log->method == 'DELETE' ? 'danger' : 'secondary'))) }}-subtle text-{{ $log->method == 'GET' ? 'info' : ($log->method == 'POST' ? 'success' : ($log->method == 'PUT' ? 'warning' : ($log->method == 'DELETE' ? 'danger' : 'secondary'))) }}">
                                            {{ $log->method }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Related Logs -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Related Logs</h5>
            </div>
            <div class="card-body">
                @if($relatedLogs->count() > 0)
                    @foreach($relatedLogs as $relatedLog)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">{{ ucfirst($relatedLog->action) }}</h6>
                                <small class="text-muted">{{ $relatedLog->description }}</small>
                                <br><small class="text-muted">{{ $relatedLog->created_at->diffForHumans() }}</small>
                            </div>
                            <a href="{{ route('admin.audit-logs.show', $relatedLog->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-eye"></i>
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-3">
                        <i class="ti ti-file-text fs-48 text-muted mb-3"></i>
                        <p class="text-muted">No related logs</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-2"></i>Back to Audit Logs
                    </a>
                    
                    @if($log->admin)
                        <a href="{{ route('admin.admin-users.show', $log->admin->id) }}" class="btn btn-outline-info">
                            <i class="ti ti-user me-2"></i>View Admin Profile
                        </a>
                    @endif
                    
                    <button class="btn btn-outline-success" onclick="exportLog()">
                        <i class="ti ti-download me-2"></i>Export Log
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Agent Modal -->
<div class="modal fade" id="userAgentModal" tabindex="-1" aria-labelledby="userAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userAgentModalLabel">Full User Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre class="bg-light p-3 rounded"><code>{{ $log->user_agent ?? 'N/A' }}</code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="copyToClipboard('{{ $log->user_agent }}')">Copy</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        window.showNotification('Copied to clipboard!', 'success');
    }, function(err) {
        window.showNotification('Failed to copy to clipboard', 'error');
    });
}

function showFullUserAgent() {
    const modal = new bootstrap.Modal(document.getElementById('userAgentModal'));
    modal.show();
}

function exportLog() {
    // This would typically generate a PDF or export the log data
    window.showNotification('Export feature coming soon!', 'info');
}
</script>
@endpush