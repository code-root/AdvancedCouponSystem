/**
 * AJAX Helper for Dashboard
 * Provides utilities for making AJAX requests with proper error handling
 */

window.ajaxHelper = {
    /**
     * Make an AJAX request
     * @param {string} method - HTTP method (GET, POST, PUT, DELETE)
     * @param {string} url - Request URL
     * @param {Object|FormData} data - Request data
     * @param {Object} options - Additional options
     * @returns {Promise} - Promise that resolves with response data
     */
    request: async function(method, url, data = {}, options = {}) {
        const defaultOptions = {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            loadingElement: null,
            successMessage: 'Operation completed successfully.',
            errorMessage: 'An error occurred.',
            showNotifications: true,
        };
        
        options = { ...defaultOptions, ...options };
        
        let buttonText = '';
        if (options.loadingElement) {
            buttonText = options.loadingElement.innerHTML;
            options.loadingElement.disabled = true;
            options.loadingElement.innerHTML = options.loadingElement.dataset.loadingText || '<i class="ti ti-loader me-1"></i>Loading...';
        }
        
        try {
            const config = {
                method: method,
                headers: options.headers,
            };
            
            if (method === 'GET' || method === 'HEAD') {
                const params = new URLSearchParams(data).toString();
                url = `${url}?${params}`;
            } else {
                if (data instanceof FormData) {
                    config.body = data;
                    // Remove Content-Type header for FormData to let browser set it with boundary
                    delete config.headers['Content-Type'];
                } else {
                    config.body = JSON.stringify(data);
                }
            }
            
            const response = await fetch(url, config);
            const responseData = await response.json();
            
            if (!response.ok) {
                const error = new Error(responseData.message || options.errorMessage);
                error.response = response;
                error.data = responseData;
                throw error;
            }
            
            if (options.showNotifications && options.successMessage) {
                this.showNotification(options.successMessage, 'success');
            }
            
            return responseData;
        } catch (error) {
            if (options.showNotifications && options.errorMessage) {
                this.showNotification(error.message || options.errorMessage, 'error');
            }
            throw error;
        } finally {
            if (options.loadingElement) {
                options.loadingElement.disabled = false;
                options.loadingElement.innerHTML = buttonText;
            }
        }
    },
    
    /**
     * Make a GET request
     * @param {string} url - Request URL
     * @param {Object} data - Query parameters
     * @param {Object} options - Additional options
     * @returns {Promise} - Promise that resolves with response data
     */
    get: function(url, data = {}, options = {}) {
        return this.request('GET', url, data, options);
    },
    
    /**
     * Make a POST request
     * @param {string} url - Request URL
     * @param {Object|FormData} data - Request data
     * @param {Object} options - Additional options
     * @returns {Promise} - Promise that resolves with response data
     */
    post: function(url, data = {}, options = {}) {
        return this.request('POST', url, data, options);
    },
    
    /**
     * Make a PUT request
     * @param {string} url - Request URL
     * @param {Object|FormData} data - Request data
     * @param {Object} options - Additional options
     * @returns {Promise} - Promise that resolves with response data
     */
    put: function(url, data = {}, options = {}) {
        return this.request('PUT', url, data, options);
    },
    
    /**
     * Make a DELETE request
     * @param {string} url - Request URL
     * @param {Object} data - Request data
     * @param {Object} options - Additional options
     * @returns {Promise} - Promise that resolves with response data
     */
    delete: function(url, data = {}, options = {}) {
        return this.request('DELETE', url, data, options);
    },
    
    /**
     * Show notification using SweetAlert2 or fallback to alert
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, warning, info)
     */
    showNotification: function(message, type = 'info') {
        if (window.Swal) {
            const iconMap = {
                success: 'success',
                error: 'error',
                warning: 'warning',
                info: 'info'
            };
            
            Swal.fire({
                icon: iconMap[type] || 'info',
                title: type.charAt(0).toUpperCase() + type.slice(1),
                text: message,
                timer: type === 'success' ? 3000 : 5000,
                timerProgressBar: true,
                showConfirmButton: type !== 'success'
            });
        } else {
            // Fallback to browser alert
            alert(`${type.toUpperCase()}: ${message}`);
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('AJAX Helper initialized');
});







