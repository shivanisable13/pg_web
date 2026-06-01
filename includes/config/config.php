<?php
// ============================================================
// CampusStay Configuration File
// Path: includes/config/config.php
// ============================================================

// ============================================================
// TIMEZONE
// ============================================================

date_default_timezone_set('Asia/Kolkata');

// ============================================================
// APPLICATION SETTINGS
// ============================================================

define('APP_NAME', 'CampusStay');

// Dynamic APP URL Detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? "https"
    : "http";

$host = $_SERVER['HTTP_HOST'];

$current_dir = str_replace('\\', '/', __DIR__);
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

$relative_path = str_replace($doc_root, '', $current_dir);

$project_root = str_replace('/includes/config', '', $relative_path);

define('APP_URL', $protocol . "://" . $host . $project_root);

// Base Project Path
define('BASE_PATH', dirname(__DIR__, 2));

// ============================================================
// DATABASE CONFIGURATION
// ============================================================
// Supports:
// 1. Docker ENV variables
// 2. Local XAMPP fallback
// ============================================================

define('DB_HOST', getenv('DB_HOST') ?: 'campusstay_db');

define('DB_NAME', getenv('DB_NAME') ?: 'campusstay');

define('DB_USER', getenv('DB_USER') ?: 'campusstay_user');

define('DB_PASS', getenv('DB_PASS') ?: 'CampusStay2024');

// ============================================================
// DATABASE CONNECTION (PDO)
// ============================================================

try {

    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );

    // Enable Exception Mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Default Fetch Mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Database Connection Failed: " . $e->getMessage());
}

// ============================================================
// PAYMENT GATEWAY (RAZORPAY)
// ============================================================

define('RAZORPAY_KEY_ID', 'rzp_test_SjNPEU6SPz0j2X');

define('RAZORPAY_KEY_SECRET', 'your_secret_key');

// ============================================================
// GOOGLE MAPS API
// ============================================================

define('GOOGLE_MAPS_KEY', 'your_google_maps_key');

// ============================================================
// EMAIL CONFIGURATION
// ============================================================

define('SMTP_HOST', 'smtp.gmail.com');

define('SMTP_PORT', 587);

define('SMTP_USER', 'shivanisable031@gmail.com');

// Gmail App Password
define('SMTP_PASS', 'muxg xvfi hysb cycf');

define('SMTP_FROM', 'shivanisable031@gmail.com');

define('SMTP_FROM_NAME', 'CampusStay');

// ============================================================
// SESSION SETTINGS
// ============================================================

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}

// ============================================================
// SECURITY HEADERS
// ============================================================

// Prevent Clickjacking
header('X-Frame-Options: SAMEORIGIN');

// Prevent MIME Sniffing
header('X-Content-Type-Options: nosniff');

// Basic XSS Protection
header('X-XSS-Protection: 1; mode=block');

// Hide PHP Version
header_remove('X-Powered-By');

// ============================================================
// ERROR REPORTING
// ============================================================

// DEVELOPMENT MODE
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PRODUCTION MODE (use later)
// error_reporting(0);
// ini_set('display_errors', 0);

// ============================================================
// DEFAULT SETTINGS
// ============================================================

// Default User Image
define('DEFAULT_USER_IMAGE', 'default_user.png');

// Records Per Page
define('RECORDS_PER_PAGE', 10);

// OTP Expiry
define('OTP_EXPIRY_MINUTES', 10);

// Maximum Upload Size (5MB)
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// ============================================================
// FILE PATHS
// ============================================================

define('UPLOAD_PATH', BASE_PATH . '/uploads/');

define('USER_UPLOAD_PATH', UPLOAD_PATH . 'profiles/');

define('PROPERTY_UPLOAD_PATH', UPLOAD_PATH . 'pgs/');

// ============================================================
// ALLOWED FILE TYPES
// ============================================================

$allowed_extensions = [
    'jpg',
    'jpeg',
    'png',
    'webp'
];

// ============================================================
// END CONFIG FILE
// ============================================================
?>
