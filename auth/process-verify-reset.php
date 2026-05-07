<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = sanitize($_POST['otp']);
    $email = $_SESSION['reset_email'] ?? '';

    if (empty($otp) || empty($email)) {
        setFlash('danger', 'Invalid request.');
        redirect('/auth/forgot-password.php');
    }

    $stmt = $pdo->prepare("SELECT id, otp_expiry FROM users WHERE email = ? AND otp_code = ?");
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();

    if ($user) {
        $expiry = strtotime($user['otp_expiry']);
        if ($expiry > time()) {
            $_SESSION['can_reset_password'] = true;
            $_SESSION['reset_user_id'] = $user['id'];
            
            // Clear OTP
            $stmt = $pdo->prepare("UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE id = ?");
            $stmt->execute([$user['id']]);

            redirect('/auth/reset-password.php');
        } else {
            setFlash('danger', 'This reset code has expired. Please request a new one.');
            redirect('/auth/verify-reset-otp.php');
        }
    } else {
        setFlash('danger', 'Invalid reset code. Please check the code sent to your email.');
        redirect('/auth/verify-reset-otp.php');
    }
} else {
    redirect('/auth/login.php');
}
?>
