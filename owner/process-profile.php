<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $new_password = $_POST['new_password'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$full_name, $phone, $user_id]);
        $_SESSION['user_name'] = $full_name;

        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
        }

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
            $img_url = uploadImage($_FILES['profile_image'], 'uploads/profiles/');
            if ($img_url) {
                $stmt = $pdo->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->execute([$img_url, $user_id]);
                $_SESSION['user_image'] = $img_url;
            }
        }

        setFlash('success', 'Account updated successfully.');
        redirect('/owner/profile.php');

    } catch (PDOException $e) {
        setFlash('danger', 'Error updating account.');
        redirect('/owner/profile.php');
    }
}
?>
