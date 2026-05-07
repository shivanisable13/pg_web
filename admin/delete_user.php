<?php
require_once '../includes/config/config.php';
require_once '../includes/functions.php';
require_once '../includes/config/db.php';

// Auth Check
if (!isLoggedIn() || !hasRole('admin')) {
    setFlash('danger', 'Unauthorized access.');
    redirect('/admin/dashboard.php');
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Check if user is trying to delete themselves
    if ($id == $_SESSION['user_id']) {
        setFlash('danger', 'You cannot delete your own account.');
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$id])) {
                setFlash('success', 'User deleted successfully.');
            } else {
                setFlash('danger', 'Failed to delete user.');
            }
        } catch (PDOException $e) {
            setFlash('danger', 'Error: ' . $e->getMessage());
        }
    }
} else {
    setFlash('danger', 'Invalid user ID.');
}

redirect('/admin/users.php?role=all');
?>
