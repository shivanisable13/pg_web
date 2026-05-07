<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

$owner_id = $_SESSION['user_id'];

// Simulation of report generation
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="revenue_report_'.date('Y-m-d').'.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Booking ID', 'Student Name', 'PG Title', 'Amount', 'Date', 'Status']);

$stmt = $pdo->prepare("SELECT b.id, u.full_name, pg.title, b.total_amount, b.booking_date, b.status 
                      FROM bookings b 
                      JOIN users u ON b.user_id = u.id 
                      JOIN pg_listings pg ON b.pg_id = pg.id 
                      WHERE pg.owner_id = ?");
$stmt->execute([$owner_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
