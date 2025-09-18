<?php
require_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        $db->delete('cases', $id);
        header('Location: index.php?page=cases&success=1');
    } catch (Exception $e) {
        header('Location: index.php?page=cases&error=1');
    }
} else {
    header('Location: index.php?page=cases');
}

exit();
?> 