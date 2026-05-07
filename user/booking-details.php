<?php
$pageTitle = "Booking Details";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Detailed Booking Info
$stmt = $pdo->prepare("SELECT b.*, p.title as pg_title, p.address, p.city, p.area, r.room_type, r.rent_per_month, u.full_name as owner_name, u.phone as owner_phone
                      FROM bookings b 
                      JOIN pg_listings p ON b.pg_id = p.id 
                      JOIN rooms r ON b.room_id = r.id 
                      JOIN users u ON p.owner_id = u.id
                      WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('danger', 'Booking details not found.');
    redirect('bookings.php');
}

if (!$booking) {
    setFlash('danger', 'Booking details not found.');
    redirect('bookings.php');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="d-flex align-items-center gap-3">
                    <a href="bookings.php" class="btn btn-light rounded-circle shadow-sm d-print-none"><i class="fa-solid fa-arrow-left"></i></a>
                    <h2 class="fw-bold mb-0">Booking Details</h2>
                </div>
            </div>

            <div class="glass-card overflow-hidden">
                <!-- Header Status -->
                <div class="p-4 <?php echo $booking['payment_status'] === 'paid' ? 'bg-success text-white' : 'bg-warning text-dark'; ?> d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-75">Booking ID</p>
                        <h4 class="fw-bold mb-0">CS-<?php echo $booking['id']; ?></h4>
                    </div>
                    <div class="text-end">
                        <p class="mb-1 opacity-75">Payment Status</p>
                        <h5 class="fw-bold mb-0 text-uppercase"><?php echo $booking['payment_status']; ?></h5>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <div class="row g-4">
                        <!-- Property Info -->
                        <div class="col-md-6">
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Property Details</h6>
                            <h4 class="fw-bold mb-1"><?php echo $booking['pg_title']; ?></h4>
                            <p class="text-muted"><i class="fa-solid fa-location-dot me-2"></i><?php echo $booking['area']; ?>, <?php echo $booking['city']; ?></p>
                            
                            <hr class="my-4">
                            
                            <h6 class="text-muted text-uppercase small fw-bold mb-3">Stay Details</h6>
                            <div class="glass-card bg-light border-0 p-3 mb-3">
                                <div class="row g-0">
                                    <div class="col-5">
                                        <p class="text-muted small mb-1">Check-in</p>
                                        <h6 class="fw-bold mb-0"><?php echo date('d M, Y', strtotime($booking['move_in_date'])); ?></h6>
                                    </div>
                                    <div class="col-2 text-center d-flex align-items-center justify-content-center">
                                        <i class="fa-solid fa-arrow-right text-primary opacity-50"></i>
                                    </div>
                                    <div class="col-5 text-end">
                                        <p class="text-muted small mb-1">Check-out</p>
                                        <h6 class="fw-bold mb-0">
                                            <?php 
                                                $date = new DateTime($booking['move_in_date']);
                                                $date->modify('+' . $booking['duration_months'] . ' months');
                                                $date->modify('-1 day');
                                                echo $date->format('d M, Y');
                                            ?>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center p-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-bed text-muted"></i>
                                    <span class="text-muted small">Room Type:</span>
                                    <span class="fw-bold small ms-1"><?php echo ucfirst($booking['room_type']); ?> Sharing</span>
                                </div>
                                <div class="badge bg-primary-light text-primary rounded-pill px-3">
                                    <?php echo $booking['duration_months']; ?> Months Stay
                                </div>
                            </div>
                        </div>

                        <!-- Owner & Payment -->
                        <div class="col-md-6">
                            <div class="bg-light rounded-4 p-4">
                                <h6 class="text-muted text-uppercase small fw-bold mb-3">Billing Summary</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Booking Token</span>
                                    <span class="fw-bold">₹<?php echo number_format($booking['total_amount']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                    <span>Security Deposit</span>
                                    <span class="text-muted">At Move-in</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="h5 fw-bold mb-0">Total Paid</span>
                                    <span class="h5 fw-bold mb-0 text-primary">₹<?php echo $booking['payment_status'] === 'paid' ? number_format($booking['total_amount']) : '0.00'; ?></span>
                                </div>
                            </div>

                            <div class="mt-4 p-3 border rounded-4 d-flex align-items-center gap-3">
                                <div class="icon-box bg-primary-light text-primary" style="width: 50px; height: 50px;">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0"><?php echo $booking['owner_name']; ?></h6>
                                    <p class="text-muted small mb-0">Property Owner</p>
                                </div>
                                <a href="tel:<?php echo $booking['owner_phone']; ?>" class="btn btn-sm btn-outline-primary rounded-pill ms-auto">
                                    <i class="fa-solid fa-phone"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php if($booking['payment_status'] !== 'paid'): ?>
                        <div class="mt-5 text-center">
                            <a href="../checkout.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-primary px-5 py-3 rounded-pill fw-bold">
                                Complete Payment Now
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <p class="text-center mt-4 text-muted small">
                For any issues with this booking, please contact support or the property owner directly.
            </p>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide non-receipt elements */
    .d-print-none, .sidebar-link, .btn, .navbar, footer, .alert {
        display: none !important;
    }
    
    /* Reset background and padding */
    body {
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .container {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 20px !important;
    }
    
    /* Ensure colors are printed */
    .glass-card {
        border: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        background: white !important;
    }
    
    .bg-success {
        background-color: #10b981 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .text-white {
        color: white !important;
    }
    
    .bg-light {
        background-color: #f8fafc !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Force specific text colors */
    .text-primary {
        color: #4f46e5 !important;
    }
    
    .text-muted {
        color: #64748b !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
