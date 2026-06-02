<?php
// includes/api/payment-success.php
// ============================================================
// SECURE PAYMENT CONFIRMATION
// Verifies Razorpay cryptographic signature before confirming
// ============================================================
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../functions.php';
if (!isLoggedIn()) redirect('/auth/login.php');
// ============================================================
// STEP 1: Accept POST only (GET-based confirmation is exploitable)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('danger', 'Invalid payment request.');
    redirect('/index.php');
}
$booking_id         = isset($_POST['booking_id'])           ? (int)$_POST['booking_id']              : 0;
$razorpay_payment_id = isset($_POST['razorpay_payment_id']) ? sanitize($_POST['razorpay_payment_id']) : '';
$razorpay_order_id  = isset($_POST['razorpay_order_id'])    ? sanitize($_POST['razorpay_order_id'])   : '';
$razorpay_signature = isset($_POST['razorpay_signature'])   ? $_POST['razorpay_signature']            : '';
if (empty($booking_id) || empty($razorpay_payment_id) || empty($razorpay_order_id) || empty($razorpay_signature)) {
    setFlash('danger', 'Incomplete payment data received. Please contact support.');
    redirect('/index.php');
}
// ============================================================
// STEP 2: Cryptographic Signature Verification
// Razorpay signs: HMAC-SHA256(order_id + "|" + payment_id, secret)
// This CANNOT be forged without the private secret key
// ============================================================
$expected_signature = hash_hmac(
    'sha256',
    $razorpay_order_id . '|' . $razorpay_payment_id,
    RAZORPAY_KEY_SECRET
);
if (!hash_equals($expected_signature, $razorpay_signature)) {
    // Log the tampered/fake attempt
    error_log("[CampusStay SECURITY] FAKE PAYMENT ATTEMPT — booking_id: $booking_id | payment_id: $razorpay_payment_id | IP: " . $_SERVER['REMOTE_ADDR']);
    setFlash('danger', 'Payment verification failed. This incident has been logged. If you believe this is an error, contact support.');
    redirect('/checkout.php?booking_id=' . $booking_id);
}
// ============================================================
// STEP 3: Verify the order_id matches what we created for this booking
// ============================================================
$stmt = $pdo->prepare("SELECT razorpay_order_id FROM bookings WHERE id = ? AND user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$stored_order_id = $stmt->fetchColumn();
if ($stored_order_id !== $razorpay_order_id) {
    error_log("[CampusStay SECURITY] ORDER ID MISMATCH — booking_id: $booking_id | expected: $stored_order_id | got: $razorpay_order_id | IP: " . $_SERVER['REMOTE_ADDR']);
    setFlash('danger', 'Payment order mismatch. Please contact support.');
    redirect('/index.php');
}
// ============================================================
// STEP 4: Signature is valid — Process the booking
// ============================================================
try {
    $pdo->beginTransaction();
    // Fetch full booking info
    $stmt = $pdo->prepare("
        SELECT b.*, r.room_type, p.title as pg_title, p.address as pg_address,
               p.owner_id, u.email as user_email, u.full_name as user_name
        FROM bookings b
        JOIN rooms r ON b.room_id = r.id
        JOIN pg_listings p ON b.pg_id = p.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();
    if (!$booking) {
        $pdo->rollBack();
        setFlash('danger', 'Booking not found.');
        redirect('/index.php');
    }
    if ($booking['payment_status'] === 'paid') {
        $pdo->rollBack();
        setFlash('warning', 'This booking was already confirmed.');
        redirect('/user/booking-details.php?id=' . $booking_id);
    }
    // Fetch commission percentage from settings
    $comm_stmt = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'commission_percentage'");
    $comm_percentage = (float)($comm_stmt->fetchColumn() ?: 10);
    // Calculate split amounts
    $total_amount = (float)$booking['total_amount'];
    $commission_amount = ($total_amount * $comm_percentage) / 100;
    $owner_amount = $total_amount - $commission_amount;
    // Update booking status
    $pdo->prepare("UPDATE bookings SET status = 'confirmed', payment_status = 'paid' WHERE id = ?")
        ->execute([$booking_id]);
    // Insert payment record
    $pdo->prepare("INSERT INTO payments (booking_id, user_id, pg_id, owner_id, razorpay_order_id, razorpay_payment_id, transaction_id, amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Razorpay', 'captured')")
    $pdo->prepare("INSERT INTO payments (booking_id, user_id, pg_id, owner_id, razorpay_order_id, razorpay_payment_id, transaction_id, amount, commission_amount, owner_amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Razorpay', 'captured')")
        ->execute([
            $booking_id,
            $booking['user_id'],
            $booking['pg_id'],
            $booking['owner_id'],
            $razorpay_order_id,
            $razorpay_payment_id,
            $razorpay_payment_id,
            $booking['total_amount']
            $total_amount,
            $commission_amount,
            $owner_amount
        ]);
    // Reduce available beds
    $pdo->prepare("UPDATE rooms SET available_beds = available_beds - 1 WHERE id = ? AND available_beds > 0")
        ->execute([$booking['room_id']]);
    // Notify student
    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Booking Confirmed!', 'Your booking has been confirmed and payment received successfully.', 'success')")
        ->execute([$booking['user_id']]);
    // Notify owner with full details
    $owner_msg = "New booking confirmed! {$booking['user_name']} has paid ₹" . number_format($booking['total_amount']) .
    // Notify owner with full details (including commission split)
    $owner_msg = "New booking confirmed! {$booking['user_name']} has paid ₹" . number_format($total_amount) . 
                 " for a " . ucfirst($booking['room_type']) . " Sharing room at '{$booking['pg_title']}'. " .
                 "Admin Commission ({$comm_percentage}%): ₹" . number_format($commission_amount) . ". " .
                 "Your Earnings: ₹" . number_format($owner_amount) . ". " .
                 "Razorpay Payment ID: {$razorpay_payment_id}.";
    $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Payment Received ✓', ?, 'info')")
        ->execute([$booking['owner_id'], $owner_msg]);
    $pdo->commit();
    // ============================================================
    // STEP 5: Send confirmation email to student
    // ============================================================
    $subject = "Booking Confirmed — " . $booking['pg_title'];
    $move_out = new DateTime($booking['move_in_date']);
    $move_out->modify('+' . $booking['duration_months'] . ' months -1 day');
    $emailContent = "
        <div style='text-align:center;margin-bottom:20px;'>
            <div style='display:inline-block;padding:10px 20px;background:#ecfdf5;color:#059669;border-radius:50px;font-weight:bold;font-size:14px;'>
                ✓ Payment Verified &amp; Confirmed
            </div>
        </div>
        <h2 style='color:#1e293b;margin-top:0;'>Booking Confirmed!</h2>
        <p>Hi <strong>{$booking['user_name']}</strong>,</p>
        <p>Your payment has been <strong>verified and confirmed</strong> by CampusStay. Here is your stay summary:</p>
        <div style='background:#f8fafc;border:1px solid #e2e8f0;padding:25px;border-radius:16px;margin:25px 0;'>
            <div style='margin-bottom:15px;border-bottom:1px dashed #cbd5e1;padding-bottom:15px;'>
                <span style='color:#64748b;font-size:12px;'>Booking ID</span><br>
                <strong style='font-size:18px;color:#4f46e5;'>CS-{$booking_id}</strong>
            </div>
            <table style='width:100%;font-size:14px;'>
                <tr>
                    <td style='padding:6px 0;color:#64748b;'>Property</td>
                    <td style='padding:6px 0;text-align:right;font-weight:bold;'>" . htmlspecialchars($booking['pg_title']) . "</td>
                </tr>
                <tr>
                    <td style='padding:6px 0;color:#64748b;'>Room Type</td>
                    <td style='padding:6px 0;text-align:right;font-weight:bold;'>" . ucfirst($booking['room_type']) . " Sharing</td>
                </tr>
                <tr>
                    <td style='padding:6px 0;color:#64748b;'>Move-in</td>
                    <td style='padding:6px 0;text-align:right;font-weight:bold;'>" . date('d M, Y', strtotime($booking['move_in_date'])) . "</td>
                </tr>
                <tr>
                    <td style='padding:6px 0;color:#64748b;'>Move-out</td>
                    <td style='padding:6px 0;text-align:right;font-weight:bold;'>" . $move_out->format('d M, Y') . "</td>
                </tr>
                <tr>
                    <td style='padding:6px 0;color:#64748b;'>Duration</td>
                    <td style='padding:6px 0;text-align:right;font-weight:bold;'>{$booking['duration_months']} Month(s)</td>
                </tr>
                <tr>
                    <td style='padding:6px 0;color:#64748b;'>Payment ID</td>
                    <td style='padding:6px 0;text-align:right;'><code style='color:#4f46e5;'>{$razorpay_payment_id}</code></td>
                </tr>
                <tr>
                    <td style='padding:15px 0 5px;color:#1e293b;font-weight:bold;font-size:16px;'>Amount Paid</td>
                    <td style='padding:15px 0 5px;text-align:right;font-weight:800;color:#4f46e5;font-size:18px;'>₹" . number_format($booking['total_amount']) . "</td>
                </tr>
            </table>
        </div>
        <div style='text-align:center;margin-top:30px;'>
            <a href='" . APP_URL . "/user/booking-details.php?id={$booking_id}'
               style='background:#4f46e5;color:#fff;padding:14px 30px;text-decoration:none;border-radius:50px;font-weight:bold;display:inline-block;'>
                View Booking Receipt
            </a>
        </div>
    ";
    sendEmail($booking['user_email'], $subject, $emailContent);
    setFlash('success', 'Payment verified and booking confirmed! A receipt has been sent to ' . $booking['user_email']);
    redirect('/user/booking-details.php?id=' . $booking_id);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('[CampusStay] Payment processing error: ' . $e->getMessage());
    setFlash('danger', 'An error occurred while confirming your booking. Your payment was captured — please contact support with Payment ID: ' . $razorpay_payment_id);
    redirect('/index.php');
}
?>
