<?php
// Clean output buffer to prevent any output before JSON
while (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffer
ob_start();

require_once __DIR__ . '/../../controllers/users.php';

// Get user ID from request
$userId = $_GET['id'] ?? null;

if (!$userId) {
    // Clear any output
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID user tidak ditemukan']);
    exit;
}

$usersController = new UsersController();

// Get user data
$user = $usersController->getUserById($userId);

// Clear any output that might have been generated
ob_clean();

if ($user) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
}
?>
