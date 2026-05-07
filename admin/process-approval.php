<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

// Auth Check
if (!isLoggedIn() || !hasRole('admin')) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pg_id = (int)$_POST['pg_id'];
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? sanitize($_POST['reason']) : '';

    if (empty($pg_id) || !in_array($action, ['approve', 'reject'])) {
        setFlash('danger', 'Invalid request.');
        redirect('/admin/approvals.php');
    }

    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE pg_listings SET status = 'approved', rejection_reason = NULL WHERE id = ?");
            $stmt->execute([$pg_id]);
            
            // Notify Owner (Placeholder)
            // sendEmail($owner_email, 'Your PG is now Live!', '...');
            
            setFlash('success', 'Property listing has been approved and is now live.');
        } else {
            $stmt = $pdo->prepare("UPDATE pg_listings SET status = 'rejected', rejection_reason = ? WHERE id = ?");
            $stmt->execute([$reason, $pg_id]);
            
            setFlash('info', 'Property listing has been rejected and the owner has been notified.');
        }
        
        redirect('/admin/approvals.php');

    } catch (PDOException $e) {
        setFlash('danger', 'Error processing request: ' . $e->getMessage());
        redirect('/admin/approvals.php');
    }
} else {
    redirect('/admin/dashboard.php');
}
?>
