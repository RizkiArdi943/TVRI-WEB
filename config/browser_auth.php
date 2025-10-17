<?php
/**
 * Browser-based authentication using localStorage/sessionStorage
 * More reliable for Vercel serverless environment
 */

/**
 * Check if user is logged in using browser storage
 */
function isLoggedIn() {
    // First check if we have valid session data (for local development)
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }
    
    // Check if we have auth token in request
    $authToken = $_COOKIE['auth_token'] ?? $_POST['auth_token'] ?? $_GET['auth_token'] ?? null;
    
    if (!$authToken) {
        return false;
    }
    
    // Decode and validate token
    $userData = decodeAuthToken($authToken);
    
    if (!$userData || !isset($userData['user_id'])) {
        return false;
    }
    
    // Check if token is expired
    if (isset($userData['expires']) && $userData['expires'] < time()) {
        return false;
    }
    
    // Set session data for compatibility
    $_SESSION['user_id'] = $userData['user_id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['full_name'] = $userData['full_name'];
    $_SESSION['role'] = $userData['role'];
    $_SESSION['user_role'] = $userData['role'];
    
    return true;
}

/**
 * Login user and return auth token
 */
function login($username, $password) {
    global $db;

    $users = $db->findAll('users', ['username' => $username]);
    $user = $users[0] ?? null;

    if ($user && password_verify($password, $user['password'])) {
        // Create auth token
        $authData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
            'login_time' => time(),
            'expires' => time() + (7 * 24 * 60 * 60) // 7 days
        ];
        
        $authToken = encodeAuthToken($authData);
        
        // Set cookie for server-side access
        setcookie('auth_token', $authToken, time() + (7 * 24 * 60 * 60), '/', '', true, true);
        
        // Set session data for compatibility
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_role'] = $user['role'];
        
        return [
            'success' => true,
            'auth_token' => $authToken,
            'user' => $authData
        ];
    }

    return ['success' => false, 'message' => 'Invalid credentials'];
}

/**
 * Logout user
 */
function logout() {
    // Clear cookie
    setcookie('auth_token', '', time() - 3600, '/', '', true, true);
    
    // Clear session
    $_SESSION = [];
    
    return [
        'success' => true,
        'message' => 'Logged out successfully'
    ];
}

/**
 * Encode auth token (simple base64 encoding)
 */
function encodeAuthToken($data) {
    return base64_encode(json_encode($data));
}

/**
 * Decode auth token
 */
function decodeAuthToken($token) {
    try {
        $decoded = base64_decode($token);
        return json_decode($decoded, true);
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    global $db;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    return $db->find('users', $_SESSION['user_id']);
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Return JSON response for AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Authentication required', 'redirect' => 'login']);
            exit();
        }
        
        header('Location: index.php?page=login');
        exit();
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php?page=dashboard');
        exit();
    }
}
?>
