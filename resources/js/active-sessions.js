/**
 * Active Sessions Manager
 */
class ActiveSessionsManager {
    constructor() {
        this.sessions = [];
        this.isInitialized = false;
        this.refreshInterval = null;
        this.init();
    }

    init() {
        if (this.isInitialized) return;
        
        this.setupElements();
        this.setupEventListeners();
        this.loadSessions();
        this.setupRealTimeUpdates();
        this.startAutoRefresh();
        this.isInitialized = true;
    }

    setupElements() {
        this.sessionsContainer = document.getElementById('sessionsContainer');
        this.sessionsTable = document.getElementById('sessionsTable');
        this.sessionsStats = document.getElementById('sessionsStats');
        this.refreshButton = document.getElementById('refreshSessions');
    }

    setupEventListeners() {
        if (this.refreshButton) {
            this.refreshButton.addEventListener('click', () => {
                this.loadSessions();
            });
        }

        // Setup terminate session handlers
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('terminate-session-btn')) {
                e.preventDefault();
                const sessionId = e.target.dataset.sessionId;
                this.terminateSession(sessionId);
            }
        });
    }

    loadSessions() {
        const isAdmin = window.location.pathname.includes('/admin/');
        const endpoint = isAdmin ? '/admin/sessions' : '/dashboard/sessions';
        
        fetch(`${endpoint}/api`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.sessions = data.sessions || [];
                this.renderSessions();
                this.updateStats();
            }
        })
        .catch(error => {
            console.error('Failed to load sessions:', error);
        });
    }

    setupRealTimeUpdates() {
        if (typeof window.Echo === 'undefined') {
            console.warn('Laravel Echo not available');
            return;
        }

        // Listen for admin session updates
        window.Echo.channel('admin-sessions')
            .listen('AdminSessionStarted', (e) => {
                this.addSession(e);
            })
            .listen('AdminSessionEnded', (e) => {
                this.removeSession(e.session_id);
            });

        // Listen for user session updates
        if (window.userId) {
            window.Echo.private(`user.${window.userId}`)
                .listen('SessionStarted', (e) => {
                    this.addSession(e);
                })
                .listen('SessionEnded', (e) => {
                    this.removeSession(e.session_id);
                });
        }
    }

    addSession(sessionData) {
        // Check if session already exists
        const existingIndex = this.sessions.findIndex(s => s.id === sessionData.id);
        
        if (existingIndex >= 0) {
            // Update existing session
            this.sessions[existingIndex] = { ...this.sessions[existingIndex], ...sessionData };
        } else {
            // Add new session
            this.sessions.unshift(sessionData);
        }

        this.renderSessions();
        this.updateStats();
    }

    removeSession(sessionId) {
        this.sessions = this.sessions.filter(s => s.id !== sessionId);
        this.renderSessions();
        this.updateStats();
    }

    renderSessions() {
        if (!this.sessionsTable) return;

        if (this.sessions.length === 0) {
            this.sessionsTable.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="ti ti-users-off fs-3 text-muted"></i>
                        <p class="text-muted mb-0">No active sessions</p>
                    </td>
                </tr>
            `;
            return;
        }

        const sessionsHtml = this.sessions.map(session => {
            const isCurrentSession = session.is_current;
            const statusBadge = session.is_active ? 
                '<span class="badge bg-success">Active</span>' : 
                '<span class="badge bg-secondary">Inactive</span>';
            
            const deviceIcon = this.getDeviceIcon(session.device_name);
            const location = session.location || 'Unknown';
            const lastActivity = this.getTimeAgo(session.last_activity_at);

            return `
                <tr class="${isCurrentSession ? 'table-primary' : ''}">
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="${deviceIcon} me-2"></i>
                            <div>
                                <div class="fw-medium">${session.device_name || 'Unknown Device'}</div>
                                <small class="text-muted">${session.platform || 'Unknown'} • ${session.browser || 'Unknown'}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div class="fw-medium">${session.ip_address}</div>
                            <small class="text-muted">${location}</small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div class="fw-medium">${session.admin?.name || session.user?.name || 'N/A'}</div>
                            <small class="text-muted">${session.admin?.email || session.user?.email || ''}</small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <div class="fw-medium">${this.formatDate(session.login_at)}</div>
                            <small class="text-muted">${lastActivity}</small>
                        </div>
                    </td>
                    <td>
                        ${statusBadge}
                        ${isCurrentSession ? '<span class="badge bg-primary ms-1">Current</span>' : ''}
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-info" onclick="activeSessionsManager.viewSessionDetails('${session.id}')">
                                <i class="ti ti-eye"></i>
                            </button>
                            ${!isCurrentSession ? `
                                <button type="button" class="btn btn-sm btn-outline-danger terminate-session-btn" data-session-id="${session.id}">
                                    <i class="ti ti-x"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        this.sessionsTable.innerHTML = sessionsHtml;
    }

    updateStats() {
        if (!this.sessionsStats) return;

        const activeSessions = this.sessions.filter(s => s.is_active).length;
        const totalSessions = this.sessions.length;
        const uniqueUsers = new Set(this.sessions.map(s => s.admin?.id || s.user?.id)).size;
        const currentSessions = this.sessions.filter(s => s.is_current).length;

        this.sessionsStats.innerHTML = `
            <div class="row text-center">
                <div class="col-3">
                    <div class="border-end">
                        <h4 class="mb-0 text-primary">${activeSessions}</h4>
                        <small class="text-muted">Active</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="border-end">
                        <h4 class="mb-0 text-info">${totalSessions}</h4>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="border-end">
                        <h4 class="mb-0 text-success">${uniqueUsers}</h4>
                        <small class="text-muted">Users</small>
                    </div>
                </div>
                <div class="col-3">
                    <h4 class="mb-0 text-warning">${currentSessions}</h4>
                    <small class="text-muted">Current</small>
                </div>
            </div>
        `;
    }

    terminateSession(sessionId) {
        if (!confirm('Are you sure you want to terminate this session?')) {
            return;
        }

        const isAdmin = window.location.pathname.includes('/admin/');
        const endpoint = isAdmin ? 
            `/admin/sessions/${sessionId}/terminate` : 
            `/dashboard/sessions/${sessionId}/terminate`;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.removeSession(sessionId);
                this.showToast('Session terminated successfully', 'success');
            } else {
                this.showToast(data.message || 'Failed to terminate session', 'error');
            }
        })
        .catch(error => {
            console.error('Failed to terminate session:', error);
            this.showToast('Failed to terminate session', 'error');
        });
    }

    viewSessionDetails(sessionId) {
        const session = this.sessions.find(s => s.id === sessionId);
        if (!session) return;

        // Show session details in a modal
        const modalHtml = `
            <div class="modal fade" id="sessionDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Session Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Device Information</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>Device:</strong></td><td>${session.device_name || 'Unknown'}</td></tr>
                                        <tr><td><strong>Platform:</strong></td><td>${session.platform || 'Unknown'}</td></tr>
                                        <tr><td><strong>Browser:</strong></td><td>${session.browser || 'Unknown'}</td></tr>
                                        <tr><td><strong>User Agent:</strong></td><td><small>${session.user_agent || 'N/A'}</small></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6>Session Information</h6>
                                    <table class="table table-sm">
                                        <tr><td><strong>IP Address:</strong></td><td>${session.ip_address}</td></tr>
                                        <tr><td><strong>Location:</strong></td><td>${session.location || 'Unknown'}</td></tr>
                                        <tr><td><strong>Login Time:</strong></td><td>${this.formatDateTime(session.login_at)}</td></tr>
                                        <tr><td><strong>Last Activity:</strong></td><td>${this.formatDateTime(session.last_activity_at)}</td></tr>
                                        <tr><td><strong>Status:</strong></td><td>${session.is_active ? 'Active' : 'Inactive'}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            ${!session.is_current ? `
                                <button type="button" class="btn btn-danger" onclick="activeSessionsManager.terminateSession('${session.id}')">
                                    Terminate Session
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('sessionDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('sessionDetailsModal'));
        modal.show();
    }

    getDeviceIcon(deviceName) {
        if (!deviceName) return 'ti ti-device-desktop';
        
        const device = deviceName.toLowerCase();
        if (device.includes('mobile') || device.includes('phone')) {
            return 'ti ti-device-mobile';
        } else if (device.includes('tablet')) {
            return 'ti ti-device-tablet';
        } else {
            return 'ti ti-device-desktop';
        }
    }

    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffInSeconds = Math.floor((now - date) / 1000);

        if (diffInSeconds < 60) return 'Just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
        if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d ago`;
        
        return date.toLocaleDateString();
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }

    formatDateTime(dateString) {
        return new Date(dateString).toLocaleString();
    }

    showToast(message, type = 'info') {
        if (typeof Swal !== 'undefined') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: type,
                title: message
            });
        }
    }

    startAutoRefresh() {
        // Refresh sessions every 30 seconds
        this.refreshInterval = setInterval(() => {
            this.loadSessions();
        }, 30000);
    }

    stopAutoRefresh() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
    }

    destroy() {
        this.stopAutoRefresh();
        this.isInitialized = false;
    }
}

// Initialize active sessions manager
const activeSessionsManager = new ActiveSessionsManager();

// Export for global access
window.activeSessionsManager = activeSessionsManager;

