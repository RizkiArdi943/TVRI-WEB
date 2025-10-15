<?php
require_once 'config/database.php';

echo "Adding damage_date field...\n";

try {
    $result = $db->query('ALTER TABLE cases ADD COLUMN damage_date DATE NOT NULL DEFAULT (CURDATE())');
    echo "✅ damage_date field added successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

