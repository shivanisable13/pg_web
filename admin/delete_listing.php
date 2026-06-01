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
    
    try {
        // Fetch title for flash message
        $stmt = $pdo->prepare("SELECT title FROM pg_listings WHERE id = ?");
        $stmt->execute([$id]);
        $pg = $stmt->fetch();

        if ($pg) {
            $stmt = $pdo->prepare("DELETE FROM pg_listings WHERE id = ?");
            if ($stmt->execute([$id])) {
                setFlash('success', 'Property "' . $pg['title'] . '" deleted successfully.');
            } else {
                setFlash('danger', 'Failed to delete property.');
            }
        } else {
            setFlash('danger', 'Property not found.');
        }
    } catch (PDOException $e) {
        setFlash('danger', 'Error: ' . $e->getMessage());
    }
} else {
    setFlash('danger', 'Invalid property ID.');
}

redirect('/admin/listings.php');
?>
