<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';
$owner_id = $_SESSION['user_id'];

if ($booking_id && in_array($status, ['confirmed', 'cancelled'])) {
    try {
        // Verify this booking belongs to one of the owner's PGs
        $stmt = $pdo->prepare("SELECT b.id FROM bookings b JOIN pg_listings p ON b.pg_id = p.id WHERE b.id = ? AND p.owner_id = ?");
        $stmt->execute([$booking_id, $owner_id]);
        
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$status, $booking_id]);
            
            setFlash('success', "Booking marked as " . ucfirst($status));
        } else {
            setFlash('danger', 'Unauthorized action.');
        }
    } catch (PDOException $e) {
        setFlash('danger', 'Error updating booking.');
    }
}

redirect('/owner/bookings.php');
?>
