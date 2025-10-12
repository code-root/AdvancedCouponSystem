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

// Setup real-time session termination listener (works on all pages)
(function setupGlobalSessionListener() {
    const pusherKey = '{{ config("broadcasting.connections.pusher.key") }}';
    if (!pusherKey || pusherKey === '') {
        console.log('Pusher not configured - real-time logout disabled');
        return;
    }
    
    try {
        const pusher = new Pusher(pusherKey, {
            cluster: '{{ config("broadcasting.connections.pusher.options.cluster") }}',
            encrypted: true
        });

        const currentSessionId = '{{ session()->getId() }}';
        const userChannel = pusher.subscribe('private-user.{{ auth()->id() }}');
        const sessionChannel = pusher.subscribe('private-session.' + currentSessionId);

        // Listen for session termination on both channels
        function handleSessionTerminated(data) {
            // Check if this is our current session
            if (data.device_session_id === currentSessionId) {
                console.log('🚨 Current session terminated - forcing logout');
                
                // Show alert
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Session Terminated!',
                        html: `
                            <p>Your session has been terminated from another device.</p>
                            <p class="text-muted">You will be logged out now.</p>
                        `,
                        timer: 3000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    });
                }
                
                // Force logout after 3 seconds
                setTimeout(() => {
                    // Clear storage
                    if (localStorage) localStorage.clear();
                    if (sessionStorage) sessionStorage.clear();
                    
                    // Submit logout form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("logout") }}';
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = '{{ csrf_token() }}';
                    
                    form.appendChild(csrfInput);
                    document.body.appendChild(form);
                    form.submit();
                }, 3000);
            }
        }
        
        userChannel.bind('session.terminated', handleSessionTerminated);
        sessionChannel.bind('session.terminated', handleSessionTerminated);
        
        console.log('✅ Real-time session termination listener active');
    } catch (error) {
        console.error('Pusher setup error:', error);
    }
})();
</script>
@endauth

