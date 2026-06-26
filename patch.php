<?php
require_once 'includes/db.php';

try {
    // Add is_read column if it doesn't exist
    $pdo->exec("ALTER TABLE messages ADD COLUMN is_read TINYINT(1) DEFAULT 0");
    echo "Column is_read added successfully.\n";
} catch (PDOException $e) {
    // Ignore error if column already exists (SQLSTATE 42S21: Duplicate column name)
    if ($e->getCode() == '42S21') {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
