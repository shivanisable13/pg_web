<?php
// includes/api/razorpay-webhook.php
// ============================================================
// SECURE RAZORPAY WEBHOOK HANDLER (BACKUP CONFIRMATION)
// Verifies webhook signature and processes payment if success page was missed
// ============================================================
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../functions.php';
// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}
// Get the raw POST payload
$payload = file_get_contents('php://input');
// Get Razorpay Signature Header
$razorpay_signature = isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) ? $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] : '';
if (empty($razorpay_signature)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing Signature Header']);
    exit;
}
// Cryptographic Verification of Webhook Signature
// Signature verification prevents unauthorized fake payloads
$expected_signature = hash_hmac('sha256', $payload, RAZORPAY_WEBHOOK_SECRET);
if (!hash_equals($expected_signature, $razorpay_signature)) {
    // If webhook secret isn't configured yet, log a warning and exit to avoid lockouts during initial testing,
    // or strictly fail if it's set. Let's do strict validation unless it's the placeholder.
    if (RAZORPAY_WEBHOOK_SECRET === 'your_webhook_secret') {
        error_log("[CampusStay WEBHOOK WARNING] Webhook signature received but RAZORPAY_WEBHOOK_SECRET is not configured in config.php. Please configure it.");
        http_response_code(200); // Return 200 so Razorpay doesn't keep retrying, but log warning
        echo json_encode(['warning' => 'Webhook secret not configured']);
        exit;
    } else {
        error_log("[CampusStay SECURITY] INVALID WEBHOOK SIGNATURE ATTEMPT from IP: " . $_SERVER['REMOTE_ADDR']);
        http_response_code(400);
        echo json_encode(['error' => 'Invalid Signature']);
        exit;
    }
}
// Parse the payload
$data = json_decode($payload, true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}
// We care about payment.captured event
if ($data['event'] === 'payment.captured') {
    $payment = $data['payload']['payment']['entity'];
    $razorpay_payment_id = $payment['id'];
    $razorpay_order_id   = $payment['order_id'];
    $amount_in_rupees    = $payment['amount'] / 100;
    if (empty($razorpay_order_id)) {
        http_response_code(200);
        echo json_encode(['status' => 'skipped', 'message' => 'No order ID associated with this payment']);
        exit;
    }
    try {
        $pdo->beginTransaction();
        // Check if there is a booking with this order ID
        $stmt = $pdo->prepare("
            SELECT b.*, r.room_type, p.title as pg_title, p.address as pg_address,
                   p.owner_id, u.email as user_email, u.full_name as user_name
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN pg_listings p ON b.pg_id = p.id
            JOIN users u ON b.user_id = u.id
            WHERE b.razorpay_order_id = ?
        ");
        $stmt->execute([$razorpay_order_id]);
        $booking = $stmt->fetch();
        if (!$booking) {
            $pdo->rollBack();
            http_response_code(200); // 200 because we don't want Razorpay to retry an order that doesn't belong to this DB
            echo json_encode(['status' => 'ignored', 'message' => 'Booking not found for order ID ' . $razorpay_order_id]);
            exit;
        }
        // If already paid, do nothing
        if ($booking['payment_status'] === 'paid') {
            $pdo->rollBack();
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Booking already processed']);
            exit;
        }
        // Update booking status
        $pdo->prepare("UPDATE bookings SET status = 'confirmed', payment_status = 'paid' WHERE id = ?")
            ->execute([$booking['id']]);
        // Insert payment record
        $pdo->prepare("INSERT INTO payments (booking_id, transaction_id, amount, payment_method, payment_status) VALUES (?, ?, ?, 'Razorpay (Webhook)', 'captured')")
            ->execute([$booking['id'], $razorpay_payment_id, $amount_in_rupees]);
        $pdo->prepare("INSERT INTO payments (booking_id, user_id, pg_id, owner_id, razorpay_order_id, razorpay_payment_id, transaction_id, amount, payment_method, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Razorpay (Webhook)', 'captured')")
            ->execute([
                $booking['id'],
                $booking['user_id'],
                $booking['pg_id'],
                $booking['owner_id'],
                $razorpay_order_id,
                $razorpay_payment_id,
                $razorpay_payment_id,
                $amount_in_rupees
            ]);
        // Reduce available beds
        $pdo->prepare("UPDATE rooms SET available_beds = available_beds - 1 WHERE id = ? AND available_beds > 0")
            ->execute([$booking['room_id']]);
        // Notify student
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Booking Confirmed (Auto)', 'Your payment was received and your booking is confirmed.', 'success')")
            ->execute([$booking['user_id']]);
        // Notify owner
        $owner_msg = "New booking confirmed! (Auto-verified) {$booking['user_name']} has paid ₹" . number_format($amount_in_rupees) .
                     " for a " . ucfirst($booking['room_type']) . " Sharing room at '{$booking['pg_title']}'. " .
                     "Payment ID: {$razorpay_payment_id}.";
        $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, 'Payment Received ✓', ?, 'info')")
            ->execute([$booking['owner_id'], $owner_msg]);
        $pdo->commit();
        // Send confirmation email
        $subject = "Booking Confirmed — " . $booking['pg_title'];
        $move_out = new DateTime($booking['move_in_date']);
        $move_out->modify('+' . $booking['duration_months'] . ' months -1 day');
        $emailContent = "
            <div style='text-align:center;margin-bottom:20px;'>
                <div style='display:inline-block;padding:10px 20px;background:#ecfdf5;color:#059669;border-radius:50px;font-weight:bold;font-size:14px;'>
                    ✓ Payment Verified via Webhook
                </div>
            </div>
            <h2 style='color:#1e293b;margin-top:0;'>Booking Confirmed!</h2>
            <p>Hi <strong>{$booking['user_name']}</strong>,</p>
            <p>Your payment has been <strong>verified and confirmed</strong>. Here is your stay summary:</p>
            <div style='background:#f8fafc;border:1px solid #e2e8f0;padding:25px;border-radius:16px;margin:25px 0;'>
                <div style='margin-bottom:15px;border-bottom:1px dashed #cbd5e1;padding-bottom:15px;'>
                    <span style='color:#64748b;font-size:12px;'>Booking ID</span><br>
                    <strong style='font-size:18px;color:#4f46e5;'>CS-{$booking['id']}</strong>
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
                        <td style='padding:15px 0 5px;text-align:right;font-weight:800;color:#4f46e5;font-size:18px;'>₹" . number_format($amount_in_rupees) . "</td>
                    </tr>
                </table>
            </div>
        ";
        sendEmail($booking['user_email'], $subject, $emailContent);
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Booking successfully confirmed via webhook']);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('[CampusStay Webhook Error] ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error']);
        exit;
    }
} else {
    http_response_code(200);
    echo json_encode(['status' => 'ignored', 'message' => 'Unhandled event type ' . $data['event']]);
    exit;
}
