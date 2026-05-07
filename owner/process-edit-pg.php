<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pg_id = (int)$_POST['pg_id'];
    $owner_id = $_SESSION['user_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $city = sanitize($_POST['city']);
    $area = sanitize($_POST['area']);
    $address = sanitize($_POST['address']);
    $gender_allowed = sanitize($_POST['gender_allowed']);
    $amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];

    try {
        $pdo->beginTransaction();

        // Update PG Details (Reset status to pending for re-approval if critical fields change)
        $stmt = $pdo->prepare("UPDATE pg_listings SET title = ?, description = ?, city = ?, area = ?, address = ?, gender_allowed = ?, status = 'pending' WHERE id = ? AND owner_id = ?");
        $stmt->execute([$title, $description, $city, $area, $address, $gender_allowed, $pg_id, $owner_id]);

        // Update Amenities
        $pdo->prepare("DELETE FROM pg_amenities WHERE pg_id = ?")->execute([$pg_id]);
        $stmt = $pdo->prepare("INSERT INTO pg_amenities (pg_id, amenity_id) VALUES (?, ?)");
        foreach ($amenities as $aid) {
            $stmt->execute([$pg_id, $aid]);
        }

        $pdo->commit();
        setFlash('success', 'Property updated and sent for re-approval.');
        redirect('/owner/manage-pgs.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Update failed: ' . $e->getMessage());
        redirect("/owner/edit-pg.php?id=$pg_id");
    }
}
?>
