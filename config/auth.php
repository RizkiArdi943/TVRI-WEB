<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';

function isLoggedIn() {
    // Start secure session
    startSecureSession();
    
    // Check if session is valid
    if (!isSessionValid()) {
        return false;
    }
    
    // Extend session on each check
    extendSession();
    
    return isset($_SESSION['user_id']);
}

function login($username, $password) {
    global $db;

    // Start secure session
    startSecureSession();

    $users = $db->findAll('users', ['username' => $username]);
    $user = $users[0] ?? null;

    if ($user && password_verify($password, $user['password'])) {
        // Clear any existing session data
        $_SESSION = [];
        
        // Set user data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        return true;
    }

    return false;
}

function logout() {
    // Clear session data
    clearSession();
    
    // Clear session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    header('Location: index.php?page=login');
    exit();
}

function getCurrentUser() {
    global $db;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    return $db->find('users', $_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php?page=login');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php?page=dashboard');
        exit();
    }
} 