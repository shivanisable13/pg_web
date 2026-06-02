<?php
// includes/api/process-direct-payment.php
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../functions.php';
if (!isLoggedIn()) {
    redirect('/auth/login.php');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$transaction_id = isset($_POST['transaction_id']) ? sanitize($_POST['transaction_id']) : '';
if (empty($booking_id) || empty($transaction_id)) {
    setFlash('danger', 'Please enter your Transaction Reference ID.');
    redirect("/checkout.php?booking_id=$booking_id");
}
try {
    $pdo->beginTransaction();
    // 1. Fetch Booking details and Owner ID
    $stmt = $pdo->prepare("SELECT b.*, r.room_type, p.title as pg_title, p.address as pg_address, p.owner_id as owner_id, u.email as user_email, u.full_name as user_name 
                          FROM bookings b 
                          JOIN rooms r ON b.room_id = r.id 
                          JOIN pg_listings p ON b.pg_id = p.id
                          JOIN users u ON b.user_id = u.id
                          WHERE b.id = ? AND b.user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();
    if (!$booking) {
        $pdo->rollBack();
        setFlash('danger', 'Booking details not found.');
        redirect('/index.php');
    }
    if ($booking['payment_status'] !== 'unpaid') {
        $pdo->rollBack();
        setFlash('warning', 'This booking has already been processed.');
        redirect('/user/bookings.php');
    }
    // 2. Update Booking Status
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed', payment_status = 'paid' WHERE id = ?");
    $stmt->execute([$booking_id]);
    // 3. Insert Payment Record
    $stmt = $pdo->prepare("INSERT INTO payments (booking_id, transaction_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'Direct Transfer', 'captured')");
    $stmt->execute([$booking_id, $transaction_id, $booking['total_amount']]);
    $stmt = $pdo->prepare("INSERT INTO payments (booking_id, user_id, pg_id, owner_id, razorpay_order_id, razorpay_payment_id, transaction_id, amount, payment_method, payment_status) VALUES (?, ?, ?, ?, NULL, NULL, ?, ?, 'Direct Transfer', 'captured')");
    $stmt->execute([$booking_id, $booking['user_id'], $booking['pg_id'], $booking['owner_id'], $transaction_id, $booking['total_amount']]);
    // 4. Reduce Room Bed Availability
    $stmt = $pdo->prepare("UPDATE rooms SET available_beds = available_beds - 1 WHERE id = ?");
    $stmt->execute([$booking['room_id']]);
    // 5. Create System Notification for User (Student)
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Booking Confirmed!', 'Your booking for the PG has been confirmed successfully via Direct Transfer.', 'success')");
    $stmt->execute([$booking['user_id']]);
    // 6. Create System Notification for Owner
    $ownerMessage = "User {$booking['user_name']} has booked a room (" . ucfirst($booking['room_type']) . " Sharing) in your PG '{$booking['pg_title']}'. A payment of ₹" . number_format($booking['total_amount']) . " was made directly to your account. Transaction ID: $transaction_id.";
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'New Booking & Payment Received', ?, 'info')");
    $stmt->execute([$booking['owner_id'], $ownerMessage]);
    $pdo->commit();
    // 7. Send Confirmation Email to Student
    $subject = "Booking Confirmed - " . $booking['pg_title'];
    $move_out = new DateTime($booking['move_in_date']);
    $move_out->modify('+' . $booking['duration_months'] . ' months -1 day');
    
    $emailContent = "
        <div style='text-align: center; margin-bottom: 20px;'>
            <div style='display: inline-block; padding: 10px 20px; background: #ecfdf5; color: #059669; border-radius: 50px; font-weight: bold; font-size: 14px;'>Direct Payment Received</div>
        </div>
        <h2 style='color: #1e293b; margin-top: 0;'>Booking Confirmed!</h2>
        <p>Hi <strong>{$booking['user_name']}</strong>,</p>
        <p>Great news! Your booking at <strong>{$booking['pg_title']}</strong> has been confirmed. The payment has been sent directly to the property owner's account.</p>
        
        <div style='background: #f8fafc; border: 1px solid #e2e8f0; padding: 25px; border-radius: 16px; margin: 25px 0;'>
            <div style='margin-bottom: 15px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 15px;'>
                <span style='color: #64748b; font-size: 12px; text-uppercase;'>Booking ID</span><br>
                <strong style='font-size: 18px; color: #4f46e5;'>CS-{$booking_id}</strong>
            </div>
            <table style='width: 100%; font-size: 14px;'>
                <tr>
                    <td style='padding: 5px 0; color: #64748b;'>Room Type</td>
                    <td style='padding: 5px 0; text-align: right; font-weight: bold;'>" . ucfirst($booking['room_type']) . " Sharing</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0; color: #64748b;'>Move-in Date</td>
                    <td style='padding: 5px 0; text-align: right; font-weight: bold;'>" . date('d M, Y', strtotime($booking['move_in_date'])) . "</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0; color: #64748b;'>Move-out Date</td>
                    <td style='padding: 5px 0; text-align: right; font-weight: bold;'>" . $move_out->format('d M, Y') . "</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0; color: #64748b;'>Duration</td>
                    <td style='padding: 5px 0; text-align: right; font-weight: bold;'>{$booking['duration_months']} Months</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0; color: #64748b;'>Payment Method</td>
                    <td style='padding: 5px 0; text-align: right; font-weight: bold;'>Direct Transfer</td>
                </tr>
                <tr>
                    <td style='padding: 5px 0; color: #64748b;'>Transaction ID</td>
                    <td style='padding: 5px 0; text-align: right; font-weight: bold;'><code>$transaction_id</code></td>
                </tr>
                <tr>
                    <td style='padding: 15px 0 5px 0; color: #1e293b; font-weight: bold; font-size: 16px;'>Amount Paid</td>
                    <td style='padding: 15px 0 5px 0; text-align: right; font-weight: 800; color: #4f46e5; font-size: 18px;'>₹" . number_format($booking['total_amount']) . "</td>
                </tr>
            </table>
        </div>
        
        <p style='color: #64748b; font-size: 14px;'>Please present this email or the digital receipt in your dashboard during check-in.</p>
        
        <div style='text-align: center; margin-top: 30px;'>
            <a href='" . APP_URL . "/user/booking-details.php?id={$booking_id}' style='background: #4f46e5; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);'>Download Digital Receipt</a>
        </div>
    ";
    sendEmail($booking['user_email'], $subject, $emailContent);
    setFlash('success', 'Direct payment submitted successfully! Your booking is confirmed.');
    redirect('/user/booking-details.php?id=' . $booking_id);
} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('danger', 'Error processing payment: ' . $e->getMessage());
    redirect('/index.php');
}
?>
