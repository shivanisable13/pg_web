<?php
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

session_unset();
session_destroy();

// Redirect to login with a message
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
setFlash('info', 'You have been logged out.');
header("Location: " . APP_URL . "/auth/login.php");
exit();
?>
