/**
 * Subscription Sidebar Enhancement
 * Interactive features for subscription monitoring in sidebar
 */

class SubscriptionSidebar {
    constructor() {
        this.init();
    }

    /**
     * Initialize subscription sidebar features
     */
    init() {
        this.setupStatusWidget();
        this.setupNavigation();
        this.setupRealTimeUpdates();
        this.setupTooltips();
    }

    /**
     * Setup subscription status widget
     */
    setupStatusWidget() {
        const statusWidget = document.querySelector('.subscription-status-widget');
        if (!statusWidget) return;

        // Add click handlers for quick actions
        const quickActions = statusWidget.querySelectorAll('.quick-actions .btn');
        quickActions.forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleQuickAction(e.target);
            });
        });

        // Add hover effects
        statusWidget.addEventListener('mouseenter', () => {
            this.animateStatusWidget(statusWidget, 'enter');
        });

        statusWidget.addEventListener('mouseleave', () => {
            this.animateStatusWidget(statusWidget, 'leave');
        });
    }

    /**
     * Setup navigation enhancements
     */
    setupNavigation() {
        // Add active state management
        const navLinks = document.querySelectorAll('.side-nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                this.handleNavigationClick(e.target);
            });
        });

        // Add badge animations
        const badges = document.querySelectorAll('.badge');
        badges.forEach(badge => {
            if (badge.textContent && !isNaN(badge.textContent)) {
                this.animateBadge(badge);
            }
        });
    }

    /**
     * Setup real-time updates
     */
    setupRealTimeUpdates() {
        // Update subscription status every 30 seconds
        setInterval(() => {
            this.updateSubscriptionStatus();
        }, 30000);

        // Update billing status every 60 seconds
        setInterval(() => {
            this.updateBillingStatus();
        }, 60000);
    }

    /**
     * Setup tooltips for better UX
     */
    setupTooltips() {
        const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        if (typeof bootstrap !== 'undefined') {
            tooltipElements.forEach(element => {
                new bootstrap.Tooltip(element);
            });
        }
    }

    /**
     * Handle quick action clicks
     */
    handleQuickAction(button) {
        const action = button.dataset.action || button.textContent.toLowerCase();
        
        // Add loading state
        this.setButtonLoading(button, true);
        
        // Simulate action processing
        setTimeout(() => {
            this.setButtonLoading(button, false);
            this.showNotification('Action completed successfully!', 'success');
        }, 1000);
    }

    /**
     * Handle navigation clicks
     */
    handleNavigationClick(link) {
        // Add active state
        const parentMenu = link.closest('.collapse');
        if (parentMenu) {
            // Close other menus
            const allMenus = document.querySelectorAll('.collapse');
            allMenus.forEach(menu => {
                if (menu !== parentMenu && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                }
            });
        }
    }

    /**
     * Animate status widget
     */
    animateStatusWidget(widget, action) {
        const card = widget.querySelector('.card');
        if (!card) return;

        if (action === 'enter') {
            card.style.transform = 'translateY(-2px)';
            card.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
        } else {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.05)';
        }
    }

    /**
     * Animate badge
     */
    animateBadge(badge) {
        const count = parseInt(badge.textContent);
        if (count > 0) {
            badge.style.animation = 'pulse 1s ease-in-out';
            setTimeout(() => {
                badge.style.animation = '';
            }, 1000);
        }
    }

    /**
     * Update subscription status
     */
    async updateSubscriptionStatus() {
        try {
            const response = await fetch('/api/subscription/status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateStatusDisplay(data);
            }
        } catch (error) {
            console.warn('Failed to update subscription status:', error);
        }
    }

    /**
     * Update billing status
     */
    async updateBillingStatus() {
        try {
            const response = await fetch('/api/billing/status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateBillingDisplay(data);
            }
        } catch (error) {
            console.warn('Failed to update billing status:', error);
        }
    }

    /**
     * Update status display
     */
    updateStatusDisplay(data) {
        const statusWidget = document.querySelector('.subscription-status-widget');
        if (!statusWidget) return;

        // Update plan name
        const planName = statusWidget.querySelector('h6');
        if (planName && data.plan_name) {
            planName.textContent = data.plan_name;
        }

        // Update status
        const statusText = statusWidget.querySelector('.text-success, .text-info, .text-warning');
        if (statusText && data.status) {
            statusText.className = `text-${data.status === 'active' ? 'success' : data.status === 'trialing' ? 'info' : 'warning'}`;
            statusText.textContent = data.status === 'active' ? 'Active' : data.status === 'trialing' ? 'Trial' : 'Inactive';
        }

        // Update expiration date
        const expirationDate = statusWidget.querySelector('small');
        if (expirationDate && data.expires_at) {
            expirationDate.textContent = `Expires: ${new Date(data.expires_at).toLocaleDateString()}`;
        }
    }

    /**
     * Update billing display
     */
    updateBillingDisplay(data) {
        // Update pending payments badge
        const pendingBadge = document.querySelector('.billing-nav .badge');
        if (pendingBadge && data.pending_payments !== undefined) {
            if (data.pending_payments > 0) {
                pendingBadge.textContent = data.pending_payments;
                pendingBadge.style.display = 'inline-block';
            } else {
                pendingBadge.style.display = 'none';
            }
        }
    }

    /**
     * Set button loading state
     */
    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Loading...';
        } else {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || button.textContent;
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    /**
     * Add status indicator
     */
    addStatusIndicator(element, status) {
        const indicator = document.createElement('span');
        indicator.className = `status-indicator ${status}`;
        element.insertBefore(indicator, element.firstChild);
    }

    /**
     * Update usage statistics
     */
    async updateUsageStats() {
        try {
            const response = await fetch('/api/usage/stats', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateUsageDisplay(data);
            }
        } catch (error) {
            console.warn('Failed to update usage stats:', error);
        }
    }

    /**
     * Update usage display
     */
    updateUsageDisplay(data) {
        // Update usage percentages in sidebar
        const usageElements = document.querySelectorAll('[data-usage-type]');
        usageElements.forEach(element => {
            const type = element.dataset.usageType;
            const percentage = data[type]?.percentage || 0;
            element.style.width = `${Math.min(percentage, 100)}%`;
        });
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + S for subscription page
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                window.location.href = '/subscription';
            }
            
            // Ctrl/Cmd + B for billing page
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                window.location.href = '/subscription/invoices';
            }
        });
    }

    /**
     * Setup responsive behavior
     */
    setupResponsiveBehavior() {
        const handleResize = () => {
            const isMobile = window.innerWidth < 768;
            const statusWidget = document.querySelector('.subscription-status-widget');
            
            if (statusWidget) {
                if (isMobile) {
                    statusWidget.classList.add('mobile-view');
                } else {
                    statusWidget.classList.remove('mobile-view');
                }
            }
        };

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a dashboard page
    if (document.querySelector('.subscription-status-widget') || document.querySelector('.side-nav')) {
        window.subscriptionSidebar = new SubscriptionSidebar();
    }
});

// Export for global access
window.SubscriptionSidebar = SubscriptionSidebar;

