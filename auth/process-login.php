<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        setFlash('danger', 'Please enter email and password.');
        redirect('/auth/login.php');
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if (!$user['is_verified']) {
            $_SESSION['temp_user_id'] = $user['id'];
            setFlash('warning', 'Please verify your account first.');
            redirect('/auth/verify-otp.php');
        }

        // Set Sessions
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_image'] = $user['profile_image'];

        setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');

        // Redirect based on role
        if ($user['role'] === 'admin') {
            redirect('/admin/dashboard.php');
        } elseif ($user['role'] === 'owner') {
            redirect('/owner/dashboard.php');
        } else {
            redirect('/user/dashboard.php');
        }
    } else {
        setFlash('danger', 'Invalid email or password.');
        redirect('/auth/login.php');
    }
} else {
    redirect('/auth/login.php');
}
?>
