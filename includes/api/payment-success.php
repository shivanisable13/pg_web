<?php
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../functions.php';

if (!isLoggedIn()) redirect('/auth/login.php');

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$payment_id = isset($_GET['razorpay_payment_id']) ? sanitize($_GET['razorpay_payment_id']) : '';

if (empty($booking_id) || empty($payment_id)) {
    setFlash('danger', 'Invalid payment details.');
    redirect('/index.php');
}

try {
    $pdo->beginTransaction();

    // 1. Fetch Detailed Booking Info for Email
    $stmt = $pdo->prepare("SELECT b.*, r.room_type, p.title as pg_title, p.address as pg_address, u.email as user_email, u.full_name as user_name 
                          FROM bookings b 
                          JOIN rooms r ON b.room_id = r.id 
                          JOIN pg_listings p ON b.pg_id = p.id
                          JOIN users u ON b.user_id = u.id
                          WHERE b.id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if ($booking && $booking['payment_status'] === 'unpaid') {
        // 2. Update Booking Status
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed', payment_status = 'paid' WHERE id = ?");
        $stmt->execute([$booking_id]);

        // 3. Record Payment
        $stmt = $pdo->prepare("INSERT INTO payments (booking_id, transaction_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'Razorpay', 'captured')");
        $stmt->execute([$booking_id, $payment_id, $booking['total_amount']]);

        // 4. Reduce Room Availability
        $stmt = $pdo->prepare("UPDATE rooms SET available_beds = available_beds - 1 WHERE id = ?");
        $stmt->execute([$booking['room_id']]);

        // 5. Create Notification for User
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Booking Confirmed!', 'Your booking for the PG has been confirmed successfully.', 'success')");
        $stmt->execute([$booking['user_id']]);

        $pdo->commit();

        // 6. Send Confirmation Email
        $subject = "Booking Confirmed - " . $booking['pg_title'];
        $move_out = new DateTime($booking['move_in_date']);
        $move_out->modify('+' . $booking['duration_months'] . ' months -1 day');
        
        $emailContent = "
            <div style='text-align: center; margin-bottom: 20px;'>
                <div style='display: inline-block; padding: 10px 20px; background: #ecfdf5; color: #059669; border-radius: 50px; font-weight: bold; font-size: 14px;'>Payment Successful</div>
            </div>
            <h2 style='color: #1e293b; margin-top: 0;'>Booking Confirmed!</h2>
            <p>Hi <strong>{$booking['user_name']}</strong>,</p>
            <p>Great news! Your booking at <strong>{$booking['pg_title']}</strong> has been confirmed. Here is your stay summary:</p>
            
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
        
        setFlash('success', 'Payment successful! A confirmation email has been sent to ' . $booking['user_email']);
        redirect('/user/booking-details.php?id=' . $booking_id);
    } else {
        $pdo->rollBack();
        redirect('/user/bookings.php');
    }

} catch (Exception $e) {
    $pdo->rollBack();
    setFlash('danger', 'Error processing payment: ' . $e->getMessage());
    redirect('/index.php');
}
?>
