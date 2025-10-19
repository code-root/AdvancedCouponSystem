@vite('resources/js/app.js')

<!-- Global AJAX Helper -->
<script>
// Global AJAX helper for consistent error handling
window.ajaxHelper = {
    post: function(url, data, options = {}) {
        const defaultOptions = {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        };
        
        const config = { ...defaultOptions, ...options };
        
        if (data instanceof FormData) {
            config.headers = {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            };
            delete config.headers['Content-Type'];
        } else if (typeof data === 'object') {
            config.body = JSON.stringify(data);
        }
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                throw error;
            });
    },
    
    get: function(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        };
        
        const config = { ...defaultOptions, ...options };
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                throw error;
            });
    },
    
    delete: function(url, options = {}) {
        const defaultOptions = {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        };
        
        const config = { ...defaultOptions, ...options };
        
        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                throw error;
            });
    }
};

// Global notification helper
window.showNotification = function(message, type = 'success') {
    // You can replace this with a more sophisticated notification system
    if (type === 'success') {
        alert('✅ ' + message);
    } else if (type === 'error') {
        alert('❌ ' + message);
    } else if (type === 'warning') {
        alert('⚠️ ' + message);
    } else {
        alert(message);
    }
};
</script>

@yield('scripts')

<!-- Customizer Scripts -->
<script>
/**
 * Theme Customizer
 * Handles theme customization settings
 */

(function() {
    'use strict';

    // Default settings
    const defaultSettings = {
        layout: 'vertical',
        theme: 'light',
        sidebar: 'light',
        topbar: 'light'
    };

    // Get saved settings from localStorage
    function getSavedSettings() {
        const saved = localStorage.getItem('theme-settings');
        return saved ? JSON.parse(saved) : defaultSettings;
    }

    // Save settings to localStorage
    function saveSettings(settings) {
        localStorage.setItem('theme-settings', JSON.stringify(settings));
    }

    // Apply theme settings
    function applySettings(settings) {
        const body = document.body;
        const html = document.documentElement;

        // Remove existing classes
        body.classList.remove('data-layout-vertical', 'data-layout-horizontal', 'data-layout-twocolumn');
        body.classList.remove('data-theme-light', 'data-theme-dark', 'data-theme-auto');
        body.classList.remove('data-sidebar-light', 'data-sidebar-dark', 'data-sidebar-brand');
        body.classList.remove('data-topbar-light', 'data-topbar-dark', 'data-topbar-brand');

        // Add new classes
        body.classList.add(`data-layout-${settings.layout}`);
        body.classList.add(`data-theme-${settings.theme}`);
        body.classList.add(`data-sidebar-${settings.sidebar}`);
        body.classList.add(`data-topbar-${settings.topbar}`);

        // Update radio buttons
        const layoutRadio = document.querySelector(`input[name="data-layout"][value="${settings.layout}"]`);
        const themeRadio = document.querySelector(`input[name="data-theme"][value="${settings.theme}"]`);
        const sidebarRadio = document.querySelector(`input[name="data-sidebar"][value="${settings.sidebar}"]`);
        const topbarRadio = document.querySelector(`input[name="data-topbar"][value="${settings.topbar}"]`);

        if (layoutRadio) layoutRadio.checked = true;
        if (themeRadio) themeRadio.checked = true;
        if (sidebarRadio) sidebarRadio.checked = true;
        if (topbarRadio) topbarRadio.checked = true;

        // Apply auto theme detection
        if (settings.theme === 'auto') {
            initAutoTheme();
        }
    }

    // Initialize customizer
    function initCustomizer() {
        const settings = getSavedSettings();
        applySettings(settings);

        // Handle customizer toggle
        const customizerToggle = document.querySelector('.customizer-toggle');
        if (customizerToggle) {
            customizerToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const offcanvas = new bootstrap.Offcanvas(document.getElementById('theme-settings-offcanvas'));
                offcanvas.show();
            });
        }

        // Handle layout changes
        document.querySelectorAll('input[name="data-layout"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const settings = getSavedSettings();
                settings.layout = this.value;
                saveSettings(settings);
                applySettings(settings);
            });
        });

        // Handle theme changes
        document.querySelectorAll('input[name="data-theme"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const settings = getSavedSettings();
                settings.theme = this.value;
                saveSettings(settings);
                applySettings(settings);
            });
        });

        // Handle sidebar changes
        document.querySelectorAll('input[name="data-sidebar"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const settings = getSavedSettings();
                settings.sidebar = this.value;
                saveSettings(settings);
                applySettings(settings);
            });
        });

        // Handle topbar changes
        document.querySelectorAll('input[name="data-topbar"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                const settings = getSavedSettings();
                settings.topbar = this.value;
                saveSettings(settings);
                applySettings(settings);
            });
        });

        // Handle reset button
        const resetButton = document.getElementById('reset-layout');
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                saveSettings(defaultSettings);
                applySettings(defaultSettings);
            });
        }
    }

    // Auto theme detection
    function initAutoTheme() {
        const settings = getSavedSettings();
        if (settings.theme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const body = document.body;
            
            if (prefersDark) {
                body.classList.add('data-theme-dark');
                body.classList.remove('data-theme-light');
            } else {
                body.classList.add('data-theme-light');
                body.classList.remove('data-theme-dark');
            }
        }

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
            const settings = getSavedSettings();
            if (settings.theme === 'auto') {
                const body = document.body;
                if (e.matches) {
                    body.classList.add('data-theme-dark');
                    body.classList.remove('data-theme-light');
                } else {
                    body.classList.add('data-theme-light');
                    body.classList.remove('data-theme-dark');
                }
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initCustomizer();
            initAutoTheme();
        });
    } else {
        initCustomizer();
        initAutoTheme();
    }

})();
</script>