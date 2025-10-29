/**
 * Browser-based Authentication
 * Handles login/logout using localStorage
 */

class BrowserAuth {
    constructor() {
        this.storageKey = 'tvri_auth_token';
        this.userKey = 'tvri_user_data';
        this.init();
    }

    init() {
        // Check if user is logged in on page load
        this.checkAuthStatus();
        
        // Add event listeners
        this.addEventListeners();
    }

    addEventListeners() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Logout buttons
        const logoutButtons = document.querySelectorAll('.logout-btn');
        logoutButtons.forEach(btn => {
            btn.addEventListener('click', (e) => this.handleLogout(e));
        });

        // Check auth on every page navigation
        window.addEventListener('beforeunload', () => {
            this.saveAuthToStorage();
        });
    }

    /**
     * Handle login form submission
     */
    async handleLogin(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        submitBtn.disabled = true;
        
        const formData = new FormData(form);
        const username = formData.get('username');
        const password = formData.get('password');
        
        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'login',
                    username: username,
                    password: password
                })
            });
            
            let result;
            try {
                result = await response.json();
            } catch (parseError) {
                throw new Error('Invalid response from server');
            }
            
            if (result.success) {
                // Save auth data to localStorage
                this.saveAuthData(result.auth_token, result.user);
                
                // Show success message
                this.showMessage('Login berhasil!', 'success');
                
                // Redirect to dashboard
                setTimeout(() => {
                    window.location.href = 'index.php?page=dashboard';
                }, 1500);
            } else {
                this.showMessage(result.message || 'Username atau password salah!', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showMessage('Terjadi kesalahan saat login!', 'error');
        } finally {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    /**
     * Handle logout
     */
    async handleLogout(e) {
        e.preventDefault();
        
        try {
            const response = await fetch('api/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'logout'
                })
            });
            
            const result = await response.json();
            
            // Clear auth data from localStorage
            this.clearAuthData();
            
            // Show message
            this.showMessage('Logout berhasil!', 'success');
            
            // Redirect to landing page
            setTimeout(() => {
                window.location.href = 'index.php?page=landing';
            }, 1000);
            
        } catch (error) {
            console.error('Logout error:', error);
            // Clear data anyway
            this.clearAuthData();
            window.location.href = 'index.php?page=landing';
        }
    }

    /**
     * Save auth data to localStorage
     */
    saveAuthData(token, userData) {
        localStorage.setItem(this.storageKey, token);
        localStorage.setItem(this.userKey, JSON.stringify(userData));
        
        // Also save to sessionStorage as backup
        sessionStorage.setItem(this.storageKey, token);
        sessionStorage.setItem(this.userKey, JSON.stringify(userData));
    }

    /**
     * Get auth data from localStorage
     */
    getAuthData() {
        const token = localStorage.getItem(this.storageKey) || sessionStorage.getItem(this.storageKey);
        const userData = localStorage.getItem(this.userKey) || sessionStorage.getItem(this.userKey);
        
        if (token && userData) {
            try {
                return {
                    token: token,
                    user: JSON.parse(userData)
                };
            } catch (e) {
                console.error('Error parsing user data:', e);
                return null;
            }
        }
        
        return null;
    }

    /**
     * Clear auth data
     */
    clearAuthData() {
        localStorage.removeItem(this.storageKey);
        localStorage.removeItem(this.userKey);
        sessionStorage.removeItem(this.storageKey);
        sessionStorage.removeItem(this.userKey);
    }

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        const authData = this.getAuthData();
        
        if (!authData) {
            return false;
        }
        
        // Check if token is expired
        if (authData.user.expires && authData.user.expires < Date.now() / 1000) {
            this.clearAuthData();
            return false;
        }
        
        return true;
    }

    /**
     * Get current user
     */
    getCurrentUser() {
        const authData = this.getAuthData();
        return authData ? authData.user : null;
    }

    /**
     * Check auth status and redirect if needed
     */
    checkAuthStatus() {
        const currentPage = new URLSearchParams(window.location.search).get('page') || 'landing';
        const publicPages = ['login', 'register', 'landing'];
        
        if (!publicPages.includes(currentPage) && !this.isAuthenticated()) {
            // Redirect to login if not authenticated
            window.location.href = 'index.php?page=login';
            return;
        }
        
        if (currentPage === 'login' && this.isAuthenticated()) {
            // Redirect to dashboard if already logged in
            window.location.href = 'index.php?page=dashboard';
            return;
        }
        
        // Update UI with user data
        this.updateUserUI();
    }

    /**
     * Update UI with user information
     */
    updateUserUI() {
        const user = this.getCurrentUser();
        
        if (user) {
            // Update user name in header
            const userNameElements = document.querySelectorAll('.user-name');
            userNameElements.forEach(el => {
                el.textContent = user.full_name || user.username;
            });
            
            // Update user role
            const userRoleElements = document.querySelectorAll('.user-role');
            userRoleElements.forEach(el => {
                // el.textContent = user.role === 'admin' ? 'Administrator' : 'User';
            });
        }
    }

    /**
     * Save auth to storage (called before page unload)
     */
    saveAuthToStorage() {
        const authData = this.getAuthData();
        if (authData) {
            // Ensure data is saved
            this.saveAuthData(authData.token, authData.user);
        }
    }

    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        // Clear existing messages first
        this.clearMessages();
        
        // Add CSS if not exists
        this.addToastCSS();
        
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close">&times;</button>
        `;
        
        // Add to page
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        
        container.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        });
    }
    
    /**
     * Clear all existing messages
     */
    clearMessages() {
        const container = document.querySelector('.toast-container');
        if (container) {
            container.innerHTML = '';
        }
    }
    
    /**
     * Add toast CSS styles
     */
    addToastCSS() {
        if (document.getElementById('toast-css')) return;
        
        const style = document.createElement('style');
        style.id = 'toast-css';
        style.textContent = `
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            }
            
            .toast {
                min-width: 300px;
                padding: 16px 20px;
                margin-bottom: 10px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                font-weight: 500;
                font-size: 14px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                animation: slideInRight 0.3s ease-out;
            }
            
            .toast.success {
                background: linear-gradient(135deg, #10b981, #059669);
                color: white;
                border-left: 4px solid #047857;
            }
            
            .toast.error {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: white;
                border-left: 4px solid #b91c1c;
            }
            
            .toast.info {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: white;
                border-left: 4px solid #1d4ed8;
            }
            
            .toast-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .toast-close {
                background: none;
                border: none;
                color: white;
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                opacity: 0.8;
                transition: opacity 0.2s;
            }
            
            .toast-close:hover {
                opacity: 1;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        
        document.head.appendChild(style);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.browserAuth = new BrowserAuth();
});

// Export for use in other scripts
window.BrowserAuth = BrowserAuth;
