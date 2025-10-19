@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Notification Details</h4>
        <p class="text-muted mb-0">View notification information</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
            <li class="breadcrumb-item active">Details</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Notification Information</h5>
            </div>
            <div class="card-body">
                @php
                    $data = json_decode($notification->data, true);
                @endphp
                
                <div class="d-flex align-items-start mb-4">
                    <div class="avatar-lg bg-primary-subtle rounded me-3">
                        <span class="avatar-title bg-primary-subtle text-primary fs-24">
                            <i class="ti {{ $data['icon'] ?? 'ti-bell' }}"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-1">{{ $data['title'] ?? 'Notification' }}</h4>
                        <p class="text-muted mb-2">{{ $data['message'] ?? 'No message available' }}</p>
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-{{ $data['color'] ?? 'primary' }}-subtle text-{{ $data['color'] ?? 'primary' }}">
                                {{ ucfirst($data['type'] ?? 'General') }}
                            </span>
                            <span class="badge bg-{{ $data['priority'] == 'high' ? 'danger' : ($data['priority'] == 'medium' ? 'warning' : 'secondary') }}-subtle text-{{ $data['priority'] == 'high' ? 'danger' : ($data['priority'] == 'medium' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($data['priority'] ?? 'Low') }} Priority
                            </span>
                            @if($notification->read_at)
                                <span class="badge bg-success-subtle text-success">
                                    <i class="ti ti-check me-1"></i>Read
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">
                                    <i class="ti ti-clock me-1"></i>Unread
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <tbody>
                            <tr>
                                <th scope="row" style="width: 200px;">Notification ID</th>
                                <td><code>{{ $notification->id }}</code></td>
                            </tr>
                            <tr>
                                <th scope="row">Type</th>
                                <td>{{ $notification->type }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Created At</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($notification->created_at)->format('M d, Y H:i:s') }}
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                                </td>
                            </tr>
                            @if($notification->read_at)
                            <tr>
                                <th scope="row">Read At</th>
                                <td>
                                    {{ \Carbon\Carbon::parse($notification->read_at)->format('M d, Y H:i:s') }}
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($notification->read_at)->diffForHumans() }}</small>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(isset($data['action_url']) || isset($data['action_text']))
                <div class="mt-4">
                    <h6 class="text-muted mb-3">Action</h6>
                    @if(isset($data['action_url']))
                        <a href="{{ $data['action_url'] }}" class="btn btn-primary">
                            {{ $data['action_text'] ?? 'View Details' }}
                        </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if(!$notification->read_at)
                        <button class="btn btn-success" onclick="markAsRead({{ $notification->id }})">
                            <i class="ti ti-check me-2"></i>Mark as Read
                        </button>
                    @endif
                    
                    <button class="btn btn-danger" onclick="deleteNotification({{ $notification->id }})">
                        <i class="ti ti-trash me-2"></i>Delete Notification
                    </button>
                    
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-2"></i>Back to Notifications
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Raw Data</h5>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3 rounded"><code>{{ json_encode($data, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function markAsRead(notificationId) {
    window.ajaxHelper.post(`/admin/notifications/${notificationId}/mark-read`)
        .then(data => {
            if (data.success) {
                window.showNotification('Notification marked as read', 'success');
                location.reload();
            } else {
                window.showNotification('Failed to mark notification as read: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.showNotification('Error marking notification as read: ' + error.message, 'error');
        });
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        window.ajaxHelper.delete(`/admin/notifications/${notificationId}`)
            .then(data => {
                if (data.success) {
                    window.showNotification('Notification deleted successfully', 'success');
                    window.location.href = '{{ route("admin.notifications.index") }}';
                } else {
                    window.showNotification('Failed to delete notification: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error deleting notification: ' + error.message, 'error');
            });
    }
}
</script>
@endpush
