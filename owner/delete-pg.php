<?php
require_once '../includes/config/config.php';
require_once '../includes/functions.php';
require_once '../includes/config/db.php';

// Auth Check
if (!isLoggedIn() || !hasRole('owner')) {
    setFlash('danger', 'Unauthorized access.');
    redirect('/auth/login.php');
}

$owner_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT title FROM pg_listings WHERE id = ? AND owner_id = ?");
        $stmt->execute([$id, $owner_id]);
        $pg = $stmt->fetch();

        if ($pg) {
            $stmt = $pdo->prepare("DELETE FROM pg_listings WHERE id = ?");
            if ($stmt->execute([$id])) {
                setFlash('success', 'Property "' . $pg['title'] . '" deleted successfully.');
            } else {
                setFlash('danger', 'Failed to delete property.');
            }
        } else {
            setFlash('danger', 'Property not found or access denied.');
        }
    } catch (PDOException $e) {
        setFlash('danger', 'Error: ' . $e->getMessage());
    }
} else {
    setFlash('danger', 'Invalid property ID.');
}

redirect('/owner/manage-pgs.php');
?>
