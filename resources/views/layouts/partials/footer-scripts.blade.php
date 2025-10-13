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
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const pusherKey = '{{ config("broadcasting.connections.pusher.key") }}';
        const pusherCluster = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
        
        if (!pusherKey || pusherKey === '') {
            console.log('Pusher not configured - real-time logout disabled');
            return;
        }
        
        try {
            const currentSessionId = '{{ session()->getId() }}';
            
            // Initialize Pusher directly (more reliable)
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
            
            // Subscribe to channels
            const userChannel = pusher.subscribe('private-user.{{ auth()->id() }}');
            const sessionChannel = pusher.subscribe('private-session.' + currentSessionId);
            
            // Handle session termination
            function handleSessionTerminated(data) {
                console.log('🚨 Session terminated event:', data);
                
                // Check if this is our current session
                if (data.device_session_id === currentSessionId) {
                    console.log('🔴 Current session terminated - forcing logout');
                    
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
                        // Use fetch API for logout (more reliable than form submit)
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
                            // Force redirect anyway
                            window.location.href = '{{ route("login") }}';
                        });
                    }, 3000);
                }
            }
            
            // Listen on both channels
            userChannel.bind('session.terminated', handleSessionTerminated);
            sessionChannel.bind('session.terminated', handleSessionTerminated);
            
            // Connection monitoring
            pusher.connection.bind('connected', function() {
                console.log('✅ Pusher connected - Real-time logout active');
            });
            
            pusher.connection.bind('error', function(err) {
                console.error('❌ Pusher connection error:', err);
            });
            
        } catch (error) {
            console.error('❌ Pusher setup error:', error);
        }
    }, 1000); // Wait 1 second for Pusher library to load
});
</script>
@endauth

