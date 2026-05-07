<?php
// includes/config/config.php

// Set Timezone
date_default_timezone_set('Asia/Kolkata');

// App Settings
define('APP_NAME', 'CampusStay');

// Robust Dynamic APP_URL Detection
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$current_dir = str_replace('\\', '/', __DIR__);
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$relative_path = str_replace($doc_root, '', $current_dir);
$project_root = str_replace('/includes/config', '', $relative_path);
define('APP_URL', $protocol . "://" . $host . $project_root);

define('BASE_PATH', dirname(__DIR__, 2));

// Payment Gateway (Razorpay) - Replace with real keys
define('RAZORPAY_KEY_ID', 'rzp_test_SjNPEU6SPz0j2X');
define('RAZORPAY_KEY_SECRET', 'your_secret');

// Google Maps API
define('GOOGLE_MAPS_KEY', 'your_google_maps_key');

// Email Settings
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'shivanisable031@gmail.com');
define('SMTP_PASS', 'muxg xvfi hysb cycf');
define('SMTP_FROM', 'shivanisable031@gmail.com');

// Session Settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (Development)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
