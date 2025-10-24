/**
 * Admin Performance Optimization Script
 * Reduces resource consumption and improves page speed
 */

class AdminPerformanceOptimizer {
    constructor() {
        this.init();
    }

    /**
     * Initialize performance optimizations
     */
    init() {
        this.optimizeImages();
        this.optimizeTables();
        this.optimizeForms();
        this.setupLazyLoading();
        this.optimizeAnimations();
        this.setupMemoryManagement();
    }

    /**
     * Optimize images with lazy loading and compression
     */
    optimizeImages() {
        // Lazy load images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            }, { rootMargin: '50px' });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Add loading="lazy" to all images
        document.querySelectorAll('img:not([loading])').forEach(img => {
            img.setAttribute('loading', 'lazy');
        });
    }

    /**
     * Optimize tables with virtualization for large datasets
     */
    optimizeTables() {
        const tables = document.querySelectorAll('table[data-virtualize]');
        
        tables.forEach(table => {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            if (rows.length > 100) {
                this.setupTableVirtualization(table, rows);
            }
        });
    }

    /**
     * Setup table virtualization for large datasets
     */
    setupTableVirtualization(table, rows) {
        const container = table.closest('.table-responsive');
        const visibleRows = 20;
        let startIndex = 0;

        const renderRows = () => {
            const endIndex = Math.min(startIndex + visibleRows, rows.length);
            const visibleRowsArray = rows.slice(startIndex, endIndex);
            
            tbody.innerHTML = '';
            visibleRowsArray.forEach(row => {
                tbody.appendChild(row);
            });
        };

        // Initial render
        renderRows();

        // Scroll handler with throttling
        let scrollTimeout;
        container.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                const scrollTop = container.scrollTop;
                const rowHeight = 50; // Approximate row height
                const newStartIndex = Math.floor(scrollTop / rowHeight);
                
                if (newStartIndex !== startIndex) {
                    startIndex = newStartIndex;
                    renderRows();
                }
            }, 16); // ~60fps
        });
    }

    /**
     * Optimize forms with debouncing and validation
     */
    optimizeForms() {
        const forms = document.querySelectorAll('form[data-optimize]');
        
        forms.forEach(form => {
            // Debounce input events
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                let timeoutId;
                input.addEventListener('input', () => {
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(() => {
                        this.validateField(input);
                    }, 300);
                });
            });

            // Optimize form submission
            form.addEventListener('submit', (e) => {
                this.optimizeFormSubmission(e);
            });
        });
    }

    /**
     * Validate individual field
     */
    validateField(field) {
        const value = field.value.trim();
        const rules = JSON.parse(field.dataset.validation || '{}');
        
        // Clear previous errors
        field.classList.remove('is-invalid');
        const existingFeedback = field.parentNode.querySelector('.invalid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        // Validate based on rules
        let isValid = true;
        let errorMessage = '';

        if (rules.required && !value) {
            isValid = false;
            errorMessage = 'This field is required.';
        } else if (rules.email && value && !this.isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address.';
        } else if (rules.minLength && value.length < rules.minLength) {
            isValid = false;
            errorMessage = `Minimum length is ${rules.minLength} characters.`;
        } else if (rules.maxLength && value.length > rules.maxLength) {
            isValid = false;
            errorMessage = `Maximum length is ${rules.maxLength} characters.`;
        }

        if (!isValid) {
            field.classList.add('is-invalid');
            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = errorMessage;
            field.parentNode.appendChild(feedback);
        }

        return isValid;
    }

    /**
     * Validate email format
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Optimize form submission
     */
    optimizeFormSubmission(e) {
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        
        // Disable submit button to prevent double submission
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        }

        // Re-enable button after 5 seconds as fallback
        setTimeout(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButton.dataset.originalText || 'Submit';
            }
        }, 5000);
    }

    /**
     * Setup lazy loading for various elements
     */
    setupLazyLoading() {
        // Lazy load charts
        const chartElements = document.querySelectorAll('[data-chart-lazy]');
        if ('IntersectionObserver' in window) {
            const chartObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadLazyChart(entry.target);
                        chartObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            chartElements.forEach(chart => {
                chartObserver.observe(chart);
            });
        }

        // Lazy load widgets
        const widgetElements = document.querySelectorAll('[data-widget-lazy]');
        if ('IntersectionObserver' in window) {
            const widgetObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadLazyWidget(entry.target);
                        widgetObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            widgetElements.forEach(widget => {
                widgetObserver.observe(widget);
            });
        }
    }

    /**
     * Load lazy chart
     */
    loadLazyChart(chartElement) {
        const chartType = chartElement.dataset.chartLazy;
        const chartData = JSON.parse(chartElement.dataset.chartData || '{}');
        
        // Show loading indicator
        chartElement.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
        
        // Simulate loading delay for better UX
        setTimeout(() => {
            this.initializeChart(chartElement, chartType, chartData);
        }, 500);
    }

    /**
     * Load lazy widget
     */
    loadLazyWidget(widgetElement) {
        const widgetType = widgetElement.dataset.widgetLazy;
        const widgetUrl = widgetElement.dataset.widgetUrl;
        
        if (widgetUrl) {
            fetch(widgetUrl)
                .then(response => response.text())
                .then(html => {
                    widgetElement.innerHTML = html;
                    this.initializeWidget(widgetElement, widgetType);
                })
                .catch(error => {
                    console.error('Failed to load widget:', error);
                    widgetElement.innerHTML = '<div class="alert alert-warning">Failed to load widget</div>';
                });
        }
    }

    /**
     * Initialize chart
     */
    initializeChart(element, type, data) {
        if (typeof Chart !== 'undefined') {
            new Chart(element, {
                type: type,
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }
    }

    /**
     * Initialize widget
     */
    initializeWidget(element, type) {
        // Initialize widget-specific functionality
        switch (type) {
            case 'data-table':
                if (typeof $ !== 'undefined' && $.fn.DataTable) {
                    $(element).DataTable({
                        responsive: true,
                        pageLength: 25
                    });
                }
                break;
            case 'chart':
                // Chart initialization handled separately
                break;
        }
    }

    /**
     * Optimize animations for better performance
     */
    optimizeAnimations() {
        // Use CSS transforms instead of changing layout properties
        const animatedElements = document.querySelectorAll('[data-animate]');
        
        animatedElements.forEach(element => {
            element.style.willChange = 'transform, opacity';
            
            // Remove will-change after animation completes
            element.addEventListener('animationend', () => {
                element.style.willChange = 'auto';
            });
        });

        // Optimize scroll animations
        this.setupScrollAnimations();
    }

    /**
     * Setup scroll animations with throttling
     */
    setupScrollAnimations() {
        let ticking = false;
        
        const updateScrollAnimations = () => {
            const scrollY = window.pageYOffset;
            
            document.querySelectorAll('[data-scroll-animate]').forEach(element => {
                const rect = element.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
                
                if (isVisible) {
                    element.classList.add('animate-in');
                } else {
                    element.classList.remove('animate-in');
                }
            });
            
            ticking = false;
        };

        const requestTick = () => {
            if (!ticking) {
                requestAnimationFrame(updateScrollAnimations);
                ticking = true;
            }
        };

        window.addEventListener('scroll', requestTick, { passive: true });
    }

    /**
     * Setup memory management
     */
    setupMemoryManagement() {
        // Clean up event listeners on page unload
        window.addEventListener('beforeunload', () => {
            this.cleanup();
        });

        // Periodic cleanup of unused DOM elements
        setInterval(() => {
            this.cleanupUnusedElements();
        }, 60000); // Every minute
    }

    /**
     * Cleanup unused elements and event listeners
     */
    cleanupUnusedElements() {
        // Remove hidden elements that are no longer needed
        const hiddenElements = document.querySelectorAll('.d-none, .invisible');
        hiddenElements.forEach(element => {
            if (element.dataset.cleanup === 'true') {
                element.remove();
            }
        });

        // Clean up old alerts
        const oldAlerts = document.querySelectorAll('.alert[data-timestamp]');
        const now = Date.now();
        oldAlerts.forEach(alert => {
            const timestamp = parseInt(alert.dataset.timestamp);
            if (now - timestamp > 300000) { // 5 minutes
                alert.remove();
            }
        });
    }

    /**
     * Cleanup method
     */
    cleanup() {
        // Remove event listeners
        window.removeEventListener('scroll', this.scrollHandler);
        window.removeEventListener('resize', this.resizeHandler);
        
        // Clear intervals and timeouts
        if (this.cleanupInterval) {
            clearInterval(this.cleanupInterval);
        }
    }

    /**
     * Optimize network requests
     */
    optimizeNetworkRequests() {
        // Implement request caching
        const cache = new Map();
        
        const cachedFetch = async (url, options = {}) => {
            const cacheKey = `${url}-${JSON.stringify(options)}`;
            
            if (cache.has(cacheKey)) {
                const cached = cache.get(cacheKey);
                if (Date.now() - cached.timestamp < 300000) { // 5 minutes
                    return cached.response.clone();
                }
            }
            
            const response = await fetch(url, options);
            cache.set(cacheKey, {
                response: response.clone(),
                timestamp: Date.now()
            });
            
            return response;
        };

        // Override global fetch
        window.cachedFetch = cachedFetch;
    }

    /**
     * Optimize CSS delivery
     */
    optimizeCSSDelivery() {
        // Preload critical CSS
        const criticalCSS = document.querySelector('link[rel="preload"][as="style"]');
        if (criticalCSS) {
            criticalCSS.onload = function() {
                this.rel = 'stylesheet';
            };
        }

        // Defer non-critical CSS
        const nonCriticalCSS = document.querySelectorAll('link[rel="stylesheet"][data-defer]');
        nonCriticalCSS.forEach(link => {
            link.media = 'print';
            link.onload = function() {
                this.media = 'all';
            };
        });
    }
}

// Initialize performance optimizer
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.admin-content') || document.querySelector('[data-admin]')) {
        window.adminPerformanceOptimizer = new AdminPerformanceOptimizer();
    }
});

// Export for global access
window.AdminPerformanceOptimizer = AdminPerformanceOptimizer;

