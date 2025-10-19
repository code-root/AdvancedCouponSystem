/**
 * Real-time Notifications Manager
 */
class NotificationManager {
    constructor() {
        this.notificationCount = 0;
        this.notifications = [];
        this.isInitialized = false;
        this.init();
    }

    init() {
        if (this.isInitialized) return;
        
        this.setupElements();
        this.setupEventListeners();
        this.loadInitialNotifications();
        this.setupRealTimeUpdates();
        this.isInitialized = true;
    }

    setupElements() {
        this.notificationBell = document.getElementById('notificationDropdown');
        this.notificationCountElement = document.getElementById('notificationCount');
        this.notificationsList = document.getElementById('notificationsList');
        this.notificationDropdown = document.querySelector('.notification-dropdown');
    }

    setupEventListeners() {
        if (this.notificationBell) {
            this.notificationBell.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleDropdown();
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.notificationBell?.contains(e.target) && 
                !this.notificationDropdown?.contains(e.target)) {
                this.closeDropdown();
            }
        });
    }

    loadInitialNotifications() {
        // Load initial notifications via AJAX
        fetch('/api/notifications', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.notifications = data.notifications || [];
                this.updateNotificationCount();
                this.renderNotifications();
            }
        })
        .catch(error => {
            console.error('Failed to load notifications:', error);
        });
    }

    setupRealTimeUpdates() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Laravel Echo not available');
            return;
        }

        // Listen for admin notifications
        if (window.adminId) {
            window.Echo.private(`admin.${window.adminId}`)
                .notification((notification) => {
                    this.addNotification(notification);
                });
        }

        // Listen for user notifications
        if (window.userId) {
            window.Echo.private(`user.${window.userId}`)
                .notification((notification) => {
                    this.addNotification(notification);
                });
        }

        // Listen for subscription updates
        window.Echo.channel('subscription-updates')
            .listen('SubscriptionCreated', (e) => {
                this.addNotification({
                    type: 'subscription_created',
                    title: 'New Subscription',
                    message: e.message,
                    data: e
                });
            })
            .listen('SubscriptionCancelled', (e) => {
                this.addNotification({
                    type: 'subscription_cancelled',
                    title: 'Subscription Cancelled',
                    message: e.message,
                    data: e
                });
            })
            .listen('SubscriptionUpgraded', (e) => {
                this.addNotification({
                    type: 'subscription_upgraded',
                    title: 'Subscription Upgraded',
                    message: e.message,
                    data: e
                });
            });
    }

    addNotification(notification) {
        // Add timestamp if not present
        if (!notification.created_at) {
            notification.created_at = new Date().toISOString();
        }

        // Add to notifications array
        this.notifications.unshift(notification);

        // Keep only last 50 notifications
        if (this.notifications.length > 50) {
            this.notifications = this.notifications.slice(0, 50);
        }

        // Update UI
        this.updateNotificationCount();
        this.renderNotifications();
        this.showNotificationToast(notification);

        // Mark as unread
        this.markAsUnread(notification.id);
    }

    updateNotificationCount() {
        const unreadCount = this.notifications.filter(n => !n.read_at).length;
        this.notificationCount = unreadCount;

        if (this.notificationCountElement) {
            this.notificationCountElement.textContent = unreadCount;
            this.notificationCountElement.style.display = unreadCount > 0 ? 'block' : 'none';
        }
    }

    renderNotifications() {
        if (!this.notificationsList) return;

        if (this.notifications.length === 0) {
            this.notificationsList.innerHTML = `
                <div class="text-center py-3">
                    <i class="ti ti-bell-off fs-3 text-muted"></i>
                    <p class="text-muted mb-0">No notifications</p>
                </div>
            `;
            return;
        }

        const notificationsHtml = this.notifications.slice(0, 10).map(notification => {
            const isUnread = !notification.read_at;
            const timeAgo = this.getTimeAgo(notification.created_at);
            const icon = this.getNotificationIcon(notification.type);

            return `
                <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="${icon} fs-5"></i>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <h6 class="mb-1">${notification.title || 'Notification'}</h6>
                            <p class="mb-1 text-muted">${notification.message}</p>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                        ${isUnread ? '<div class="flex-shrink-0"><span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px;"></span></div>' : ''}
                    </div>
                </div>
            `;
        }).join('');

        this.notificationsList.innerHTML = `
            <div class="notification-header p-3 border-bottom">
                <h6 class="mb-0">Notifications</h6>
                <button class="btn btn-sm btn-outline-primary" onclick="notificationManager.markAllAsRead()">
                    Mark all as read
                </button>
            </div>
            <div class="notification-list">
                ${notificationsHtml}
            </div>
            <div class="notification-footer p-3 border-top text-center">
                <a href="/notifications" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        `;

        // Add click handlers
        this.setupNotificationClickHandlers();
    }

    setupNotificationClickHandlers() {
        const notificationItems = this.notificationsList.querySelectorAll('.notification-item');
        notificationItems.forEach(item => {
            item.addEventListener('click', () => {
                const notificationId = item.dataset.id;
                this.markAsRead(notificationId);
                this.handleNotificationClick(notificationId);
            });
        });
    }

    handleNotificationClick(notificationId) {
        const notification = this.notifications.find(n => n.id == notificationId);
        if (!notification) return;

        // Handle different notification types
        switch (notification.type) {
            case 'subscription_created':
            case 'subscription_cancelled':
            case 'subscription_upgraded':
                window.location.href = '/admin/subscriptions';
                break;
            case 'new_user':
                window.location.href = '/admin/users';
                break;
            case 'system_alert':
                // Show modal or redirect based on data
                break;
            default:
                // Default action
                break;
        }
    }

    showNotificationToast(notification) {
        // Show toast notification
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });

            Toast.fire({
                icon: this.getNotificationIconType(notification.type),
                title: notification.title || 'New Notification',
                text: notification.message
            });
        }
    }

    getNotificationIcon(type) {
        const icons = {
            'subscription_created': 'ti ti-crown text-success',
            'subscription_cancelled': 'ti ti-x-circle text-danger',
            'subscription_upgraded': 'ti ti-trending-up text-info',
            'new_user': 'ti ti-user-plus text-primary',
            'system_alert': 'ti ti-alert-triangle text-warning',
            'default': 'ti ti-bell text-secondary'
        };
        return icons[type] || icons.default;
    }

    getNotificationIconType(type) {
        const types = {
            'subscription_created': 'success',
            'subscription_cancelled': 'error',
            'subscription_upgraded': 'info',
            'new_user': 'success',
            'system_alert': 'warning',
            'default': 'info'
        };
        return types[type] || types.default;
    }

    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d ago`;
        
        return date.toLocaleDateString();
    }

    markAsRead(notificationId) {
        // Update local state
        const notification = this.notifications.find(n => n.id == notificationId);
        if (notification) {
            notification.read_at = new Date().toISOString();
        }

        // Update server
        fetch(`/api/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        }).catch(error => {
            console.error('Failed to mark notification as read:', error);
        });

        // Update UI
        this.updateNotificationCount();
        this.renderNotifications();
    }

    markAllAsRead() {
        // Update local state
        this.notifications.forEach(notification => {
            if (!notification.read_at) {
                notification.read_at = new Date().toISOString();
            }
        });

        // Update server
        fetch('/api/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        }).catch(error => {
            console.error('Failed to mark all notifications as read:', error);
        });

        // Update UI
        this.updateNotificationCount();
        this.renderNotifications();
    }

    markAsUnread(notificationId) {
        const notification = this.notifications.find(n => n.id == notificationId);
        if (notification) {
            notification.read_at = null;
        }
    }

    toggleDropdown() {
        if (this.notificationDropdown) {
            this.notificationDropdown.classList.toggle('show');
        }
    }

    closeDropdown() {
        if (this.notificationDropdown) {
            this.notificationDropdown.classList.remove('show');
        }
    }
}

// Initialize notification manager
const notificationManager = new NotificationManager();

// Export for global access
window.notificationManager = notificationManager;

