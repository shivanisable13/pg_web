<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)$_POST['room_id'];
    $pg_id = (int)$_POST['pg_id'];
    $rent = (float)$_POST['rent'];
    $total_beds = (int)$_POST['total_beds'];
    $available_beds = (int)$_POST['available_beds'];

    try {
        $stmt = $pdo->prepare("UPDATE rooms SET rent_per_month = ?, total_beds = ?, available_beds = ? WHERE id = ?");
        $stmt->execute([$rent, $total_beds, $available_beds, $room_id]);

        setFlash('success', 'Room details updated successfully.');
        redirect("/owner/manage-rooms.php?id=$pg_id");

    } catch (PDOException $e) {
        setFlash('danger', 'Update failed.');
        redirect("/owner/manage-rooms.php?id=$pg_id");
    }
}
?>
