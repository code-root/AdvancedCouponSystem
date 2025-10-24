/**
 * Admin Dashboard General Scripts
 * Optimized for performance and reduced resource consumption
 */

class AdminDashboardManager {
    constructor() {
        this.csrfToken = this.getCsrfToken();
        this.init();
    }

    /**
     * Initialize the admin dashboard manager
     */
    init() {
        this.bindEvents();
        this.initializeComponents();
        this.setupRealTimeUpdates();
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : null;
    }

    /**
     * Bind all event listeners with delegation for better performance
     */
    bindEvents() {
        // Global click events
        document.addEventListener('click', (e) => {
            // Bulk actions
            if (e.target.matches('[data-bulk-action]')) {
                e.preventDefault();
                this.handleBulkAction(e.target.dataset.bulkAction);
            }
            
            // Quick actions
            if (e.target.matches('[data-quick-action]')) {
                e.preventDefault();
                this.handleQuickAction(e.target.dataset.quickAction, e.target.dataset.id);
            }
            
            // Export actions
            if (e.target.matches('[data-export]')) {
                e.preventDefault();
                this.handleExport(e.target.dataset.export);
            }
            
            // Delete confirmations
            if (e.target.matches('[data-delete]')) {
                e.preventDefault();
                this.handleDelete(e.target.dataset.delete, e.target.dataset.id);
            }
        });

        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('[data-ajax-form]')) {
                e.preventDefault();
                this.handleAjaxForm(e.target);
            }
        });

        // Search with debouncing
        const searchInputs = document.querySelectorAll('[data-search]');
        searchInputs.forEach(input => {
            let timeoutId;
            input.addEventListener('input', (e) => {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => {
                    this.performSearch(e.target.value, e.target.dataset.search);
                }, 300);
            });
        });
    }

    /**
     * Initialize dashboard components
     */
    initializeComponents() {
        this.initializeCharts();
        this.initializeDataTables();
        this.initializeTooltips();
        this.initializeModals();
    }

    /**
     * Initialize charts with lazy loading
     */
    initializeCharts() {
        const chartElements = document.querySelectorAll('[data-chart]');
        
        // Use Intersection Observer for lazy loading
        if ('IntersectionObserver' in window) {
            const chartObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadChart(entry.target);
                        chartObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            chartElements.forEach(chart => {
                chartObserver.observe(chart);
            });
        } else {
            // Fallback for older browsers
            chartElements.forEach(chart => {
                this.loadChart(chart);
            });
        }
    }

    /**
     * Load individual chart
     */
    loadChart(chartElement) {
        const chartType = chartElement.dataset.chart;
        const chartData = JSON.parse(chartElement.dataset.chartData || '{}');
        
        // Initialize chart based on type
        switch (chartType) {
            case 'line':
                this.initializeLineChart(chartElement, chartData);
                break;
            case 'bar':
                this.initializeBarChart(chartElement, chartData);
                break;
            case 'pie':
                this.initializePieChart(chartElement, chartData);
                break;
            case 'doughnut':
                this.initializeDoughnutChart(chartElement, chartData);
                break;
        }
    }

    /**
     * Initialize line chart
     */
    initializeLineChart(element, data) {
        if (typeof Chart !== 'undefined') {
            new Chart(element, {
                type: 'line',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    /**
     * Initialize bar chart
     */
    initializeBarChart(element, data) {
        if (typeof Chart !== 'undefined') {
            new Chart(element, {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }

    /**
     * Initialize pie chart
     */
    initializePieChart(element, data) {
        if (typeof Chart !== 'undefined') {
            new Chart(element, {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }
    }

    /**
     * Initialize doughnut chart
     */
    initializeDoughnutChart(element, data) {
        if (typeof Chart !== 'undefined') {
            new Chart(element, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
        }
    }

    /**
     * Initialize DataTables with optimized settings
     */
    initializeDataTables() {
        if (typeof $ !== 'undefined' && $.fn.DataTable) {
            const tables = document.querySelectorAll('[data-datatable]');
            
            tables.forEach(table => {
                const options = JSON.parse(table.dataset.datatableOptions || '{}');
                const defaultOptions = {
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']],
                    language: {
                        search: "",
                        searchPlaceholder: "Search...",
                        infoFiltered: ""
                    },
                    deferRender: true,
                    processing: true,
                    stateSave: true,
                    stateDuration: 60 * 60 * 24, // 24 hours
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                         '<"row"<"col-sm-12"tr>>' +
                         '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
                };
                
                $(table).DataTable({ ...defaultOptions, ...options });
            });
        }
    }

    /**
     * Initialize tooltips
     */
    initializeTooltips() {
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    /**
     * Initialize modals
     */
    initializeModals() {
        if (typeof bootstrap !== 'undefined') {
            const modalElements = document.querySelectorAll('.modal');
            modalElements.forEach(modal => {
                modal.addEventListener('shown.bs.modal', () => {
                    // Focus first input in modal
                    const firstInput = modal.querySelector('input, select, textarea');
                    if (firstInput) {
                        firstInput.focus();
                    }
                });
            });
        }
    }

    /**
     * Setup real-time updates
     */
    setupRealTimeUpdates() {
        // Update statistics every 2 minutes
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                this.updateDashboardStats();
            }
        }, 2 * 60 * 1000);

        // Update notifications every 30 seconds
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                this.updateNotifications();
            }
        }, 30 * 1000);
    }

    /**
     * Update dashboard statistics
     */
    async updateDashboardStats() {
        try {
            const response = await fetch('/admin/dashboard/stats', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateStatsCards(data);
            }
        } catch (error) {
            console.warn('Failed to update dashboard stats:', error);
        }
    }

    /**
     * Update statistics cards
     */
    updateStatsCards(data) {
        Object.entries(data).forEach(([key, value]) => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                const currentValue = parseInt(element.textContent.replace(/[^\d]/g, '')) || 0;
                if (currentValue !== value) {
                    this.animateNumber(element, currentValue, value);
                }
            }
        });
    }

    /**
     * Animate number change
     */
    animateNumber(element, from, to) {
        const duration = 1000; // 1 second
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = Math.round(from + (to - from) * progress);
            element.textContent = currentValue.toLocaleString();
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    }

    /**
     * Update notifications
     */
    async updateNotifications() {
        try {
            const response = await fetch('/admin/notifications/unread-count', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateNotificationBadge(data.count);
            }
        } catch (error) {
            console.warn('Failed to update notifications:', error);
        }
    }

    /**
     * Update notification badge
     */
    updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * Handle bulk actions
     */
    async handleBulkAction(action) {
        const selectedItems = this.getSelectedItems();
        
        if (selectedItems.length === 0) {
            this.showAlert('warning', 'Please select at least one item.');
            return;
        }

        if (!confirm(`Are you sure you want to ${action} ${selectedItems.length} item(s)?`)) {
            return;
        }

        try {
            const response = await fetch('/admin/bulk-action', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    items: selectedItems
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message);
                this.refreshCurrentPage();
            } else {
                this.showAlert('error', data.message);
            }
        } catch (error) {
            this.showAlert('error', `An error occurred while performing bulk ${action}.`);
            console.error('Bulk action error:', error);
        }
    }

    /**
     * Handle quick actions
     */
    async handleQuickAction(action, id) {
        try {
            const response = await fetch(`/admin/quick-action/${action}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message);
                this.refreshCurrentPage();
            } else {
                this.showAlert('error', data.message);
            }
        } catch (error) {
            this.showAlert('error', `An error occurred while performing ${action}.`);
            console.error('Quick action error:', error);
        }
    }

    /**
     * Handle export actions
     */
    handleExport(format) {
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('export', format);
        window.open(currentUrl.toString(), '_blank');
    }

    /**
     * Handle delete actions
     */
    async handleDelete(type, id) {
        if (!confirm(`Are you sure you want to delete this ${type}?`)) {
            return;
        }

        try {
            const response = await fetch(`/admin/${type}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message);
                this.refreshCurrentPage();
            } else {
                this.showAlert('error', data.message);
            }
        } catch (error) {
            this.showAlert('error', `An error occurred while deleting the ${type}.`);
            console.error('Delete error:', error);
        }
    }

    /**
     * Handle AJAX form submissions
     */
    async handleAjaxForm(form) {
        const formData = new FormData(form);
        const url = form.action || window.location.href;
        
        try {
            const response = await fetch(url, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    this.refreshCurrentPage();
                }
            } else {
                this.showAlert('error', data.message);
                if (data.errors) {
                    this.displayFormErrors(form, data.errors);
                }
            }
        } catch (error) {
            this.showAlert('error', 'An error occurred while processing the form.');
            console.error('Form submission error:', error);
        }
    }

    /**
     * Display form validation errors
     */
    displayFormErrors(form, errors) {
        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(feedback => {
            feedback.remove();
        });

        // Display new errors
        Object.entries(errors).forEach(([field, messages]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = messages[0];
                input.parentNode.appendChild(feedback);
            }
        });
    }

    /**
     * Perform search with debouncing
     */
    async performSearch(query, target) {
        if (query.length < 2) return;

        try {
            const response = await fetch(`/admin/search/${target}?q=${encodeURIComponent(query)}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateSearchResults(target, data.results);
            }
        } catch (error) {
            console.warn('Search failed:', error);
        }
    }

    /**
     * Update search results
     */
    updateSearchResults(target, results) {
        const container = document.querySelector(`[data-search-results="${target}"]`);
        if (container) {
            container.innerHTML = results.map(result => this.renderSearchResult(result)).join('');
        }
    }

    /**
     * Render search result item
     */
    renderSearchResult(result) {
        return `
            <div class="search-result-item p-2 border-bottom">
                <h6 class="mb-1">${result.title}</h6>
                <small class="text-muted">${result.description}</small>
            </div>
        `;
    }

    /**
     * Get selected items for bulk actions
     */
    getSelectedItems() {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }

    /**
     * Show alert message
     */
    showAlert(type, message) {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        const alertId = `alert-${Date.now()}`;
        
        const alertHtml = `
            <div id="${alertId}" class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        const container = document.querySelector('.admin-content') || document.body;
        container.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    /**
     * Refresh current page
     */
    refreshCurrentPage() {
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on an admin page
    if (document.querySelector('.admin-content') || document.querySelector('[data-admin]')) {
        window.adminDashboardManager = new AdminDashboardManager();
    }
});

// Export for global access
window.AdminDashboardManager = AdminDashboardManager;

