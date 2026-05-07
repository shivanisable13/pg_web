<?php
// includes/api/toggle-favorite.php

// Use absolute paths for reliability
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/functions.php';

header('Content-Type: application/json');

// Ensure session is started (config.php handles this but we double check)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login to save PGs']);
    exit;
}

$user_id = $_SESSION['user_id'];
$pg_id = isset($_POST['pg_id']) ? (int)$_POST['pg_id'] : 0;

if (!$pg_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid property ID']);
    exit;
}

try {
    // Check if already favorited
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND pg_id = ?");
    $stmt->execute([$user_id, $pg_id]);
    $fav = $stmt->fetch();

    if ($fav) {
        // Remove from favorites
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
        $stmt->execute([$fav['id']]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add to favorites
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, pg_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $pg_id]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
