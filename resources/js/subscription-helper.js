/**
 * Subscription Helper for Dashboard
 * Provides utilities for subscription-related operations
 */

window.subscriptionHelper = {
    /**
     * Apply coupon code to a plan
     * @param {number} planId - Plan ID
     * @param {string} couponCode - Coupon code
     * @returns {Promise} - Promise that resolves with coupon validation result
     */
    applyCoupon: async function(planId, couponCode) {
        if (!couponCode.trim()) {
            return {
                valid: false,
                message: 'Please enter a coupon code'
            };
        }

        try {
            const response = await window.ajaxHelper.post('/api/validate-coupon', {
                coupon: couponCode,
                plan_id: planId
            }, {
                showNotifications: false
            });

            return response;
        } catch (error) {
            return {
                valid: false,
                message: error.message || 'Invalid coupon code'
            };
        }
    },

    /**
     * Update price display with discount
     * @param {string} elementId - Price display element ID
     * @param {number} originalPrice - Original price
     * @param {number} discount - Discount percentage
     * @param {string} billingCycle - Billing cycle
     */
    updatePriceDisplay: function(elementId, originalPrice, discount, billingCycle) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const discountedPrice = originalPrice - (originalPrice * discount / 100);
        
        element.innerHTML = `
            <span class="text-decoration-line-through text-muted me-2">$${originalPrice.toFixed(2)}</span>
            <span class="fs-5 fw-bold text-success">$${discountedPrice.toFixed(2)}</span>
            <small class="text-muted">/${billingCycle}</small>
        `;
    },

    /**
     * Reset price display to original
     * @param {string} elementId - Price display element ID
     * @param {number} originalPrice - Original price
     * @param {string} billingCycle - Billing cycle
     */
    resetPriceDisplay: function(elementId, originalPrice, billingCycle) {
        const element = document.getElementById(elementId);
        if (!element) return;

        element.innerHTML = `
            <span class="fs-5 fw-bold text-primary">$${originalPrice.toFixed(2)}</span>
            <small class="text-muted">/${billingCycle}</small>
        `;
    },

    /**
     * Show coupon result message
     * @param {string} elementId - Result element ID
     * @param {boolean} valid - Whether coupon is valid
     * @param {string} message - Result message
     * @param {string} discount - Discount percentage (if valid)
     */
    showCouponResult: function(elementId, valid, message, discount = null) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const iconClass = valid ? 'ti-check-circle' : 'ti-x-circle';
        const textClass = valid ? 'text-success' : 'text-danger';
        
        let html = `<div class="${textClass}"><i class="ti ${iconClass} me-1"></i>${message}</div>`;
        
        if (valid && discount) {
            html += `<div class="text-info mt-1"><small>${discount}% discount applied</small></div>`;
        }

        element.innerHTML = html;
    },

    /**
     * Initialize coupon functionality for all plans
     */
    initializeCoupons: function() {
        // Auto-apply coupon on Enter key
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.name === 'coupon') {
                e.preventDefault();
                const planId = e.target.id.split('-')[1];
                window.subscriptionHelper.handleCouponApplication(planId);
            }
        });

        // Initialize coupon buttons
        document.querySelectorAll('[onclick*="applyCoupon"]').forEach(button => {
            const planId = button.getAttribute('onclick').match(/\d+/)[0];
            button.addEventListener('click', function() {
                window.subscriptionHelper.handleCouponApplication(planId);
            });
        });
    },

    /**
     * Handle coupon application for a specific plan
     * @param {number} planId - Plan ID
     */
    handleCouponApplication: async function(planId) {
        const couponInput = document.getElementById(`coupon-${planId}`);
        const couponResult = document.getElementById(`coupon-result-${planId}`);
        const priceDisplay = document.getElementById(`price-display-${planId}`);
        
        if (!couponInput || !couponResult || !priceDisplay) return;

        const coupon = couponInput.value.trim();
        
        if (!coupon) {
            this.showCouponResult(`coupon-result-${planId}`, false, 'Please enter a coupon code');
            return;
        }

        // Show loading
        couponResult.innerHTML = '<div class="text-info"><i class="ti ti-loader me-1"></i>Validating coupon...</div>';

        try {
            const result = await this.applyCoupon(planId, coupon);
            
            if (result.valid) {
                this.showCouponResult(`coupon-result-${planId}`, true, result.message, result.discount);
                
                // Update price display if discount is provided
                if (result.discount && result.original_price) {
                    this.updatePriceDisplay(`price-display-${planId}`, result.original_price, result.discount, result.billing_cycle);
                }
            } else {
                this.showCouponResult(`coupon-result-${planId}`, false, result.message);
            }
        } catch (error) {
            this.showCouponResult(`coupon-result-${planId}`, false, 'Error validating coupon');
        }
    },

    /**
     * Get subscription context data
     * @returns {Object|null} - Subscription context or null
     */
    getSubscriptionContext: function() {
        return window.subscriptionContext || null;
    },

    /**
     * Check if user can perform an action based on subscription limits
     * @param {string} action - Action to check (add_network, add_campaign, sync_data, create_order)
     * @returns {boolean} - Whether user can perform the action
     */
    canPerformAction: function(action) {
        const context = this.getSubscriptionContext();
        if (!context) return false;

        switch (action) {
            case 'add_network':
                return context.canAddNetwork || false;
            case 'add_campaign':
                return context.canAddCampaign || false;
            case 'sync_data':
                return context.canSyncData || false;
            case 'create_order':
                return context.canCreateOrder || false;
            default:
                return false;
        }
    },

    /**
     * Get usage statistics
     * @returns {Object|null} - Usage statistics or null
     */
    getUsageStats: function() {
        const context = this.getSubscriptionContext();
        return context?.usageStats || null;
    },

    /**
     * Show limit exceeded notification
     * @param {string} limitType - Type of limit exceeded
     */
    showLimitExceededNotification: function(limitType) {
        const messages = {
            networks: 'You have reached your network limit. Please upgrade your plan to add more networks.',
            campaigns: 'You have reached your campaign limit. Please upgrade your plan to add more campaigns.',
            syncs: 'You have reached your sync limit for this period. Please wait or upgrade your plan.',
            orders: 'You have reached your monthly order limit. Please upgrade your plan to process more orders.',
            revenue: 'You have reached your monthly revenue limit. Please upgrade your plan to generate more revenue.'
        };

        const message = messages[limitType] || 'You have reached a limit. Please upgrade your plan.';
        
        window.ajaxHelper.showNotification(message, 'warning');
    },

    /**
     * Initialize subscription helper
     */
    init: function() {
        this.initializeCoupons();
        console.log('Subscription Helper initialized');
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.subscriptionHelper.init();
});

// Global function for backward compatibility
function applyCoupon(planId) {
    window.subscriptionHelper.handleCouponApplication(planId);
}







