<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pg_id = $_POST['pg_id'];
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);
    $owner_id = $_SESSION['user_id'];

    try {
        // Find all students who have a confirmed booking for the selected PG(s)
        if ($pg_id === 'all') {
            $stmt = $pdo->prepare("SELECT DISTINCT user_id FROM bookings b 
                                 JOIN pg_listings p ON b.pg_id = p.id 
                                 WHERE p.owner_id = ? AND b.status = 'confirmed'");
            $stmt->execute([$owner_id]);
        } else {
            $stmt = $pdo->prepare("SELECT DISTINCT user_id FROM bookings 
                                 WHERE pg_id = ? AND status = 'confirmed'");
            $stmt->execute([$pg_id]);
        }
        
        $tenants = $stmt->fetchAll();

        if (empty($tenants)) {
            setFlash('warning', 'No active tenants found for the selected property.');
            redirect('/owner/send-notification.php');
        }

        // Insert notification for each tenant
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
        
        foreach ($tenants as $tenant) {
            $notifStmt->execute([$tenant['user_id'], $subject, $message]);
        }

        setFlash('success', 'Notification sent successfully to ' . count($tenants) . ' tenants.');
        redirect('/owner/dashboard.php');
    } catch (Exception $e) {
        setFlash('danger', 'Error: ' . $e->getMessage());
        redirect('/owner/send-notification.php');
    }
}
?>
