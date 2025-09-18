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
        
        const formData = new FormData(e.target);
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
            
            const result = await response.json();
            
            if (result.success) {
                // Save auth data to localStorage
                this.saveAuthData(result.auth_token, result.user);
                
                // Show success message
                this.showMessage('Login berhasil!', 'success');
                
                // Redirect to dashboard
                setTimeout(() => {
                    window.location.href = 'index.php?page=dashboard';
                }, 1000);
            } else {
                this.showMessage(result.message || 'Login gagal!', 'error');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showMessage('Terjadi kesalahan saat login!', 'error');
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
            
            // Redirect to login
            setTimeout(() => {
                window.location.href = 'index.php?page=login';
            }, 1000);
            
        } catch (error) {
            console.error('Logout error:', error);
            // Clear data anyway
            this.clearAuthData();
            window.location.href = 'index.php?page=login';
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
                el.textContent = user.role === 'admin' ? 'Administrator' : 'User';
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
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.browserAuth = new BrowserAuth();
});

// Export for use in other scripts
window.BrowserAuth = BrowserAuth;
