@extends('admin.layouts.app')

@section('admin-content')
<div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-18 fw-semibold mb-0">Notifications</h4>
        <p class="text-muted mb-0">Manage system notifications and alerts</p>
    </div>
    <div class="text-end">
        <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active">Notifications</li>
        </ol>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 text-center mb-4">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Total Notifications</h5>
                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_notifications'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Unread</h5>
                <h3 class="mb-0 fw-bold text-warning">{{ $stats['unread_notifications'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">Today</h5>
                <h3 class="mb-0 fw-bold text-info">{{ $stats['today_notifications'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="text-muted fs-13 text-uppercase">This Week</h5>
                <h3 class="mb-0 fw-bold text-success">{{ $stats['week_notifications'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.notifications.index') }}" class="row g-3">
            <div class="col-md-2">
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">All Types</option>
                    <option value="subscription" {{ request('type') == 'subscription' ? 'selected' : '' }}>Subscription</option>
                    <option value="user" {{ request('type') == 'user' ? 'selected' : '' }}>User</option>
                    <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>System</option>
                    <option value="security" {{ request('type') == 'security' ? 'selected' : '' }}>Security</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="priority" class="form-label">Priority</label>
                <select class="form-select" id="priority" name="priority">
                    <option value="">All Priorities</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Notifications Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Notifications</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-success btn-sm" onclick="markAllAsRead()">
                    <i class="ti ti-check me-1"></i>Mark All Read
                </button>
                <button class="btn btn-danger btn-sm" onclick="clearAllNotifications()">
                    <i class="ti ti-trash me-1"></i>Clear All
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-nowrap" id="notificationsTable">
                <thead class="table-light">
                    <tr>
                        <th>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                    <tr class="{{ $notification->read_at ? '' : 'table-warning' }}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input notification-checkbox" type="checkbox" 
                                       value="{{ $notification->id }}">
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $notification->data['type'] == 'subscription' ? 'success' : ($notification->data['type'] == 'user' ? 'info' : ($notification->data['type'] == 'system' ? 'primary' : 'danger')) }}-subtle text-{{ $notification->data['type'] == 'subscription' ? 'success' : ($notification->data['type'] == 'user' ? 'info' : ($notification->data['type'] == 'system' ? 'primary' : 'danger')) }}">
                                {{ ucfirst($notification->data['type'] ?? 'General') }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm bg-{{ $notification->data['color'] ?? 'primary' }}-subtle rounded me-3">
                                    <span class="avatar-title bg-{{ $notification->data['color'] ?? 'primary' }}-subtle text-{{ $notification->data['color'] ?? 'primary' }}">
                                        <i class="ti {{ $notification->data['icon'] ?? 'ti-bell' }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                                    <p class="text-muted mb-0">{{ Str::limit($notification->data['message'] ?? '', 100) }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $notification->data['priority'] == 'high' ? 'danger' : ($notification->data['priority'] == 'medium' ? 'warning' : 'secondary') }}-subtle text-{{ $notification->data['priority'] == 'high' ? 'danger' : ($notification->data['priority'] == 'medium' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($notification->data['priority'] ?? 'Low') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $notification->read_at ? 'success' : 'warning' }}-subtle text-{{ $notification->read_at ? 'success' : 'warning' }}">
                                {{ $notification->read_at ? 'Read' : 'Unread' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-muted">{{ $notification->created_at->format('M d, Y H:i') }}</span>
                            <br><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" 
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item" onclick="viewNotification({{ $notification->id }})">
                                            <i class="ti ti-eye me-2"></i>View Details
                                        </button>
                                    </li>
                                    @if(!$notification->read_at)
                                    <li>
                                        <button class="dropdown-item" onclick="markAsRead({{ $notification->id }})">
                                            <i class="ti ti-check me-2"></i>Mark as Read
                                        </button>
                                    </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-danger" onclick="deleteNotification({{ $notification->id }})">
                                            <i class="ti ti-trash me-2"></i>Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="ti ti-bell-off fs-48 mb-3"></i>
                                <h5>No Notifications Found</h5>
                                <p>No notifications match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($notifications->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Notification Details Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="notificationContent">
                    <!-- Notification content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="markAsReadBtn" onclick="markAsReadFromModal()">Mark as Read</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentNotificationId = null;

// Wait for jQuery to be available
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    // Initialize DataTable
    $('#notificationsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[5, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 6] }
        ],
        language: {
            search: "",
            searchPlaceholder: "Search notifications...",
            infoFiltered: ""
        }
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.notification-checkbox').prop('checked', this.checked);
    });

    // Individual checkbox change
    $('.notification-checkbox').on('change', function() {
        if (!this.checked) {
            $('#selectAll').prop('checked', false);
        }
    });
});

function viewNotification(notificationId) {
    currentNotificationId = notificationId;
    
    // This would typically make an AJAX call to get notification details
    document.getElementById('notificationContent').innerHTML = `
        <div class="text-center py-4">
            <i class="ti ti-loader fs-48 text-muted mb-3"></i>
            <p class="text-muted">Loading notification details...</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    modal.show();
    
    // Simulate loading notification details
    setTimeout(() => {
        document.getElementById('notificationContent').innerHTML = `
            <div class="notification-details">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-sm bg-primary-subtle rounded me-3">
                        <span class="avatar-title bg-primary-subtle text-primary">
                            <i class="ti ti-bell"></i>
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-1">Notification Title</h5>
                        <small class="text-muted">Created 2 hours ago</small>
                    </div>
                </div>
                <div class="mb-3">
                    <p>This is the detailed message content of the notification. It provides more information about the event that triggered this notification.</p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Type:</strong> System<br>
                        <strong>Priority:</strong> High<br>
                        <strong>Status:</strong> Unread
                    </div>
                    <div class="col-md-6">
                        <strong>Created:</strong> 2 hours ago<br>
                        <strong>ID:</strong> ${notificationId}
                    </div>
                </div>
            </div>
        `;
    }, 1000);
}

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

function markAsReadFromModal() {
    if (currentNotificationId) {
        markAsRead(currentNotificationId);
        const modal = bootstrap.Modal.getInstance(document.getElementById('notificationModal'));
        modal.hide();
    }
}

function markAllAsRead() {
    if (confirm('Are you sure you want to mark all notifications as read?')) {
        window.ajaxHelper.post('/admin/notifications/mark-all-read')
            .then(data => {
                if (data.success) {
                    window.showNotification('All notifications marked as read', 'success');
                    location.reload();
                } else {
                    window.showNotification('Failed to mark all notifications as read: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error marking all notifications as read: ' + error.message, 'error');
            });
    }
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        window.ajaxHelper.post(`/admin/notifications/${notificationId}/delete`)
            .then(data => {
                if (data.success) {
                    window.showNotification('Notification deleted successfully', 'success');
                    location.reload();
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

function clearAllNotifications() {
    if (confirm('Are you sure you want to clear all notifications? This action cannot be undone.')) {
        window.ajaxHelper.post('/admin/notifications/clear-all')
            .then(data => {
                if (data.success) {
                    window.showNotification('All notifications cleared successfully', 'success');
                    location.reload();
                } else {
                    window.showNotification('Failed to clear all notifications: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showNotification('Error clearing all notifications: ' + error.message, 'error');
            });
    }
}
</script>
@endpush

