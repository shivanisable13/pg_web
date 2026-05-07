<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = sanitize($_POST['otp']);
    $email = $_SESSION['temp_email'] ?? '';

    if (empty($entered_otp) || empty($email)) {
        setFlash('danger', 'Invalid request.');
        redirect('/auth/register.php');
    }

    try {
        // Find user with this email and OTP
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND otp_code = ? AND is_verified = 0");
        $stmt->execute([$email, $entered_otp]);
        $user = $stmt->fetch();

        if ($user) {
            // Check expiry
            if (strtotime($user['otp_expiry']) < time()) {
                setFlash('danger', 'OTP has expired. Please request a new one.');
                redirect('/auth/verify-otp.php');
            }

            // Mark as verified and clear OTP
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
            $update->execute([$user['id']]);

            // Auto Login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_image'] = $user['profile_image'] ?? 'assets/images/default-avatar.png';

            unset($_SESSION['temp_email']);
            
            setFlash('success', 'Account verified successfully! Welcome to CampusStay.');
            
            // Redirect based on role
            if ($user['role'] === 'admin') redirect('/admin/dashboard.php');
            elseif ($user['role'] === 'owner') redirect('/owner/dashboard.php');
            else redirect('/user/dashboard.php');

        } else {
            setFlash('danger', 'Invalid OTP. Please check your email and try again.');
            redirect('/auth/verify-otp.php');
        }
    } catch (PDOException $e) {
        setFlash('danger', 'Verification failed. Please try again.');
        redirect('/auth/verify-otp.php');
    }
} else {
    redirect('/auth/verify-otp.php');
}
?>
