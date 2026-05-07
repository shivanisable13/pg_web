<?php
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../functions.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'send') {
    $receiver_id = (int)$_POST['receiver_id'];
    $message = sanitize($_POST['message']);
    $sender_id = $_SESSION['user_id'];

    if (!empty($message) && !empty($receiver_id)) {
        $stmt = $pdo->prepare("INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$sender_id, $receiver_id, $message]);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    }
} elseif ($action === 'fetch') {
    $partner_id = (int)$_GET['partner_id'];
    $user_id = $_SESSION['user_id'];

    // Mark as read
    $pdo->prepare("UPDATE chats SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?")->execute([$partner_id, $user_id]);

    // Fetch messages
    $stmt = $pdo->prepare("SELECT * FROM chats 
                          WHERE (sender_id = ? AND receiver_id = ?) 
                          OR (sender_id = ? AND receiver_id = ?) 
                          ORDER BY created_at ASC");
    $stmt->execute([$user_id, $partner_id, $partner_id, $user_id]);
    $messages = $stmt->fetchAll();

    echo json_encode($messages);
}
?>
