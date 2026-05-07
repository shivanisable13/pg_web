<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = sanitize($_POST['otp']);
    $user_id = $_SESSION['temp_user_id'];

    if (empty($otp) || empty($user_id)) {
        setFlash('danger', 'Invalid request.');
        redirect('/auth/verify-otp.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND otp_code = ?");
    $stmt->execute([$user_id, $otp]);
    $user = $stmt->fetch();

    if ($user) {
        // Activate Account
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
        $stmt->execute([$user_id]);

        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_otp']);

        setFlash('success', 'Account verified! You can now login.');
        redirect('/auth/login.php');
    } else {
        setFlash('danger', 'Invalid or expired OTP.');
        redirect('/auth/verify-otp.php');
    }
} else {
    redirect('/auth/login.php');
}
?>
