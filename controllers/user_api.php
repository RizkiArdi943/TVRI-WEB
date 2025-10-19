<?php
// Clean API endpoint for user operations
// This file should be called directly

// Suppress all warnings and errors
error_reporting(0);
ini_set('display_errors', 0);

// Get the action from URL
$action = $_GET['action'] ?? 'get';
$userId = $_GET['id'] ?? null;

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/users.php';

$usersController = new UsersController();

// Handle different actions
if ($action === 'get') {
    // Get user data
    if (!$userId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID user tidak ditemukan']);
        exit;
    }
    
    $user = $usersController->getUserById($userId);
    
    if ($user) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    }
    
} elseif ($action === 'create') {
    // Create user
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $result = $usersController->create($_POST);
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
    
} elseif ($action === 'update') {
    // Update user
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId) {
        $result = $usersController->update($userId, $_POST);
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed or ID not provided']);
    }
    
} elseif ($action === 'delete') {
    // Delete user
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && $userId) {
        $result = $usersController->delete($userId);
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Method not allowed or ID not provided']);
    }
    
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

exit;
?>

