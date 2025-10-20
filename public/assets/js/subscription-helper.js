/**
 * Subscription Helper Functions
 * Provides utilities for handling subscription-based features and restrictions
 */

// Global subscription context (set by views)
window.subscriptionContext = window.subscriptionContext || {};

/**
 * Check if user can perform a specific action
 * @param {string} feature - The feature to check
 * @returns {boolean} - Whether the action is allowed
 */
function canPerformAction(feature) {
    if (!window.subscriptionContext) {
        console.warn('Subscription context not available');
        return false;
    }
    
    // Check specific feature access
    const featureMap = {
        'add-network': 'canAddNetwork',
        'add-campaign': 'canAddCampaign',
        'sync-data': 'canSyncData',
        'export-data': 'canExportData',
        'api-access': 'canAccessAPI',
        'advanced-analytics': 'canAdvancedAnalytics'
    };
    
    const contextKey = featureMap[feature];
    if (contextKey && window.subscriptionContext.hasOwnProperty(contextKey)) {
        return window.subscriptionContext[contextKey] === true;
    }
    
    // Fallback to general read-only check
    return !window.subscriptionContext.isReadOnly;
}

/**
 * Show upgrade prompt modal
 * @param {string} feature - The feature that requires upgrade
 * @param {string} message - Custom message (optional)
 */
function showUpgradePrompt(feature, message) {
    // Try to find existing modal
    let modal = document.getElementById('upgradeModal');
    
    if (!modal) {
        // Create modal if it doesn't exist
        modal = createUpgradeModal();
        document.body.appendChild(modal);
    }
    
    // Update modal content based on feature
    updateModalContent(modal, feature, message);
    
    // Show modal
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}

/**
 * Handle restricted action - disable button and show upgrade prompt
 * @param {HTMLElement} button - The button element
 * @param {string} feature - The feature that requires subscription
 */
function handleRestrictedAction(button, feature) {
    if (!button) return;
    
    // Store original onclick
    const originalOnclick = button.onclick;
    
    // Override onclick
    button.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!canPerformAction(feature)) {
            showUpgradePrompt(feature);
            return false;
        }
        
        // If action is allowed, execute original onclick
        if (originalOnclick) {
            return originalOnclick.call(this, e);
        }
    };
    
    // Add visual indicators
    if (!canPerformAction(feature)) {
        button.classList.add('subscription-required');
        button.title = 'Subscribe to unlock this feature';
        
        // Add lock icon if not present
        if (!button.querySelector('.ti-lock')) {
            const icon = button.querySelector('i');
            if (icon) {
                icon.className = 'ti ti-lock me-1';
            }
        }
    }
}

/**
 * Mark elements as read-only based on subscription status
 */
function markAsReadOnly() {
    if (!window.subscriptionContext || !window.subscriptionContext.isReadOnly) {
        return;
    }
    
    // Add read-only class to body
    document.body.classList.add('read-only-mode');
    
    // Handle action buttons
    const actionButtons = document.querySelectorAll('[data-requires-subscription]');
    actionButtons.forEach(button => {
        const feature = button.getAttribute('data-requires-subscription');
        handleRestrictedAction(button, feature);
    });
    
    // Handle form inputs
    const formInputs = document.querySelectorAll('input[type="text"], input[type="email"], textarea, select');
    formInputs.forEach(input => {
        if (!canPerformAction('edit-data')) {
            input.disabled = true;
            input.classList.add('read-only');
        }
    });
    
    // Handle links that require subscription
    const restrictedLinks = document.querySelectorAll('a[data-requires-subscription]');
    restrictedLinks.forEach(link => {
        const feature = link.getAttribute('data-requires-subscription');
        if (!canPerformAction(feature)) {
            link.onclick = function(e) {
                e.preventDefault();
                showUpgradePrompt(feature);
                return false;
            };
            link.classList.add('subscription-required');
        }
    });
}

/**
 * Create upgrade modal dynamically
 * @returns {HTMLElement} - The modal element
 */
