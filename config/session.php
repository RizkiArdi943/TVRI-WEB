<?php
/**
 * Session configuration for Vercel deployment
 * Handles session persistence in serverless environment
 */

// Session configuration untuk Vercel
if (getenv('VERCEL') === '1') {
    // Di Vercel, gunakan session dengan cookie yang lebih panjang
    ini_set('session.cookie_lifetime', 86400 * 7); // 7 hari
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // HTTPS only
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', 86400 * 7); // 7 hari
    ini_set('session.use_strict_mode', 1);
    
    // Set session name yang unik
    session_name('TVRI_SESSION_' . substr(md5(getenv('BLOB_READ_WRITE_TOKEN') ?: 'default'), 0, 8));
}

/**
 * Start session dengan konfigurasi yang tepat
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set session parameters sebelum start
        if (getenv('VERCEL') === '1') {
            // Di Vercel, set cookie parameters
            session_set_cookie_params([
                'lifetime' => 86400 * 7, // 7 hari
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'] ?? '',
                'secure' => true, // HTTPS only
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        
        session_start();
        
        // Regenerate session ID untuk keamanan
        if (!isset($_SESSION['session_regenerated'])) {
            session_regenerate_id(true);
            $_SESSION['session_regenerated'] = true;
        }
        
        // Set session timeout
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        } else {
            // Check if session expired (24 hours)
            if (time() - $_SESSION['last_activity'] > 86400) {
                session_destroy();
                session_start();
                $_SESSION['last_activity'] = time();
            } else {
                $_SESSION['last_activity'] = time();
            }
        }
    }
}

/**
 * Check if session is valid
 */
function isSessionValid() {
    if (session_status() === PHP_SESSION_NONE) {
        return false;
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > 86400) { // 24 hours
            return false;
        }
    }
    
    return true;
}

/**
 * Extend session lifetime
 */
function extendSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['last_activity'] = time();
    }
}

/**
 * Clear session data
 */
function clearSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_destroy();
    }
}
?>
