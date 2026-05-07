<?php
require_once '../config/db.php';
require_once '../config/config.php';
require_once '../functions.php';

// Auth Check
if (!isLoggedIn()) {
    setFlash('warning', 'Please login to book a stay.');
    redirect('/auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $pg_id = (int)$_POST['pg_id'];
    $room_id = (int)$_POST['room_id'];
    $move_in_date = $_POST['move_in_date'];
    $duration = (int)$_POST['duration'];

    // 1. Validation
    if (empty($room_id) || empty($move_in_date) || empty($duration)) {
        setFlash('danger', 'Please fill all booking details.');
        redirect("/pg-details.php?id=$pg_id");
    }

    // 2. Availability Check
    $stmt = $pdo->prepare("SELECT available_beds, rent_per_month, security_deposit FROM rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room || $room['available_beds'] <= 0) {
        setFlash('danger', 'Sorry, this room type is no longer available.');
        redirect("/pg-details.php?id=$pg_id");
    }

    // 3. Calculate Totals
    // Dynamic calculation: Rent * Duration
    $booking_amount = $room['rent_per_month'] * $duration; 
    $total_contract_value = ($room['rent_per_month'] * $duration) + $room['security_deposit'];
    try {
        $pdo->beginTransaction();

        // 4. Check for existing pending booking for this PG and User
        $stmt = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND pg_id = ? AND status = 'pending' AND payment_status = 'unpaid'");
        $stmt->execute([$user_id, $pg_id]);
        $existing_booking_id = $stmt->fetchColumn();

        if ($existing_booking_id) {
            // Update existing instead of creating new
            $stmt = $pdo->prepare("UPDATE bookings SET room_id = ?, move_in_date = ?, duration_months = ?, total_amount = ? WHERE id = ?");
            $stmt->execute([$room_id, $move_in_date, $duration, $booking_amount, $existing_booking_id]);
            $booking_id = $existing_booking_id;
        } else {
            // Create new
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, pg_id, room_id, move_in_date, duration_months, total_amount, status, payment_status) 
                                    VALUES (?, ?, ?, ?, ?, ?, 'pending', 'unpaid')");
            $stmt->execute([$user_id, $pg_id, $room_id, $move_in_date, $duration, $booking_amount]);
            $booking_id = $pdo->lastInsertId();
        }

        $pdo->commit();
        
        // 5. Redirect to Checkout/Payment Page
        redirect("/checkout.php?booking_id=$booking_id");

    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Booking failed: ' . $e->getMessage());
        redirect("/pg-details.php?id=$pg_id");
    }
} else {
    redirect('/index.php');
}
?>
