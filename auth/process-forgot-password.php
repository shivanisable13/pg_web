<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);

    if (empty($email)) {
        setFlash('danger', 'Email is required.');
        redirect('/auth/forgot-password.php');
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate OTP
        $otp = generateOTP();
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // Update User with OTP
        $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
        $stmt->execute([$otp, $otp_expiry, $user['id']]);

        // Send Email
        $subject = "Reset your CampusStay Password";
        $message = "
            <h2 style='color: #4f46e5;'>Password Reset Request</h2>
            <p>Hi <strong>{$user['full_name']}</strong>,</p>
            <p>We received a request to reset your password. Please use the verification code below to proceed:</p>
            <div style='background: #f1f5f9; padding: 20px; text-align: center; border-radius: 12px; margin: 25px 0;'>
                <span style='font-size: 32px; font-weight: 800; letter-spacing: 5px; color: #4f46e5;'>{$otp}</span>
            </div>
            <p>This code will expire in 15 minutes. If you did not request this, please ignore this email.</p>
        ";

        if (sendEmail($email, $subject, $message)) {
            $_SESSION['reset_email'] = $email;
            setFlash('success', 'A reset code has been sent to your email.');
            redirect('/auth/verify-reset-otp.php');
        } else {
            setFlash('danger', 'Failed to send reset code. Please try again later.');
            redirect('/auth/forgot-password.php');
        }
    } else {
        // For security, don't reveal if user exists, but here for a school project it's better to show error
        setFlash('danger', 'Email address not found.');
        redirect('/auth/forgot-password.php');
    }
} else {
    redirect('/auth/login.php');
}
?>
