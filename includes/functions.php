<?php
// includes/functions.php

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect to a specific page
 */
function redirect($path) {
    header("Location: " . APP_URL . $path);
    exit();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check user role
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Set a flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display flash message
 */
function displayFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert'>
                {$flash['message']}
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

/**
 * Generate OTP
 */
function generateOTP($length = 6) {
    return sprintf("%0{$length}d", mt_rand(0, pow(10, $length) - 1));
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

/**
 * Upload Image
 */
function uploadImage($file, $targetDir = 'uploads/pgs/') {
    $targetFile = $targetDir . time() . '_' . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check if image file is a actual image
    $check = getimagesize($file["tmp_name"]);
    if($check === false) return false;
    
    // Check file size (5MB limit)
    if ($file["size"] > 5000000) return false;
    
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "webp") {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], BASE_PATH . '/' . $targetFile)) {
        return $targetFile;
    }
    return false;
}
/**
 * Get displayable Image URL (handles local vs remote)
 */
function getImageUrl($url) {
    if (empty($url)) return APP_URL . '/assets/images/placeholder.png';
    if (strpos($url, 'http') === 0) return $url;
    return APP_URL . '/' . $url;
}
/**
 * Get a system setting from the database
 */
function getSetting($key, $default = '') {
    global $pdo;
    if (!isset($pdo)) {
        require_once BASE_PATH . '/includes/config/db.php';
    }
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : $default;
}

/**
 * Update a system setting in the database
 */
function updateSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Check if a PG is favorited by a user
 */
function isFavorited($user_id, $pg_id) {
    global $pdo;
    if (empty($user_id) || empty($pg_id)) return false;
    try {
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND pg_id = ?");
        $stmt->execute([$user_id, $pg_id]);
        return (bool) $stmt->fetch();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Send a Real Email (Guaranteed Delivery via Direct SMTP)
 */
function sendEmail($to, $subject, $message) {
    require_once __DIR__ . '/SimpleSMTP.php';

    $htmlMessage = "
    <html>
    <body style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; background-color: #f8fafc; padding: 40px 0;'>
        <div style='max-width: 550px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #4f46e5; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.025em;'>".APP_NAME."</h1>
            </div>
            <div style='background: #ffffff;'>
                {$message}
            </div>
            <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;'>
                <p style='font-size: 12px; color: #94a3b8; margin: 0;'>&copy; ".date('Y')." ".APP_NAME.". All rights reserved.</p>
                <p style='font-size: 11px; color: #cbd5e1; margin-top: 5px;'>This is a secure verification message from CampusStay.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Local Debugging: Save a copy to email-debug.txt
    $debugFile = BASE_PATH . '/email-debug.txt';
    $debugInfo = "Date: " . date('Y-m-d H:i:s') . "\nTo: $to\nSubject: $subject\nMessage: $message\n-------------------\n";
    file_put_contents($debugFile, $debugInfo, FILE_APPEND);

    return SimpleSMTP::send(
        $to, 
        $subject, 
        $htmlMessage, 
        SMTP_FROM, 
        APP_NAME, 
        SMTP_USER, 
        SMTP_PASS
    );
}
?>
