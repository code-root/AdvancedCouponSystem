/**
 * Dashboard Utilities
 * Common helper functions for dashboard operations
 */

window.dashboardUtils = {
    /**
     * Show loading state on element
     * @param {string|Element} element - Element selector or element
     * @param {string} text - Loading text
     */
    showLoading: function(element, text = 'Loading...') {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) return;
        
        // Store original content
        el.dataset.originalContent = el.innerHTML;
        el.dataset.originalDisabled = el.disabled;
        
        // Set loading state
        el.disabled = true;
        el.innerHTML = `<i class="ti ti-loader me-1"></i>${text}`;
    },
    
    /**
     * Hide loading state on element
     * @param {string|Element} element - Element selector or element
     * @param {string} text - Text to restore (optional)
     */
    hideLoading: function(element, text = null) {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) return;
        
        // Restore original state
        el.disabled = el.dataset.originalDisabled === 'true';
        el.innerHTML = text || el.dataset.originalContent || '';
        
        // Clean up
        delete el.dataset.originalContent;
        delete el.dataset.originalDisabled;
    },
    
    /**
     * Show success notification
     * @param {string} message - Success message
     * @param {Object} options - Additional options
     */
    showSuccess: function(message, options = {}) {
        const defaultOptions = {
            title: 'Success',
            icon: 'success',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        };
        
        this.showNotification(message, { ...defaultOptions, ...options });
    },
    
    /**
     * Show error notification
     * @param {string} message - Error message
     * @param {Object} options - Additional options
     */
    showError: function(message, options = {}) {
        const defaultOptions = {
            title: 'Error',
            icon: 'error',
            timer: 5000,
            timerProgressBar: true,
            showConfirmButton: true,
            toast: true,
            position: 'top-end'
        };
        
        this.showNotification(message, { ...defaultOptions, ...options });
    },
    
    /**
     * Show warning notification
     * @param {string} message - Warning message
     * @param {Object} options - Additional options
     */
    showWarning: function(message, options = {}) {
        const defaultOptions = {
            title: 'Warning',
            icon: 'warning',
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: true,
            toast: true,
            position: 'top-end'
        };
        
        this.showNotification(message, { ...defaultOptions, ...options });
    },
    
    /**
     * Show info notification
     * @param {string} message - Info message
     * @param {Object} options - Additional options
     */
    showInfo: function(message, options = {}) {
        const defaultOptions = {
            title: 'Info',
            icon: 'info',
            timer: 4000,
            timerProgressBar: true,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        };
        
        this.showNotification(message, { ...defaultOptions, ...options });
    },
    
    /**
     * Show notification using SweetAlert2
     * @param {string} message - Notification message
     * @param {Object} options - SweetAlert2 options
     */
    showNotification: function(message, options = {}) {
        if (window.Swal) {
            Swal.fire({
                text: message,
                ...options
            });
        } else {
            // Fallback to browser alert
            alert(message);
        }
    },
    
    /**
     * Show confirmation dialog
     * @param {string} title - Dialog title
     * @param {string} message - Confirmation message
     * @param {Function} callback - Callback function
     * @param {Object} options - Additional options
     */
    showConfirm: function(title, message, callback, options = {}) {
        const defaultOptions = {
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        };
        
        if (window.Swal) {
            Swal.fire({
                ...defaultOptions,
                ...options
            }).then((result) => {
                if (result.isConfirmed && callback) {
                    callback();
                }
            });
        } else {
            // Fallback to browser confirm
            if (confirm(`${title}\n\n${message}`) && callback) {
                callback();
            }
        }
    },
    
    /**
     * Show upgrade prompt with detailed information
     * @param {Object} data - Upgrade data from server
     */
    showUpgradePrompt: function(data) {
        if (!data.upgrade_prompt) return;
        
        const prompt = data.upgrade_prompt;
        const benefits = prompt.benefits ? prompt.benefits.map(benefit => `<li><i class="ti ti-check text-success me-2"></i>${benefit}</li>`).join('') : '';
        
        const html = `
            <div class="text-start">
                <h5 class="mb-3">${prompt.title || 'Upgrade Required'}</h5>
                <p class="mb-3">${prompt.message || 'This feature requires a higher plan.'}</p>
                ${benefits ? `<ul class="list-unstyled mb-3">${benefits}</ul>` : ''}
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Current Plan: <strong>${prompt.current_plan || 'Free'}</strong></small>
                    <a href="${data.redirect_url || '#'}" class="btn btn-primary btn-sm">
                        <i class="ti ti-crown me-1"></i>${prompt.action_text || 'Upgrade Now'}
                    </a>
                </div>
            </div>
        `;
        
        if (window.Swal) {
            Swal.fire({
                html: html,
                icon: 'info',
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Later',
                width: '500px'
            });
        } else {
            alert(prompt.message || 'This feature requires a higher plan.');
        }
    },
    
    /**
     * Show progress bar
     * @param {string} message - Progress message
     * @param {number} progress - Progress percentage (0-100)
     */
    showProgress: function(message, progress = 0) {
        const progressHtml = `
            <div class="text-center">
                <h6 class="mb-3">${message}</h6>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: ${progress}%"
                         aria-valuenow="${progress}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
                <small class="text-muted">${progress}% Complete</small>
            </div>
        `;
        
        if (window.Swal) {
            Swal.fire({
                html: progressHtml,
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                width: '400px'
            });
        }
    },
    
    /**
     * Hide progress bar
     */
    hideProgress: function() {
        if (window.Swal) {
            Swal.close();
        }
    },
    
    /**
     * Show network connection progress
     * @param {string} networkName - Network name
     * @param {string} step - Current step
     */
    showNetworkProgress: function(networkName, step = 'Connecting') {
        const steps = [
            'Connecting to network...',
            'Authenticating credentials...',
            'Testing connection...',
            'Fetching data...',
            'Processing data...',
            'Saving to database...',
            'Complete!'
        ];
        
        const currentStep = steps.indexOf(step);
        const progress = Math.round((currentStep / (steps.length - 1)) * 100);
        
        this.showProgress(`Connecting to ${networkName} - ${step}`, progress);
    },
    
    /**
     * Handle AJAX response with proper error handling
     * @param {Response} response - Fetch response
     * @param {Object} options - Options
     */
    handleResponse: function(response, options = {}) {
        return response.json().then(data => {
            if (data.success) {
                if (data.message) {
                    this.showSuccess(data.message);
                }
                return data;
            } else {
                // Handle upgrade required
                if (data.upgrade_required) {
                    this.showUpgradePrompt(data);
                } else {
                    this.showError(data.message || 'An error occurred');
                }
                throw new Error(data.message || 'Request failed');
            }
        });
    },
    
    /**
     * Make AJAX request with dashboard utilities
     * @param {string} url - Request URL
     * @param {Object} options - Request options
     */
    request: function(url, options = {}) {
        const defaultOptions = {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            loadingElement: null,
            showProgress: false,
            progressMessage: 'Processing...'
        };
        
        options = { ...defaultOptions, ...options };
        
        // Show loading if element provided
        if (options.loadingElement) {
            this.showLoading(options.loadingElement);
        }
        
        // Show progress if requested
        if (options.showProgress) {
            this.showProgress(options.progressMessage);
        }
        
        return fetch(url, options)
            .then(response => this.handleResponse(response, options))
            .catch(error => {
                this.showError(error.message || 'Request failed');
                throw error;
            })
            .finally(() => {
                if (options.loadingElement) {
                    this.hideLoading(options.loadingElement);
                }
                if (options.showProgress) {
                    this.hideProgress();
                }
            });
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard Utils initialized');
});
