<?php
/**
 * Authentication API
 * Handles login/logout using browser storage
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/browser_auth.php';

// Start session for compatibility
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            handleLogin($input);
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        case 'check':
            handleAuthCheck();
            break;
            
        case 'refresh':
            handleTokenRefresh();
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Handle login request
 */
function handleLogin($input) {
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        throw new Exception('Username dan password harus diisi');
    }
    
    $result = login($username, $password);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'auth_token' => $result['auth_token'],
            'user' => $result['user']
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
}

/**
 * Handle logout request
 */
function handleLogout() {
    $result = logout();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logout berhasil'
    ]);
}

/**
 * Handle auth check request
 */
function handleAuthCheck() {
    if (isLoggedIn()) {
        $user = getCurrentUser();
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'authenticated' => false
        ]);
    }
}

/**
 * Handle token refresh
 */
function handleTokenRefresh() {
    if (isLoggedIn()) {
        $user = getCurrentUser();
        
        // Create new token
        $authData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
            'login_time' => time(),
            'expires' => time() + (7 * 24 * 60 * 60) // 7 days
        ];
        
        $authToken = encodeAuthToken($authData);
        
        // Update cookie
        setcookie('auth_token', $authToken, time() + (7 * 24 * 60 * 60), '/', '', true, true);
        
        echo json_encode([
            'success' => true,
            'auth_token' => $authToken,
            'user' => $authData
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Not authenticated'
        ]);
    }
}
?>
