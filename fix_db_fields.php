<?php
require_once 'config/database.php';

echo "=== FIXING DATABASE FIELDS ===\n";

try {
    // Fix the fields with correct syntax
    $fields = [
        'equipment_name' => 'VARCHAR(255) NOT NULL',
        'model' => 'VARCHAR(255) NOT NULL', 
        'serial_number' => 'VARCHAR(255) NOT NULL',
        'damage_date' => 'DATE NOT NULL',
        'damage_condition' => "ENUM('light', 'moderate', 'severe') NOT NULL DEFAULT 'light'"
    ];

    foreach ($fields as $fieldName => $fieldDefinition) {
        // First check if field exists
        $checkSql = "SHOW COLUMNS FROM cases LIKE '{$fieldName}'";
        $exists = $db->query($checkSql);
        
        if (empty($exists)) {
            $sql = "ALTER TABLE cases ADD COLUMN {$fieldName} {$fieldDefinition}";
            echo "Adding field: {$fieldName}\n";
            $result = $db->query($sql);
            if ($result !== false) {
                echo "✅ Field '{$fieldName}' added successfully\n";
            } else {
                echo "❌ Failed to add field '{$fieldName}'\n";
            }
        } else {
            echo "⚠️  Field '{$fieldName}' already exists\n";
        }
    }

    echo "\n=== DATABASE UPDATE COMPLETED ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

