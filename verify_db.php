<?php
require_once 'config/database.php';

echo "=== VERIFYING DATABASE FIELDS ===\n";

$result = $db->query('DESCRIBE cases');
echo "Current cases table structure:\n";
foreach($result as $row) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

$requiredFields = ['equipment_name', 'model', 'serial_number', 'damage_date', 'damage_condition'];
$existingFields = array_column($result, 'Field');

echo "\n=== FIELD VERIFICATION ===\n";
foreach($requiredFields as $field) {
    if (in_array($field, $existingFields)) {
        echo "✅ Field '$field' exists\n";
    } else {
        echo "❌ Field '$field' missing\n";
    }
}
?>

