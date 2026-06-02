<?php
// ============================================================
// Database Connection File
// Path: includes/config/db.php
// ============================================================
require_once __DIR__ . '/config.php';
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Set MySQL Timezone to Indian Standard Time (IST)
    $pdo->exec("SET time_zone = '+05:30'");
    // Auto-migration check for verification columns
    try {
        $check = $pdo->query("SHOW COLUMNS FROM pg_listings LIKE 'verification_doc'");
        if ($check->rowCount() == 0) {
            $pdo->exec("ALTER TABLE pg_listings 
                        ADD COLUMN verification_doc VARCHAR(255) NULL AFTER property_type,
                        ADD COLUMN verification_doc_type VARCHAR(50) NULL AFTER verification_doc");
        }
    } catch (Exception $schema_e) {
        // Suppress errors if the table doesn't exist yet during initial setup
    }
    // Auto-migration check for owner payment details columns
    try {
        $check_users = $pdo->query("SHOW COLUMNS FROM users LIKE 'payment_upi_id'");
        if ($check_users->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users 
                        ADD COLUMN payment_upi_id VARCHAR(100) NULL AFTER is_verified,
                        ADD COLUMN payment_bank_name VARCHAR(100) NULL AFTER payment_upi_id,
                        ADD COLUMN payment_account_number VARCHAR(100) NULL AFTER payment_bank_name,
                        ADD COLUMN payment_ifsc_code VARCHAR(50) NULL AFTER payment_account_number");
        }
    } catch (Exception $schema_users_e) {
        // Suppress errors if users table doesn't exist during initial setup
    }
    // Auto-migration: Add razorpay_order_id to bookings (for secure payment verification)
    try {
        $check_order = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'razorpay_order_id'");
        if ($check_order->rowCount() == 0) {
            $pdo->exec("ALTER TABLE bookings 
                        ADD COLUMN razorpay_order_id VARCHAR(100) NULL AFTER payment_status");
        }
    } catch (Exception $schema_order_e) {
        // Suppress errors if bookings table doesn't exist yet
    }
    // Auto-migration: Add detailed payment tracking columns to payments table
    try {
        $check_pay_cols = $pdo->query("SHOW COLUMNS FROM payments LIKE 'user_id'");
        if ($check_pay_cols->rowCount() == 0) {
            $pdo->exec("ALTER TABLE payments 
                        ADD COLUMN user_id INT NULL,
                        ADD COLUMN pg_id INT NULL,
                        ADD COLUMN owner_id INT NULL,
                        ADD COLUMN razorpay_order_id VARCHAR(100) NULL,
                        ADD COLUMN razorpay_payment_id VARCHAR(100) NULL");
        }
    } catch (Exception $schema_pay_e) {
        // Suppress errors if payments table doesn't exist yet
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
