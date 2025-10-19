/**
 * AJAX Helper Module for Admin Panel
 * Provides global AJAX functionality with CSRF protection, error handling, and loading states
 */

class AjaxHelper {
    constructor() {
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.setupGlobalErrorHandler();
    }

    /**
     * Make an AJAX request
     * @param {string} url - The URL to send the request to
     * @param {string} method - HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param {Object} data - Data to send with the request
     * @param {Object} options - Additional options
     * @returns {Promise} - Promise that resolves with the response
     */
    async request(url, method = 'GET', data = null, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        };

        const requestOptions = {
            method: method.toUpperCase(),
            ...defaultOptions,
            ...options,
        };

        // Add data to request
        if (data) {
            if (requestOptions.headers['Content-Type'] === 'application/json') {
                requestOptions.body = JSON.stringify(data);
            } else if (data instanceof FormData) {
                requestOptions.body = data;
                delete requestOptions.headers['Content-Type']; // Let browser set it
            } else {
                requestOptions.body = new URLSearchParams(data);
                requestOptions.headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
        }

        // Show loading indicator
        this.showLoading(options.loadingElement);

        try {
            const response = await fetch(url, requestOptions);
            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || `HTTP error! status: ${response.status}`);
            }

            return responseData;
        } catch (error) {
            this.handleError(error, options.errorElement);
            throw error;
        } finally {
            this.hideLoading(options.loadingElement);
        }
    }

    /**
     * GET request
     */
    async get(url, options = {}) {
        return this.request(url, 'GET', null, options);
    }

    /**
     * POST request
     */
    async post(url, data = null, options = {}) {
        return this.request(url, 'POST', data, options);
    }

    /**
     * PUT request
     */
    async put(url, data = null, options = {}) {
        return this.request(url, 'PUT', data, options);
    }

    /**
     * PATCH request
     */
    async patch(url, data = null, options = {}) {
        return this.request(url, 'PATCH', data, options);
    }

    /**
     * DELETE request
     */
    async delete(url, options = {}) {
        return this.request(url, 'DELETE', null, options);
    }

    /**
     * Upload file with progress
     */
    async uploadFile(url, formData, options = {}) {
        const defaultOptions = {
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        };

        const requestOptions = {
            method: 'POST',
            body: formData,
            ...defaultOptions,
            ...options,
        };

        // Show loading indicator
        this.showLoading(options.loadingElement);

        try {
            const response = await fetch(url, requestOptions);
            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || `HTTP error! status: ${response.status}`);
            }

            return responseData;
        } catch (error) {
            this.handleError(error, options.errorElement);
            throw error;
        } finally {
            this.hideLoading(options.loadingElement);
        }
    }

    /**
     * Show loading indicator
     */
    showLoading(element = null) {
        if (element) {
            element.classList.add('loading');
            element.disabled = true;
        } else {
            document.body.classList.add('loading');
        }
    }

    /**
     * Hide loading indicator
     */
    hideLoading(element = null) {
        if (element) {
            element.classList.remove('loading');
            element.disabled = false;
        } else {
            document.body.classList.remove('loading');
        }
    }

    /**
     * Handle errors
     */
    handleError(error, element = null) {
        console.error('AJAX Error:', error);

        let message = 'An error occurred. Please try again.';
        
        if (error.message) {
            message = error.message;
        }

        // Show error in specific element
        if (element) {
            this.showErrorInElement(element, message);
        } else {
            // Show global notification
            this.showNotification(message, 'error');
        }
    }

    /**
     * Show error in specific element
     */
    showErrorInElement(element, message) {
        const errorElement = element.querySelector('.error-message') || 
                           element.parentNode.querySelector('.error-message');
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        } else {
            // Create error element
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger mt-1';
            errorDiv.textContent = message;
            element.parentNode.appendChild(errorDiv);
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }

    /**
     * Setup global error handler
     */
    setupGlobalErrorHandler() {
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && event.reason.message) {
                this.showNotification(event.reason.message, 'error');
            }
        });
    }

    /**
     * Serialize form data
     */
    serializeForm(form) {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }
        
        return data;
    }

    /**
     * Clear form errors
     */
    clearFormErrors(form) {
        const errorElements = form.querySelectorAll('.error-message, .invalid-feedback');
        errorElements.forEach(element => {
            element.style.display = 'none';
            element.textContent = '';
        });

        const invalidInputs = form.querySelectorAll('.is-invalid');
        invalidInputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
    }

    /**
     * Show form errors
     */
    showFormErrors(form, errors) {
        this.clearFormErrors(form);

        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                
                const errorElement = input.parentNode.querySelector('.invalid-feedback') ||
                                   input.parentNode.querySelector('.error-message');
                
                if (errorElement) {
                    errorElement.textContent = errors[field][0];
                    errorElement.style.display = 'block';
                }
            }
        });
    }
}

// Create global instance
window.ajaxHelper = new AjaxHelper();

// Export for module usage
export default AjaxHelper;

