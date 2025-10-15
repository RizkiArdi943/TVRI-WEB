<?php
require_once 'config/database.php';

echo "=== CHECKING DATABASE FIELDS ===\n";

// Check cases table structure
$result = $db->query('DESCRIBE cases');
echo "Current cases table structure:\n";
foreach($result as $row) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// Check which fields we need
$requiredFields = ['equipment_name', 'model', 'serial_number', 'damage_date', 'damage_condition'];
$existingFields = array_column($result, 'Field');

echo "\n=== FIELD CHECK ===\n";
foreach($requiredFields as $field) {
    if (in_array($field, $existingFields)) {
        echo "✅ Field '$field' exists\n";
    } else {
        echo "❌ Field '$field' missing - needs to be added\n";
    }
}

// Check if we need to remove indicator field
if (in_array('indicator', $existingFields)) {
    echo "⚠️  Field 'indicator' exists - consider removing if not needed\n";
}

// Check if we need to remove category_id field
if (in_array('category_id', $existingFields)) {
    echo "⚠️  Field 'category_id' exists - consider removing if not needed\n";
}
?>

