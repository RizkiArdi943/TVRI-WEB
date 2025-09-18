<?php
require_once 'config/database.php';

function isLoggedIn() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return isset($_SESSION['user_id']);
}

function login($username, $password) {
    global $db;

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $users = $db->findAll('users', ['username' => $username]);
    $user = $users[0] ?? null;

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_role'] = $user['role']; // Add this for consistency
        return true;
    }

    return false;
}

function logout() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Clear session data
    $_SESSION = [];

    // Destroy session
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
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