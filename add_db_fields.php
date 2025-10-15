<?php
require_once 'config/database.php';

echo "=== ADDING NEW DATABASE FIELDS ===\n";

try {
    // Add new fields
    $fields = [
        'equipment_name' => 'VARCHAR(255) NOT NULL DEFAULT ""',
        'model' => 'VARCHAR(255) NOT NULL DEFAULT ""',
        'serial_number' => 'VARCHAR(255) NOT NULL DEFAULT ""',
        'damage_date' => 'DATE NOT NULL',
        'damage_condition' => 'ENUM("light", "moderate", "severe") NOT NULL DEFAULT "light"'
    ];

    foreach ($fields as $fieldName => $fieldDefinition) {
        $sql = "ALTER TABLE cases ADD COLUMN {$fieldName} {$fieldDefinition}";
        echo "Adding field: {$fieldName}\n";
        $result = $db->query($sql);
        if ($result !== false) {
            echo "✅ Field '{$fieldName}' added successfully\n";
        } else {
            echo "❌ Failed to add field '{$fieldName}'\n";
        }
    }

    echo "\n=== DATABASE UPDATE COMPLETED ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