function createUpgradeModal() {
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'upgradeModal';
    modal.setAttribute('tabindex', '-1');
    modal.setAttribute('aria-labelledby', 'upgradeModalLabel');
    modal.setAttribute('aria-hidden', 'true');
    
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="upgradeModalLabel">
                        <i class="ti ti-crown me-2"></i>Upgrade Required
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                <i class="ti ti-lock fs-24"></i>
                            </div>
                        </div>
                        <h4 class="mb-2" id="modalTitle">Upgrade Required</h4>
                        <p class="text-muted" id="modalMessage">This feature requires a subscription.</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="mb-3">What you'll get:</h6>
                        <ul class="list-unstyled" id="modalBenefits">
                            <li class="mb-2">
                                <i class="ti ti-check text-success me-2"></i>
                                Unlock premium features
                            </li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <span id="modalUrgency">Join thousands of users who trust our platform</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Maybe Later</button>
                    <a href="${window.subscriptionContext.upgradeUrl || '/subscription/plans'}" class="btn btn-primary btn-lg">
                        <i class="ti ti-crown me-2"></i>Upgrade Now
                    </a>
                </div>
            </div>
        </div>
    `;
    
    return modal;
}

/**
 * Update modal content based on feature
 * @param {HTMLElement} modal - The modal element
 * @param {string} feature - The feature name
 * @param {string} message - Custom message
 */
function updateModalContent(modal, feature, message) {
    const title = modal.querySelector('#modalTitle');
    const messageEl = modal.querySelector('#modalMessage');
    const benefits = modal.querySelector('#modalBenefits');
    const urgency = modal.querySelector('#modalUrgency');
    
    // Feature-specific content
    const featureContent = {
        'add-network': {
            title: 'Connect Networks',
            message: 'Subscribe to connect and manage unlimited networks.',
            benefits: [
                'Connect unlimited networks',
                'Sync data from all sources',
                'Advanced network management',
                'Real-time data updates'
            ]
        },
        'add-campaign': {
            title: 'Create Campaigns',
            message: 'Subscribe to create and manage unlimited campaigns.',
            benefits: [
                'Create unlimited campaigns',
                'Advanced campaign analytics',
                'Automated optimization',
                'A/B testing tools'
            ]
        },
        'sync-data': {
            title: 'Sync Data',
            message: 'Subscribe to sync data in real-time.',
            benefits: [
                'Unlimited data sync',
                'Real-time updates',
                'Custom sync schedules',
                'Automated backups'
            ]
        },
        'export-data': {
            title: 'Export Data',
            message: 'Subscribe to export all your data.',
            benefits: [
                'Export all your data',
                'Multiple export formats',
                'Scheduled exports',
                'Data backup & recovery'
            ]
        },
        'advanced-analytics': {
            title: 'Advanced Analytics',
            message: 'Subscribe to unlock advanced analytics and insights.',
            benefits: [
                'Advanced reporting',
                'Custom dashboards',
                'Data insights',
                'Predictive analytics'
            ]
        }
    };
    
    const content = featureContent[feature] || {
        title: 'Premium Feature',
        message: 'Subscribe to unlock this premium feature.',
        benefits: [
            'Unlock premium features',
            'Get priority support',
            'Access to all tools',
            'Advanced customization'
        ]
    };
    
    // Update content
    if (title) title.textContent = content.title;
    if (messageEl) messageEl.textContent = message || content.message;
    
    if (benefits) {
        benefits.innerHTML = content.benefits.map(benefit => 
            `<li class="mb-2"><i class="ti ti-check text-success me-2"></i>${benefit}</li>`
        ).join('');
    }
    
    // Update urgency message
    if (urgency && window.subscriptionContext) {
        if (window.subscriptionContext.isTrialing && window.subscriptionContext.trialEndsIn <= 3) {
            urgency.textContent = `Your trial ends in ${window.subscriptionContext.trialEndsIn} days! Subscribe now to continue.`;
        } else {
            urgency.textContent = 'Join thousands of users who trust our platform';
        }
    }
}

/**
 * Initialize subscription helpers
 */
function initSubscriptionHelpers() {
    // Mark elements as read-only
    markAsReadOnly();
    
    // Add CSS for read-only mode
    if (!document.getElementById('subscription-helper-styles')) {
        const style = document.createElement('style');
        style.id = 'subscription-helper-styles';
        style.textContent = `
            .read-only-mode .subscription-required {
                opacity: 0.6;
                cursor: not-allowed;
            }
            
            .read-only-mode .read-only {
                background-color: #f8f9fa;
                cursor: not-allowed;
            }
            
            .subscription-required {
                position: relative;
            }
            
            .subscription-required::after {
                content: '🔒';
                position: absolute;
                top: -5px;
                right: -5px;
                font-size: 12px;
                background: #ffc107;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        `;
        document.head.appendChild(style);
    }
}

// Wait for jQuery to be available
function waitForJQuery(callback) {
    if (window.jQuery) {
        callback();
    } else {
        setTimeout(() => waitForJQuery(callback), 100);
    }
}

// Initialize when DOM and jQuery are ready
waitForJQuery(() => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSubscriptionHelpers);
    } else {
        initSubscriptionHelpers();
    }
});

// Export functions for global use
window.canPerformAction = canPerformAction;
window.showUpgradePrompt = showUpgradePrompt;
window.handleRestrictedAction = handleRestrictedAction;
window.markAsReadOnly = markAsReadOnly;
