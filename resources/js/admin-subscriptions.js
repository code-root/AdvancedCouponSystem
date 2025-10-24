/**
 * Admin Subscriptions Management Script
 * Optimized for performance and reduced resource consumption
 */

class AdminSubscriptionsManager {
    constructor() {
        this.currentSubscriptionId = null;
        this.csrfToken = this.getCsrfToken();
        this.init();
    }

    /**
     * Initialize the admin subscriptions manager
     */
    init() {
        this.bindEvents();
        this.initializeDataTable();
        this.setupAutoRefresh();
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Form submissions with event delegation for better performance
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'cancelForm') {
                e.preventDefault();
                this.handleCancelSubscription();
            } else if (e.target.id === 'upgradeForm') {
                e.preventDefault();
                this.handleUpgradeSubscription();
            } else if (e.target.id === 'extendForm') {
                e.preventDefault();
                this.handleExtendSubscription();
            }
        });

        // Click events with delegation
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="cancel"]')) {
                e.preventDefault();
                this.cancelSubscription(e.target.dataset.id);
            } else if (e.target.matches('[data-action="upgrade"]')) {
                e.preventDefault();
                this.upgradeSubscription(e.target.dataset.id);
            } else if (e.target.matches('[data-action="extend"]')) {
                e.preventDefault();
                this.extendSubscription(e.target.dataset.id);
            } else if (e.target.matches('[data-action="manual-activate"]')) {
                e.preventDefault();
                this.manualActivate(e.target.dataset.id);
            } else if (e.target.matches('[data-action="export"]')) {
                e.preventDefault();
                this.exportSubscriptions();
            }
        });
    }

    /**
     * Initialize DataTable with optimized settings
     */
    initializeDataTable() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            $('#subscriptionsTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: [8] }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search subscriptions...",
                    infoFiltered: ""
                },
                // Performance optimizations
                deferRender: true,
                processing: true,
                stateSave: true,
                stateDuration: 60 * 60 * 24, // 24 hours
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                // Custom search for better performance
                search: {
                    smart: false,
                    regex: false
                }
            });
        }
    }

    /**
     * Setup auto-refresh for real-time updates
     */
    setupAutoRefresh() {
        // Refresh every 5 minutes if page is visible
        if (document.visibilityState === 'visible') {
            setInterval(() => {
                if (document.visibilityState === 'visible') {
                    this.refreshStatistics();
                }
            }, 5 * 60 * 1000); // 5 minutes
        }
    }

    /**
     * Refresh statistics without full page reload
     */
    async refreshStatistics() {
        try {
            const response = await fetch('/admin/legacy/subscriptions/statistics', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateStatisticsCards(data);
            }
        } catch (error) {
            console.warn('Failed to refresh statistics:', error);
        }
    }

    /**
     * Update statistics cards with new data
     */
    updateStatisticsCards(data) {
        const updates = {
            'total_subscriptions': data.total_subscriptions || 0,
            'active_subscriptions': data.active_subscriptions || 0,
            'trial_subscriptions': data.trial_subscriptions || 0,
            'monthly_revenue': data.monthly_revenue || 0
        };

        Object.entries(updates).forEach(([key, value]) => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                element.textContent = key === 'monthly_revenue' ? 
                    `$${value.toLocaleString('en-US', { minimumFractionDigits: 2 })}` : 
                    value;
            }
        });
    }

    /**
     * Cancel subscription
     */
    cancelSubscription(id) {
        this.currentSubscriptionId = id;
        this.showModal('cancelModal');
    }

    /**
     * Upgrade subscription
     */
    upgradeSubscription(id) {
        this.currentSubscriptionId = id;
        this.showModal('upgradeModal');
    }

    /**
     * Extend subscription
     */
    extendSubscription(id) {
        this.currentSubscriptionId = id;
        this.showModal('extendModal');
    }

    /**
     * Manual activate subscription
     */
    async manualActivate(id) {
        if (confirm('Are you sure you want to manually activate this subscription?')) {
            try {
                const response = await fetch(`/admin/legacy/subscriptions/${id}/manual-activate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    this.showAlert('success', data.message);
                    this.refreshPage();
                } else {
                    this.showAlert('error', data.message);
                }
            } catch (error) {
                this.showAlert('error', 'An error occurred while activating the subscription.');
                console.error('Manual activate error:', error);
            }
        }
    }

    /**
     * Handle cancel subscription form submission
     */
    async handleCancelSubscription() {
        try {
            const form = document.getElementById('cancelForm');
            const formData = new FormData(form);
            formData.append('_token', this.csrfToken);

            const response = await fetch(`/admin/legacy/subscriptions/${this.currentSubscriptionId}/cancel`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message);
                this.hideModal('cancelModal');
                this.refreshPage();
            } else {
                this.showAlert('error', data.message);
            }
        } catch (error) {
            this.showAlert('error', 'An error occurred while cancelling the subscription.');
            console.error('Cancel subscription error:', error);
        }
    }

    /**
     * Handle upgrade subscription form submission
     */
    async handleUpgradeSubscription() {
        try {
            const form = document.getElementById('upgradeForm');
            const formData = new FormData(form);
            formData.append('_token', this.csrfToken);

            const response = await fetch(`/admin/legacy/subscriptions/${this.currentSubscriptionId}/upgrade`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message);
                this.hideModal('upgradeModal');
                this.refreshPage();
            } else {
                this.showAlert('error', data.message);
            }
        } catch (error) {
            this.showAlert('error', 'An error occurred while upgrading the subscription.');
            console.error('Upgrade subscription error:', error);
        }
    }

    /**
     * Handle extend subscription form submission
     */
    async handleExtendSubscription() {
        try {
            const form = document.getElementById('extendForm');
            const formData = new FormData(form);
            formData.append('_token', this.csrfToken);

            const response = await fetch(`/admin/legacy/subscriptions/${this.currentSubscriptionId}/extend`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message);
                this.hideModal('extendModal');
                this.refreshPage();
            } else {
                this.showAlert('error', data.message);
            }
        } catch (error) {
            this.showAlert('error', 'An error occurred while extending the subscription.');
            console.error('Extend subscription error:', error);
        }
    }

    /**
     * Export subscriptions
     */
    exportSubscriptions() {
        const form = document.querySelector('form[method="GET"]');
        if (form) {
            const formData = new FormData(form);
            formData.append('export', '1');
            
            const params = new URLSearchParams(formData);
            window.open(`/admin/legacy/subscriptions/export?${params.toString()}`, '_blank');
        } else {
            window.open('/admin/legacy/subscriptions/export', '_blank');
        }
    }

    /**
     * Show modal with animation
     */
    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
        }
    }

    /**
     * Hide modal with animation
     */
    hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }

    /**
     * Show alert message with auto-dismiss
     */
    showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertId = `alert-${Date.now()}`;
        
        const alertHtml = `
            <div id="${alertId}" class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert at the top of the content
        const content = document.querySelector('.admin-content');
        if (content) {
            content.insertAdjacentHTML('afterbegin', alertHtml);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    }

    /**
     * Refresh page with loading indicator
     */
    refreshPage() {
        // Show loading indicator
        const loadingHtml = `
            <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                 style="background: rgba(255,255,255,0.8); z-index: 9999;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', loadingHtml);
        
        // Reload page after short delay
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    /**
     * Bulk actions for subscriptions
     */
    async bulkAction(action, subscriptionIds) {
        if (!subscriptionIds.length) {
            this.showAlert('error', 'Please select at least one subscription.');
            return;
        }

        if (!confirm(`Are you sure you want to ${action} ${subscriptionIds.length} subscription(s)?`)) {
            return;
        }

        try {
            const response = await fetch('/admin/legacy/subscriptions/bulk-action', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    subscription_ids: subscriptionIds
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message);
                this.refreshPage();
            } else {
                this.showAlert('error', data.message);
            }
        } catch (error) {
            this.showAlert('error', `An error occurred while performing bulk ${action}.`);
            console.error('Bulk action error:', error);
        }
    }

    /**
     * Advanced filtering with debouncing
     */
    setupAdvancedFiltering() {
        const searchInput = document.getElementById('search');
        if (searchInput) {
            let timeoutId;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 300); // 300ms debounce
            });
        }
    }

    /**
     * Perform search with AJAX
     */
    async performSearch(query) {
        try {
            const response = await fetch(`/admin/legacy/subscriptions/search?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateTable(data.subscriptions);
            }
        } catch (error) {
            console.warn('Search failed:', error);
        }
    }

    /**
     * Update table with new data
     */
    updateTable(subscriptions) {
        const tbody = document.querySelector('#subscriptionsTable tbody');
        if (tbody) {
            tbody.innerHTML = subscriptions.map(sub => this.renderSubscriptionRow(sub)).join('');
        }
    }

    /**
     * Render subscription row HTML
     */
    renderSubscriptionRow(subscription) {
        const statusColors = {
            'active': 'success',
            'trialing': 'info',
            'canceled': 'warning',
            'expired': 'danger',
            'past_due': 'secondary'
        };
        
        const color = statusColors[subscription.status] || 'secondary';
        
        return `
            <tr>
                <td><span class="fw-semibold">#${subscription.id}</span></td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-xs">
                                <div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                                    ${subscription.user.name ? subscription.user.name.charAt(0) : 'U'}
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">${subscription.user.name || 'N/A'}</h6>
                            <small class="text-muted">${subscription.user.email || 'N/A'}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-xs">
                                <div class="avatar-title rounded-circle bg-info-subtle text-info">
                                    ${subscription.plan.name ? subscription.plan.name.charAt(0) : 'P'}
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">${subscription.plan.name || 'N/A'}</h6>
                            <small class="text-muted">$${subscription.plan.price || 0}</small>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-${color}-subtle text-${color}">${subscription.status}</span></td>
                <td><span class="badge bg-primary-subtle text-primary">${subscription.gateway || 'Manual'}</span></td>
                <td>
                    <span class="fw-semibold">${subscription.starts_at || 'N/A'}</span>
                    ${subscription.starts_at ? `<br><small class="text-muted">${subscription.starts_at_human}</small>` : ''}
                </td>
                <td>
                    <span class="fw-semibold">${subscription.ends_at || 'N/A'}</span>
                    ${subscription.ends_at ? `<br><small class="text-muted">${subscription.ends_at_human}</small>` : ''}
                </td>
                <td>
                    <span class="fw-semibold">${subscription.created_at}</span>
                    <br><small class="text-muted">${subscription.created_at_human}</small>
                </td>
                <td>
                    <div class="dropdown">
                        <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="ti ti-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/legacy/subscriptions/${subscription.id}"><i class="ti ti-eye me-2"></i>View Details</a></li>
                            <li><a class="dropdown-item" href="/admin/legacy/subscriptions/${subscription.id}/edit"><i class="ti ti-edit me-2"></i>Edit</a></li>
                            <li><hr class="dropdown-divider"></li>
                            ${subscription.status !== 'canceled' ? `<li><button class="dropdown-item text-warning" data-action="cancel" data-id="${subscription.id}"><i class="ti ti-x me-2"></i>Cancel</button></li>` : ''}
                            ${subscription.status === 'trialing' ? `<li><button class="dropdown-item text-success" data-action="manual-activate" data-id="${subscription.id}"><i class="ti ti-hand-click me-2"></i>Manual Activate</button></li>` : ''}
                            <li><button class="dropdown-item text-info" data-action="upgrade" data-id="${subscription.id}"><i class="ti ti-arrow-up me-2"></i>Upgrade</button></li>
                            <li><button class="dropdown-item text-primary" data-action="extend" data-id="${subscription.id}"><i class="ti ti-calendar-plus me-2"></i>Extend</button></li>
                        </ul>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the admin subscriptions page
    if (document.querySelector('#subscriptionsTable')) {
        window.adminSubscriptionsManager = new AdminSubscriptionsManager();
    }
});

// Global functions for backward compatibility
window.cancelSubscription = function(id) {
    if (window.adminSubscriptionsManager) {
        window.adminSubscriptionsManager.cancelSubscription(id);
    }
};

window.upgradeSubscription = function(id) {
    if (window.adminSubscriptionsManager) {
        window.adminSubscriptionsManager.upgradeSubscription(id);
    }
};

window.extendSubscription = function(id) {
    if (window.adminSubscriptionsManager) {
        window.adminSubscriptionsManager.extendSubscription(id);
    }
};

window.manualActivate = function(id) {
    if (window.adminSubscriptionsManager) {
        window.adminSubscriptionsManager.manualActivate(id);
    }
};

window.exportSubscriptions = function() {
    if (window.adminSubscriptionsManager) {
        window.adminSubscriptionsManager.exportSubscriptions();
    }
};

// Export for global access
window.AdminSubscriptionsManager = AdminSubscriptionsManager;

