<?php
$pageTitle = "Secure Checkout";
require_once 'includes/config/config.php';
require_once 'includes/functions.php';
if (!isLoggedIn()) redirect('/auth/login.php');
require_once 'includes/header.php';
require_once 'includes/config/db.php';
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
// Fetch Booking Details
$stmt = $pdo->prepare("SELECT b.*, p.title as pg_title, r.room_type, r.rent_per_month 
                      FROM bookings b 
                      JOIN pg_listings p ON b.pg_id = p.id 
                      JOIN rooms r ON b.room_id = r.id 
                      WHERE b.id = ? AND b.user_id = ?");
$stmt = $pdo->prepare("SELECT b.*, p.title as pg_title, r.room_type, r.rent_per_month, 
                              o.full_name as owner_name, o.payment_upi_id, o.payment_bank_name, 
                              o.payment_account_number, o.payment_ifsc_code 
                       FROM bookings b 
                       JOIN pg_listings p ON b.pg_id = p.id 
                       JOIN rooms r ON b.room_id = r.id 
                       JOIN users o ON p.owner_id = o.id
                       WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch();
if (!$booking) {
    setFlash('danger', 'Booking not found.');
    redirect('/index.php');
}
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="glass-card p-4 p-md-5">
                <div class="text-center mb-5">
                    <div class="icon-box bg-primary-light text-primary mx-auto mb-3">
                        <i class="fa-solid fa-lock fa-2x"></i>
                    </div>
                    <h2 class="fw-bold">Confirm & Pay</h2>
                    <p class="text-muted">Review your booking summary and proceed to payment</p>
                </div>
                <!-- Summary -->
                <div class="bg-light rounded-4 p-4 mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Property</span>
                        <span class="fw-bold"><?php echo $booking['pg_title']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Room Type</span>
                        <span class="fw-bold"><?php echo ucfirst($booking['room_type']); ?> Sharing</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Move-in Date</span>
                        <span class="fw-bold"><?php echo date('d M, Y', strtotime($booking['move_in_date'])); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Duration</span>
                        <span class="fw-bold"><?php echo $booking['duration_months']; ?> Months</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted">Move-out Date</span>
                        <span class="fw-bold text-dark">
                            <?php 
                                $date = new DateTime($booking['move_in_date']);
                                $date->modify('+' . $booking['duration_months'] . ' months');
                                $date->modify('-1 day');
                                echo $date->format('d M, Y');
                            ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="h5 fw-bold mb-0">Booking Amount</span>
                        <span class="h5 fw-bold mb-0 text-primary">₹<?php echo number_format($booking['total_amount'], 2); ?></span>
                    </div>
                    <p class="small text-muted mt-2 mb-0">* This amount is calculated based on your selected duration of <?php echo $booking['duration_months']; ?> month(s).</p>
                </div>
                <!-- Payment Methods -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Payment Options</h6>
                </div>
                
                <div class="d-grid gap-3">
                    <button id="rzp-button1" class="btn btn-primary py-3 rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-credit-card me-2"></i> Pay with Razorpay
                    </button>
                    <p class="text-center small text-muted mt-2">
                        <i class="fa-solid fa-lock text-success me-1"></i> Secure 256-bit SSL Encryption
                    </p>
                <!-- Payment Options Navigation -->
                <ul class="nav nav-pills nav-fill gap-2 mb-4 bg-light p-2 rounded-pill" id="paymentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-bold" id="razorpay-tab" data-bs-toggle="tab" data-bs-target="#razorpay-pane" type="button" role="tab"><i class="fa-solid fa-credit-card me-1"></i> Razorpay Online</button>
                    </li>
                    <?php if (!empty($booking['payment_upi_id']) || !empty($booking['payment_account_number'])): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold" id="direct-tab" data-bs-toggle="tab" data-bs-target="#direct-pane" type="button" role="tab"><i class="fa-solid fa-money-bill-transfer me-1"></i> Direct Transfer</button>
                    </li>
                    <?php endif; ?>
                </ul>
                <div class="tab-content" id="paymentTabsContent">
                    <!-- Razorpay Pane -->
                    <div class="tab-pane fade show active" id="razorpay-pane" role="tabpanel">
                        <div class="d-grid gap-3 mt-3">
                            <button id="rzp-button1" class="btn btn-primary py-3 rounded-pill fw-bold shadow-sm">
                                <i class="fa-solid fa-credit-card me-2"></i> Pay with Razorpay
                            </button>
                            <p class="text-center small text-muted mt-2">
                                <i class="fa-solid fa-lock text-success me-1"></i> Secure 256-bit SSL Encryption
                            </p>
                        </div>
                    </div>
                    <!-- Direct Transfer Pane -->
                    <?php if (!empty($booking['payment_upi_id']) || !empty($booking['payment_account_number'])): ?>
                    <div class="tab-pane fade" id="direct-pane" role="tabpanel">
                        <div class="bg-light rounded-4 p-4 mb-4 mt-3">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-building-columns text-primary me-2"></i>Owner's Payment Details</h6>
                            
                            <?php if (!empty($booking['payment_upi_id'])): ?>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted">UPI ID</span>
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($booking['payment_upi_id']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($booking['payment_account_number'])): ?>
                            <div class="mb-0">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Bank Name</span>
                                    <span class="fw-semibold"><?php echo htmlspecialchars($booking['payment_bank_name']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Account Number</span>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($booking['payment_account_number']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">IFSC Code</span>
                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($booking['payment_ifsc_code']); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <form action="includes/api/process-direct-payment.php" method="POST" class="needs-validation" novalidate>
                            <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Transaction Reference / UPI Ref No.</label>
                                <input type="text" name="transaction_id" class="form-control" placeholder="Enter the 12-digit transaction/UTR ID" required>
                                <div class="invalid-feedback">Please enter your transaction reference number.</div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm">
                                <i class="fa-solid fa-check me-2"></i> Submit Direct Payment Info
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Razorpay Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo RAZORPAY_KEY_ID; ?>",
    "amount": "<?php echo $booking['total_amount'] * 100; ?>", // Amount in paise
    "currency": "INR",
    "name": "CampusStay",
    "description": "<?php echo $booking['duration_months']; ?> Month(s) Stay - <?php echo $booking['pg_title']; ?>",
    "handler": function (response){
        // Redirect to success page with response IDs
        window.location.href = "includes/api/payment-success.php?booking_id=<?php echo $booking_id; ?>&razorpay_payment_id=" + response.razorpay_payment_id;
    },
    "prefill": {
        "name": "<?php echo $_SESSION['user_name']; ?>",
        "email": "test@example.com",
        "contact": "9876543210"
    },
    "theme": {
        "color": "#4f46e5"
    }
};
var rzp1 = new Razorpay(options);
document.getElementById('rzp-button1').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script>
<?php require_once 'includes/footer.php'; ?>
