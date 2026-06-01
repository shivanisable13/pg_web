<?php
// ============================================================
// Database Migration Script
// Path: migrate.php
// ============================================================

require_once __DIR__ . '/includes/config/config.php';
require_once __DIR__ . '/includes/config/db.php';

echo "<h2>Starting Database Migration...</h2>";

try {
    // 1. Check if pg_listings columns exist
    $check = $pdo->query("SHOW COLUMNS FROM pg_listings LIKE 'verification_doc'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE pg_listings 
                    ADD COLUMN verification_doc VARCHAR(255) NULL AFTER property_type,
                    ADD COLUMN verification_doc_type VARCHAR(50) NULL AFTER verification_doc");
        echo "<p style='color: green;'>✔ Successfully added verification columns to pg_listings table!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Verification columns already exist in pg_listings.</p>";
    }

    echo "<h3 style='color: green;'>Migration Completed Successfully!</h3>";
} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Migration Failed: " . $e->getMessage() . "</h3>";
}
?>
