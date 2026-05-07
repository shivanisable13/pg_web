<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) redirect('/auth/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $new_password = $_POST['new_password'];

    try {
        // Update Info
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $user_id]);
        $_SESSION['user_name'] = $full_name;

        // Update Password if provided
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
        }

        // Handle Image Upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            if (!is_dir(BASE_PATH . '/uploads/profiles')) {
                mkdir(BASE_PATH . '/uploads/profiles', 0777, true);
            }
            $img_url = uploadImage($_FILES['profile_image'], 'uploads/profiles/');
            if ($img_url) {
                $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->execute([$img_url, $user_id]);
                $_SESSION['user_image'] = $img_url;
            }
        }

        setFlash('success', 'Profile updated successfully.');
        redirect('/user/profile.php');

    } catch (PDOException $e) {
        setFlash('danger', 'Error updating profile.');
        redirect('/user/profile.php');
    }
}
?>
