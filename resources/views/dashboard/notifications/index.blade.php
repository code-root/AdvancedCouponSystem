@extends('layouts.vertical', ['title' => 'Notifications'])

@section('content')
    @include('layouts.partials.page-title', ['subtitle' => 'Account', 'title' => 'Notifications'])

    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Notifications ({{ auth()->user()->unreadNotifications->count() }} unread)</h5>
                <div class="d-flex gap-2">
                    @if(auth()->user()->unreadNotifications->count() > 0)
                    <button type="button" class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                        <i class="ti ti-check-all me-1"></i> Mark All as Read
                    </button>
                    @endif
                    <button type="button" class="btn btn-light btn-sm" onclick="clearAll()">
                        <i class="ti ti-trash me-1"></i> Clear All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @forelse(auth()->user()->notifications as $notification)
                    <div class="notification-item d-flex align-items-start p-3 mb-2 rounded {{ $notification->read_at ? 'bg-light-subtle' : 'bg-primary-subtle' }}">
                        <div class="avatar-sm bg-{{ $notification->data['color'] ?? 'primary' }}-subtle rounded me-3">
                            <span class="avatar-title bg-{{ $notification->data['color'] ?? 'primary' }}-subtle text-{{ $notification->data['color'] ?? 'primary' }}">
                                <i class="ti {{ $notification->data['icon'] ?? 'ti-bell' }} fs-20"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $notification->data['title'] ?? 'Notification' }}</h6>
                            <p class="text-muted mb-2">{{ $notification->data['message'] ?? '' }}</p>
                            
                            @if(isset($notification->data['device']))
                            <div class="d-flex gap-3 flex-wrap">
                                <small class="text-muted">
                                    <i class="ti ti-device-desktop me-1"></i>{{ $notification->data['device'] }}
                                </small>
                                @if(isset($notification->data['location']))
                                <small class="text-muted">
                                    <i class="ti ti-map-pin me-1"></i>{{ $notification->data['location'] }}
                                </small>
                                @endif
                                @if(isset($notification->data['ip_address']))
                                <small class="text-muted">
                                    <i class="ti ti-network me-1"></i><code>{{ $notification->data['ip_address'] }}</code>
                                </small>
                                @endif
                            </div>
                            @endif
                            
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="ti ti-clock me-1"></i>{{ $notification->created_at->diffForHumans() }}
                                </small>
                                @if(!$notification->read_at)
                                <span class="badge bg-primary ms-2">New</span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            @if(!$notification->read_at)
                            <button class="btn btn-soft-primary btn-icon btn-sm" onclick="markAsRead('{{ $notification->id }}')" title="Mark as read">
                                <i class="ti ti-check"></i>
                            </button>
                            @endif
                            @if(isset($notification->data['session_id']))
                            <a href="{{ route('sessions.show', $notification->data['session_id']) }}" class="btn btn-soft-info btn-icon btn-sm" title="View session">
                                <i class="ti ti-eye"></i>
                            </a>
                            @endif
                            <button class="btn btn-soft-danger btn-icon btn-sm" onclick="deleteNotification('{{ $notification->id }}')" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="ti ti-bell-off fs-64 text-muted"></i>
                        <h5 class="text-muted mt-3">No Notifications</h5>
                        <p class="text-muted">You're all caught up!</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function markAsRead(notificationId) {
    fetch(`/dashboard/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markAllAsRead() {
    fetch('/dashboard/notifications/mark-all-read', {
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
                text: 'All notifications marked as read',
                timer: 2000
            });
            setTimeout(() => location.reload(), 2000);
        }
    });
}

function deleteNotification(notificationId) {
    Swal.fire({
        title: 'Delete Notification?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f1556c',
        confirmButtonText: 'Yes, Delete!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/dashboard/notifications/${notificationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    });
}

function clearAll() {
    Swal.fire({
        title: 'Clear All Notifications?',
        text: 'This will delete all your notifications',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f1556c',
        confirmButtonText: 'Yes, Clear All!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/dashboard/notifications/clear-all', {
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
                        text: 'All notifications cleared',
                        timer: 2000
                    });
                    setTimeout(() => location.reload(), 2000);
                }
            });
        }
    });
}
</script>
@endsection

