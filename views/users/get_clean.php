<?php
// Clean JSON endpoint for getting user data
// Suppress all warnings and errors
error_reporting(0);
ini_set('display_errors', 0);

// Get user ID from request
$userId = $_GET['id'] ?? null;

if (!$userId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID user tidak ditemukan']);
    exit;
}

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/users.php';

$usersController = new UsersController();

// Get user data
$user = $usersController->getUserById($userId);

if ($user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
}
?>
