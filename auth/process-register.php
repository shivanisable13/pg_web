<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $role = sanitize($_POST['role']);

    // Basic Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        setFlash('danger', 'All fields are required.');
        redirect('/auth/register.php');
    }

    // Check if email or phone already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    if ($stmt->rowCount() > 0) {
        setFlash('danger', 'Email or Phone number already registered.');
        redirect('/auth/register.php');
    }

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate OTP for verification
    $otp = generateOTP();
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Insert User
    try {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, phone, password, role, otp_code, otp_expiry) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $phone, $hashed_password, $role, $otp, $otp_expiry]);
        
        // Real Email Delivery
        $emailSubject = "Verify your CampusStay Account";
        $emailBody = "
            <p>Hi <strong>{$full_name}</strong>,</p>
            <p>Thank you for joining CampusStay. To complete your registration, please use the OTP below:</p>
            <div style='background: #f1f5f9; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0;'>
                <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #4f46e5;'>{$otp}</span>
            </div>
            <p>This code will expire in 15 minutes.</p>
        ";

        sendEmail($email, $emailSubject, $emailBody);
        
        $_SESSION['temp_email'] = $email;

        setFlash('success', 'Registration successful! A verification code has been sent to your email.');
        redirect('/auth/verify-otp.php');
    } catch (PDOException $e) {
        setFlash('danger', 'Registration failed: ' . $e->getMessage());
        redirect('/auth/register.php');
    }
} else {
    redirect('/auth/register.php');
}
?>
