<?php
// Clean JSON endpoint for user operations
// Suppress all warnings and errors
error_reporting(0);
ini_set('display_errors', 0);

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'create';

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/users.php';

$usersController = new UsersController();

if ($method === 'POST') {
    if ($action === 'create') {
        $result = $usersController->create($_POST);
    } elseif ($action === 'update') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $result = $usersController->update($id, $_POST);
        } else {
            $result = ['success' => false, 'message' => 'ID user tidak ditemukan'];
        }
    } else {
        $result = ['success' => false, 'message' => 'Aksi tidak valid'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if ($id) {
        $result = $usersController->delete($id);
    } else {
        $result = ['success' => false, 'message' => 'ID user tidak ditemukan'];
    }
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// If not valid request, redirect to users index
header('Location: index.php?page=users');
exit;
?>
