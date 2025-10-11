@vite('resources/js/app.js')

@yield('scripts')

<!-- Global Notification Handler -->
@auth
<script>
function markAllAsReadTopbar() {
    fetch('{{ route("notifications.mark-all-read") }}', {
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

// Poll for new notifications every 30 seconds
setInterval(function() {
    fetch('{{ route("notifications.unread-count") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('notificationBadge');
                const count = document.getElementById('unreadCount');
                
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline';
                    }
                    if (count) {
                        count.textContent = data.count;
                    }
                } else {
                    if (badge) {
                        badge.style.display = 'none';
                    }
                }
            }
        });
}, 30000);

// Send heartbeat every 60 seconds to track online status
setInterval(function() {
    fetch('{{ route("sessions.heartbeat") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    }).catch(error => {
        console.log('Heartbeat failed:', error);
    });
}, 60000); // Every minute
</script>
@endauth

