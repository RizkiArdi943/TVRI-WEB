<?php
// Clean output buffer to prevent any output before JSON
while (ob_get_level()) {
    ob_end_clean();
}

// Start fresh output buffer
ob_start();

require_once __DIR__ . '/../../controllers/users.php';

// Handle different HTTP methods
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'create';

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
    
    // Clear any output
    ob_clean();
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
    
    // Clear any output
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// If not valid request, redirect to users index
ob_clean();
header('Location: index.php?page=users');
exit;
?>
