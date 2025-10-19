/**
 * Notification Module for Admin Panel
 * Toast notifications, real-time alerts, and browser notifications
 */

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.container = null;
        this.settings = {
            position: 'top-right',
            duration: 5000,
            maxNotifications: 5,
            enableSound: true,
            enableBrowserNotifications: false
        };
        this.init();
    }

    /**
     * Initialize notification manager
     */
    init() {
        this.createContainer();
        this.setupEventListeners();
        this.loadSettings();
    }

    /**
     * Create notification container
     */
    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = `notification-container position-fixed`;
        this.container.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            pointer-events: none;
        `;
        document.body.appendChild(this.container);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for custom notification events
        document.addEventListener('notification:show', (e) => {
            this.show(e.detail.message, e.detail.type, e.detail.options);
        });

        // Listen for real-time notifications (if using WebSockets/Pusher)
        if (window.Pusher) {
            this.setupPusherNotifications();
        }

        // Listen for browser visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.enableBrowserNotifications();
            } else {
                this.disableBrowserNotifications();
            }
        });
    }

    /**
     * Setup Pusher notifications
     */
    setupPusherNotifications() {
        const pusher = new Pusher(process.env.MIX_PUSHER_APP_KEY, {
            cluster: process.env.MIX_PUSHER_APP_CLUSTER
        });

        const channel = pusher.subscribe('admin-notifications');
        
        channel.bind('notification', (data) => {
            this.show(data.message, data.type, {
                title: data.title,
                duration: data.duration,
                actions: data.actions
            });
        });
    }

    /**
     * Show notification
     */
    show(message, type = 'info', options = {}) {
        const notification = this.createNotification(message, type, options);
        this.addNotification(notification);
        
        // Play sound if enabled
        if (this.settings.enableSound) {
            this.playSound(type);
        }

        // Show browser notification if enabled and page is hidden
        if (this.settings.enableBrowserNotifications && document.hidden) {
            this.showBrowserNotification(message, type, options);
        }

        return notification;
    }

    /**
     * Create notification element
     */
    createNotification(message, type, options) {
        const notification = document.createElement('div');
        notification.className = `notification alert alert-${this.getAlertClass(type)} alert-dismissible fade show`;
        notification.style.cssText = `
            margin-bottom: 10px;
            pointer-events: auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
            border-left: 4px solid ${this.getBorderColor(type)};
        `;

        const icon = this.getIcon(type);
        const title = options.title ? `<strong>${options.title}</strong><br>` : '';

        notification.innerHTML = `
            <div class="d-flex align-items-start">
                <div class="notification-icon me-2">
                    <i class="${icon}"></i>
                </div>
                <div class="notification-content flex-grow-1">
                    ${title}${message}
                    ${this.createActions(options.actions)}
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            ${this.createProgressBar(options.duration || this.settings.duration)}
        `;

        // Add click handler for actions
        if (options.actions) {
            notification.addEventListener('click', (e) => {
                const action = e.target.closest('[data-action]');
                if (action) {
                    this.handleAction(action.dataset.action, options.actions[action.dataset.action], notification);
                }
            });
        }

        return notification;
    }

    /**
     * Create action buttons
     */
    createActions(actions) {
        if (!actions) return '';

        let html = '<div class="notification-actions mt-2">';
        Object.keys(actions).forEach(key => {
            const action = actions[key];
            html += `<button type="button" class="btn btn-sm btn-outline-light me-1" data-action="${key}">
                ${action.label}
            </button>`;
        });
        html += '</div>';

        return html;
    }

    /**
     * Create progress bar
     */
    createProgressBar(duration) {
        const progressBar = document.createElement('div');
        progressBar.className = 'notification-progress';
        progressBar.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(255, 255, 255, 0.3);
            width: 100%;
            border-radius: 0 0 0.375rem 0.375rem;
        `;

        const progress = document.createElement('div');
        progress.style.cssText = `
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            width: 100%;
            border-radius: 0 0 0.375rem 0.375rem;
            transition: width ${duration}ms linear;
        `;

        progressBar.appendChild(progress);
        return progressBar.outerHTML;
    }

    /**
     * Add notification to container
     */
    addNotification(notification) {
        this.container.appendChild(notification);
        this.notifications.push(notification);

        // Limit number of notifications
        if (this.notifications.length > this.settings.maxNotifications) {
            const oldNotification = this.notifications.shift();
            oldNotification.remove();
        }

        // Auto remove after duration
        const duration = notification.dataset.duration || this.settings.duration;
        setTimeout(() => {
            this.removeNotification(notification);
        }, duration);

        // Animate in
        requestAnimationFrame(() => {
            notification.style.transform = 'translateX(0)';
            notification.style.opacity = '1';
        });
    }

    /**
     * Remove notification
     */
    removeNotification(notification) {
        if (notification.parentNode) {
            notification.style.transform = 'translateX(100%)';
            notification.style.opacity = '0';
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
                
                const index = this.notifications.indexOf(notification);
                if (index > -1) {
                    this.notifications.splice(index, 1);
                }
            }, 300);
        }
    }

    /**
     * Handle action
     */
    handleAction(actionKey, action, notification) {
        if (action.handler && typeof action.handler === 'function') {
            action.handler();
        }

        if (action.close !== false) {
            this.removeNotification(notification);
        }
    }

    /**
     * Show browser notification
     */
    showBrowserNotification(message, type, options) {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            const notification = new Notification(options.title || 'Admin Notification', {
                body: message,
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                tag: 'admin-notification',
                requireInteraction: false
            });

            notification.onclick = () => {
                window.focus();
                notification.close();
            };

            setTimeout(() => notification.close(), options.duration || this.settings.duration);
        }
    }

    /**
     * Request browser notification permission
     */
    async requestBrowserNotificationPermission() {
        if (!('Notification' in window)) return false;

        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            return permission === 'granted';
        }

        return Notification.permission === 'granted';
    }

    /**
     * Enable browser notifications
     */
    enableBrowserNotifications() {
        this.settings.enableBrowserNotifications = true;
        this.saveSettings();
    }

    /**
     * Disable browser notifications
     */
    disableBrowserNotifications() {
        this.settings.enableBrowserNotifications = false;
        this.saveSettings();
    }

    /**
     * Play notification sound
     */
    playSound(type) {
        const audio = new Audio();
        audio.src = `/sounds/notification-${type}.mp3`;
        audio.volume = 0.3;
        audio.play().catch(() => {
            // Ignore errors if audio can't play
        });
    }

    /**
     * Get alert class for type
     */
    getAlertClass(type) {
        const classes = {
            success: 'success',
            error: 'danger',
            warning: 'warning',
            info: 'info',
            danger: 'danger'
        };
        return classes[type] || 'info';
    }

    /**
     * Get border color for type
     */
    getBorderColor(type) {
        const colors = {
            success: '#22c55e',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6',
            danger: '#ef4444'
        };
        return colors[type] || '#3b82f6';
    }

    /**
     * Get icon for type
     */
    getIcon(type) {
        const icons = {
            success: 'ti ti-check-circle',
            error: 'ti ti-x-circle',
            warning: 'ti ti-alert-triangle',
            info: 'ti ti-info-circle',
            danger: 'ti ti-x-circle'
        };
        return icons[type] || 'ti ti-info-circle';
    }

    /**
     * Clear all notifications
     */
    clearAll() {
        this.notifications.forEach(notification => {
            this.removeNotification(notification);
        });
    }

    /**
     * Load settings from localStorage
     */
    loadSettings() {
        const saved = localStorage.getItem('notification-settings');
        if (saved) {
            this.settings = { ...this.settings, ...JSON.parse(saved) };
        }
    }

    /**
     * Save settings to localStorage
     */
    saveSettings() {
        localStorage.setItem('notification-settings', JSON.stringify(this.settings));
    }

    /**
     * Update settings
     */
    updateSettings(newSettings) {
        this.settings = { ...this.settings, ...newSettings };
        this.saveSettings();
    }

    /**
     * Show success notification
     */
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    /**
     * Show error notification
     */
    error(message, options = {}) {
        return this.show(message, 'error', options);
    }

    /**
     * Show warning notification
     */
    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    /**
     * Show info notification
     */
    info(message, options = {}) {
        return this.show(message, 'info', options);
    }
}

// Create global instance
window.notificationManager = new NotificationManager();

// Export for module usage
export default NotificationManager;

