/**
 * Form Handler Module for Admin Panel
 * Auto-binds forms, handles validation, and submits via AJAX
 */

class FormHandler {
    constructor() {
        this.forms = new Map();
        this.init();
    }

    /**
     * Initialize form handler
     */
    init() {
        this.bindForms();
        this.setupGlobalFormEvents();
    }

    /**
     * Bind all forms with data-ajax attribute
     */
    bindForms() {
        const ajaxForms = document.querySelectorAll('form[data-ajax]');
        ajaxForms.forEach(form => this.bindForm(form));
    }

    /**
     * Bind individual form
     */
    bindForm(form) {
        const formId = form.id || `form_${Date.now()}`;
        const options = this.parseFormOptions(form);
        
        this.forms.set(formId, {
            element: form,
            options: options,
            bound: true
        });

        form.addEventListener('submit', (e) => this.handleSubmit(e, formId));
        
        // Real-time validation
        if (options.realTimeValidation) {
            this.setupRealTimeValidation(form);
        }
    }

    /**
     * Parse form options from data attributes
     */
    parseFormOptions(form) {
        return {
            url: form.dataset.ajaxUrl || form.action,
            method: form.dataset.ajaxMethod || form.method || 'POST',
            successMessage: form.dataset.successMessage || 'Form submitted successfully',
            errorMessage: form.dataset.errorMessage || 'An error occurred',
            redirect: form.dataset.redirect,
            reset: form.dataset.reset === 'true',
            realTimeValidation: form.dataset.realTimeValidation === 'true',
            loadingElement: form.dataset.loadingElement ? 
                document.querySelector(form.dataset.loadingElement) : 
                form.querySelector('button[type="submit"]'),
            errorElement: form.dataset.errorElement ? 
                document.querySelector(form.dataset.errorElement) : 
                form,
            beforeSubmit: form.dataset.beforeSubmit,
            afterSubmit: form.dataset.afterSubmit,
        };
    }

    /**
     * Handle form submission
     */
    async handleSubmit(event, formId) {
        event.preventDefault();
        
        const formData = this.forms.get(formId);
        if (!formData) return;

        const { element: form, options } = formData;

        // Clear previous errors
        ajaxHelper.clearFormErrors(form);

        // Validate form
        if (!this.validateForm(form)) {
            return;
        }

        // Before submit callback
        if (options.beforeSubmit && window[options.beforeSubmit]) {
            const result = window[options.beforeSubmit](form);
            if (result === false) return;
        }

        try {
            // Prepare data
            const data = this.prepareFormData(form, options);
            
            // Submit form
            const response = await ajaxHelper.request(
                options.url,
                options.method,
                data,
                {
                    loadingElement: options.loadingElement,
                    errorElement: options.errorElement
                }
            );

            // Handle success
            this.handleSuccess(response, form, options);

        } catch (error) {
            // Handle error
            this.handleError(error, form, options);
        }
    }

    /**
     * Validate form
     */
    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.showFieldError(input, 'This field is required');
                isValid = false;
            } else {
                this.clearFieldError(input);
            }
        });

        // Custom validation
        const customValidation = form.dataset.customValidation;
        if (customValidation && window[customValidation]) {
            const result = window[customValidation](form);
            if (result !== true) {
                isValid = false;
            }
        }

        return isValid;
    }

    /**
     * Prepare form data
     */
    prepareFormData(form, options) {
        if (options.method.toUpperCase() === 'GET') {
            return ajaxHelper.serializeForm(form);
        }

        // Check if form has file inputs
        const fileInputs = form.querySelectorAll('input[type="file"]');
        if (fileInputs.length > 0) {
            return new FormData(form);
        }

        return ajaxHelper.serializeForm(form);
    }

    /**
     * Handle successful submission
     */
    handleSuccess(response, form, options) {
        // Show success message
        if (response.message) {
            ajaxHelper.showNotification(response.message, 'success');
        } else if (options.successMessage) {
            ajaxHelper.showNotification(options.successMessage, 'success');
        }

        // Reset form if specified
        if (options.reset) {
            form.reset();
        }

        // Redirect if specified
        if (options.redirect) {
            setTimeout(() => {
                window.location.href = options.redirect;
            }, 1000);
        }

        // After submit callback
        if (options.afterSubmit && window[options.afterSubmit]) {
            window[options.afterSubmit](response, form);
        }

        // Trigger custom event
        form.dispatchEvent(new CustomEvent('form:success', {
            detail: { response, form, options }
        }));
    }

    /**
     * Handle form error
     */
    handleError(error, form, options) {
        // Show error message
        if (error.message) {
            ajaxHelper.showNotification(error.message, 'error');
        } else if (options.errorMessage) {
            ajaxHelper.showNotification(options.errorMessage, 'error');
        }

        // Trigger custom event
        form.dispatchEvent(new CustomEvent('form:error', {
            detail: { error, form, options }
        }));
    }

    /**
     * Setup real-time validation
     */
    setupRealTimeValidation(form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                this.validateField(input);
            });

            input.addEventListener('input', () => {
                // Clear error on input
                this.clearFieldError(input);
            });
        });
    }

    /**
     * Validate individual field
     */
    validateField(input) {
        const value = input.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Required validation
        if (input.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }

        // Email validation
        if (input.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }

        // Password validation
        if (input.type === 'password' && value && !this.isValidPassword(value)) {
            isValid = false;
            errorMessage = 'Password must be at least 8 characters long';
        }

        // URL validation
        if (input.type === 'url' && value && !this.isValidUrl(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid URL';
        }

        // Min/Max length validation
        if (value) {
            const minLength = input.getAttribute('minlength');
            const maxLength = input.getAttribute('maxlength');
            
            if (minLength && value.length < parseInt(minLength)) {
                isValid = false;
                errorMessage = `Minimum length is ${minLength} characters`;
            }
            
            if (maxLength && value.length > parseInt(maxLength)) {
                isValid = false;
                errorMessage = `Maximum length is ${maxLength} characters`;
            }
        }

        // Custom validation
        const customValidation = input.dataset.validation;
        if (customValidation && window[customValidation]) {
            const result = window[customValidation](input);
            if (result !== true) {
                isValid = false;
                errorMessage = result || 'Invalid input';
            }
        }

        if (isValid) {
            this.clearFieldError(input);
        } else {
            this.showFieldError(input, errorMessage);
        }

        return isValid;
    }

    /**
     * Show field error
     */
    showFieldError(input, message) {
        input.classList.add('is-invalid');
        
        let errorElement = input.parentNode.querySelector('.invalid-feedback');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback';
            input.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }

    /**
     * Clear field error
     */
    clearFieldError(input) {
        input.classList.remove('is-invalid');
        
        const errorElement = input.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    /**
     * Email validation
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Password validation
     */
    isValidPassword(password) {
        return password.length >= 8;
    }

    /**
     * URL validation
     */
    isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    /**
     * Setup global form events
     */
    setupGlobalFormEvents() {
        // Handle dynamically added forms
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const forms = node.querySelectorAll ? 
                            node.querySelectorAll('form[data-ajax]') : 
                            (node.matches && node.matches('form[data-ajax]') ? [node] : []);
                        
                        forms.forEach(form => this.bindForm(form));
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Unbind form
     */
    unbindForm(formId) {
        const formData = this.forms.get(formId);
        if (formData) {
            formData.element.removeEventListener('submit', this.handleSubmit);
            this.forms.delete(formId);
        }
    }

    /**
     * Get form by ID
     */
    getForm(formId) {
        return this.forms.get(formId);
    }
}

// Create global instance
window.formHandler = new FormHandler();

// Export for module usage
export default FormHandler;

