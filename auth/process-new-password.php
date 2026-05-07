<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['reset_user_id'] ?? '';

    if (empty($password) || empty($confirm_password) || empty($user_id)) {
        setFlash('danger', 'All fields are required.');
        redirect('/auth/reset-password.php');
    }

    if ($password !== $confirm_password) {
        setFlash('danger', 'Passwords do not match.');
        redirect('/auth/reset-password.php');
    }

    // Hash and Update
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    
    if ($stmt->execute([$hashed_password, $user_id])) {
        // Clear reset session
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['can_reset_password']);

        setFlash('success', 'Password updated successfully. You can now login.');
        redirect('/auth/login.php');
    } else {
        setFlash('danger', 'Failed to update password. Please try again.');
        redirect('/auth/reset-password.php');
    }
} else {
    redirect('/auth/login.php');
}
?>
