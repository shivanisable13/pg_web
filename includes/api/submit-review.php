<?php
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../functions.php';

if (!isLoggedIn()) {
    setFlash('danger', 'Please login to submit a review.');
    redirect('/auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $pg_id = (int)$_POST['pg_id'];
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);

    // Check if user has stayed here (Basic check: has a confirmed booking)
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND pg_id = ? AND status = 'confirmed'");
    $stmt->execute([$user_id, $pg_id]);
    
    if ($stmt->rowCount() === 0) {
        setFlash('danger', 'You can only review PGs where you have a confirmed booking.');
        redirect("/pg-details.php?id=$pg_id");
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (pg_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$pg_id, $user_id, $rating, $comment]);

        setFlash('success', 'Thank you for your review!');
        redirect("/pg-details.php?id=$pg_id");
    } catch (PDOException $e) {
        setFlash('danger', 'Error submitting review.');
        redirect("/pg-details.php?id=$pg_id");
    }
}
?>
