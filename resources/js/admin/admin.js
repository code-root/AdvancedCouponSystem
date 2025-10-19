/**
 * Main Admin JavaScript File
 * Initializes all admin modules and provides global functionality
 */

// Import modules
import AjaxHelper from './ajax-helper.js';
import FormHandler from './form-handler.js';
import DataTableHandler from './datatable-handler.js';
import NotificationManager from './notifications.js';

class AdminApp {
    constructor() {
        this.modules = {
            ajax: new AjaxHelper(),
            forms: new FormHandler(),
            datatables: new DataTableHandler(),
            notifications: new NotificationManager()
        };
        
        this.init();
    }

    /**
     * Initialize admin app
     */
    init() {
        this.setupGlobalEventListeners();
        this.initializeComponents();
        this.setupKeyboardShortcuts();
        this.setupAutoRefresh();
    }

    /**
     * Setup global event listeners
     */
    setupGlobalEventListeners() {
        // Handle AJAX errors globally
        document.addEventListener('ajax:error', (e) => {
            this.modules.notifications.error(e.detail.message || 'An error occurred');
        });

        // Handle AJAX success globally
        document.addEventListener('ajax:success', (e) => {
            if (e.detail.message) {
                this.modules.notifications.success(e.detail.message);
            }
        });

        // Handle form submissions
        document.addEventListener('form:success', (e) => {
            const { response } = e.detail;
            if (response.message) {
                this.modules.notifications.success(response.message);
            }
        });

        // Handle DataTable events
        document.addEventListener('datatable:init', (e) => {
            console.log('DataTable initialized:', e.detail.tableId);
        });

        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseAutoRefresh();
            } else {
                this.resumeAutoRefresh();
            }
        });
    }

    /**
     * Initialize components
     */
    initializeComponents() {
        // Initialize tooltips
        this.initializeTooltips();

        // Initialize popovers
        this.initializePopovers();

        // Initialize modals
        this.initializeModals();

        // Initialize dropdowns
        this.initializeDropdowns();

        // Initialize charts
        this.initializeCharts();

        // Initialize date pickers
        this.initializeDatePickers();

        // Initialize file uploads
        this.initializeFileUploads();
    }

    /**
     * Initialize tooltips
     */
    initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    /**
     * Initialize popovers
     */
    initializePopovers() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    /**
     * Initialize modals
     */
    initializeModals() {
        // Handle modal events
        document.addEventListener('show.bs.modal', (e) => {
            const modal = e.target;
            this.loadModalContent(modal);
        });

        document.addEventListener('hidden.bs.modal', (e) => {
            const modal = e.target;
            this.clearModalContent(modal);
        });
    }

    /**
     * Initialize dropdowns
     */
    initializeDropdowns() {
        // Handle dropdown events
        document.addEventListener('show.bs.dropdown', (e) => {
            const dropdown = e.target;
            this.loadDropdownContent(dropdown);
        });
    }

    /**
     * Initialize charts
     */
    initializeCharts() {
        // Initialize Chart.js charts
        const chartElements = document.querySelectorAll('[data-chart]');
        chartElements.forEach(element => {
            this.initializeChart(element);
        });
    }

    /**
     * Initialize individual chart
     */
    initializeChart(element) {
        const chartType = element.dataset.chart;
        const chartData = element.dataset.chartData ? JSON.parse(element.dataset.chartData) : null;
        const chartOptions = element.dataset.chartOptions ? JSON.parse(element.dataset.chartOptions) : {};

        if (chartData && window.Chart) {
            new Chart(element, {
                type: chartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    ...chartOptions
                }
            });
        }
    }

    /**
     * Initialize date pickers
     */
    initializeDatePickers() {
        const dateInputs = document.querySelectorAll('input[type="date"], input[data-datepicker]');
        dateInputs.forEach(input => {
            this.initializeDatePicker(input);
        });
    }

    /**
     * Initialize individual date picker
     */
    initializeDatePicker(input) {
        // Add date picker functionality
        // This would integrate with a date picker library like Flatpickr
    }

    /**
     * Initialize file uploads
     */
    initializeFileUploads() {
        const fileInputs = document.querySelectorAll('input[type="file"][data-upload]');
        fileInputs.forEach(input => {
            this.initializeFileUpload(input);
        });
    }

    /**
     * Initialize individual file upload
     */
    initializeFileUpload(input) {
        input.addEventListener('change', (e) => {
            const files = e.target.files;
            if (files.length > 0) {
                this.uploadFiles(files, input);
            }
        });
    }

    /**
     * Upload files
     */
    async uploadFiles(files, input) {
        const formData = new FormData();
        Array.from(files).forEach(file => {
            formData.append('files[]', file);
        });

        try {
            const response = await this.modules.ajax.uploadFile(
                input.dataset.uploadUrl || '/admin/upload',
                formData,
                {
                    loadingElement: input.parentNode.querySelector('.upload-progress')
                }
            );

            this.modules.notifications.success('Files uploaded successfully');
            
            // Trigger custom event
            input.dispatchEvent(new CustomEvent('upload:success', {
                detail: { response, files }
            }));

        } catch (error) {
            this.modules.notifications.error('File upload failed: ' + error.message);
        }
    }

    /**
     * Load modal content
     */
    async loadModalContent(modal) {
        const url = modal.dataset.modalUrl;
        if (url) {
            try {
                const response = await this.modules.ajax.get(url);
                const content = modal.querySelector('.modal-body');
                if (content) {
                    content.innerHTML = response.html || response;
                }
            } catch (error) {
                this.modules.notifications.error('Failed to load modal content');
            }
        }
    }

    /**
     * Clear modal content
     */
    clearModalContent(modal) {
        const content = modal.querySelector('.modal-body');
        if (content) {
            content.innerHTML = '';
        }
    }

    /**
     * Load dropdown content
     */
    async loadDropdownContent(dropdown) {
        const url = dropdown.dataset.dropdownUrl;
        if (url) {
            try {
                const response = await this.modules.ajax.get(url);
                const content = dropdown.querySelector('.dropdown-menu');
                if (content) {
                    content.innerHTML = response.html || response;
                }
            } catch (error) {
                this.modules.notifications.error('Failed to load dropdown content');
            }
        }
    }

    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K - Search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.openSearch();
            }

            // Ctrl/Cmd + N - New item
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                this.openNewItem();
            }

            // Escape - Close modals
            if (e.key === 'Escape') {
                this.closeModals();
            }
        });
    }

    /**
     * Open search
     */
    openSearch() {
        const searchModal = document.getElementById('searchModal');
        if (searchModal) {
            const modal = new bootstrap.Modal(searchModal);
            modal.show();
        }
    }

    /**
     * Open new item
     */
    openNewItem() {
        const newItemButton = document.querySelector('[data-new-item]');
        if (newItemButton) {
            newItemButton.click();
        }
    }

    /**
     * Close modals
     */
    closeModals() {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        });
    }

    /**
     * Setup auto refresh
     */
    setupAutoRefresh() {
        this.autoRefreshIntervals = new Map();
        
        const autoRefreshElements = document.querySelectorAll('[data-auto-refresh]');
        autoRefreshElements.forEach(element => {
            const interval = parseInt(element.dataset.autoRefresh);
            if (interval > 0) {
                this.startAutoRefresh(element, interval);
            }
        });
    }

    /**
     * Start auto refresh
     */
    startAutoRefresh(element, interval) {
        const intervalId = setInterval(() => {
            this.refreshElement(element);
        }, interval * 1000);

        this.autoRefreshIntervals.set(element, intervalId);
    }

    /**
     * Refresh element
     */
    async refreshElement(element) {
        const url = element.dataset.refreshUrl;
        if (url) {
            try {
                const response = await this.modules.ajax.get(url);
                element.innerHTML = response.html || response;
            } catch (error) {
                console.error('Auto refresh failed:', error);
            }
        }
    }

    /**
     * Pause auto refresh
     */
    pauseAutoRefresh() {
        this.autoRefreshIntervals.forEach((intervalId) => {
            clearInterval(intervalId);
        });
    }

    /**
     * Resume auto refresh
     */
    resumeAutoRefresh() {
        this.setupAutoRefresh();
    }

    /**
     * Get module
     */
    getModule(name) {
        return this.modules[name];
    }

    /**
     * Show loading
     */
    showLoading(element = null) {
        this.modules.ajax.showLoading(element);
    }

    /**
     * Hide loading
     */
    hideLoading(element = null) {
        this.modules.ajax.hideLoading(element);
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info', options = {}) {
        return this.modules.notifications.show(message, type, options);
    }

    /**
     * Reload DataTable
     */
    reloadDataTable(tableId) {
        this.modules.datatables.reloadTable(tableId);
    }
}

// Initialize admin app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.adminApp = new AdminApp();
});

// Export for module usage
export default AdminApp;

