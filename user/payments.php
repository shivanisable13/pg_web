<?php
$pageTitle = "Payment History";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch All Paid Bookings as "Payments"
$stmt = $pdo->prepare("SELECT b.*, p.title as pg_title, pay.transaction_id
                      FROM bookings b 
                      JOIN pg_listings p ON b.pg_id = p.id 
                      LEFT JOIN payments pay ON b.id = pay.booking_id
                      WHERE b.user_id = ? AND b.payment_status = 'paid'
                      ORDER BY b.booking_date DESC");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <a href="bookings.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
                    <a href="favorites.php" class="sidebar-link"><i class="fa-solid fa-heart"></i> Saved PGs</a>
                    <a href="payments.php" class="sidebar-link active"><i class="fa-solid fa-wallet"></i> Payments</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user"></i> My Profile</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">Transaction History</h2>

            <?php if (empty($payments)): ?>
                <div class="glass-card p-5 text-center">
                    <div class="icon-box bg-light text-muted mx-auto mb-3">
                        <i class="fa-solid fa-file-invoice-dollar fa-2x"></i>
                    </div>
                    <h4>No transactions yet</h4>
                    <p class="text-muted">Once you pay a booking token, your receipts will appear here.</p>
                </div>
            <?php else: ?>
                <div class="glass-card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Transaction ID</th>
                                    <th>PG Property</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-muted small">TXN-</span><span class="fw-bold"><?php echo $pay['transaction_id'] ? $pay['transaction_id'] : strtoupper(substr(md5($pay['id']), 0, 8)); ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo $pay['pg_title']; ?></div>
                                        <div class="text-muted small">Booking ID: CS-<?php echo $pay['id']; ?> • <span class="text-primary fw-semibold"><?php echo $pay['duration_months']; ?> Month(s)</span></div>
                                    </td>
                                    <td><?php echo date('d M, Y', strtotime($pay['booking_date'])); ?></td>
                                    <td><span class="fw-bold text-dark">₹<?php echo number_format($pay['total_amount']); ?></span></td>
                                    <td><span class="badge bg-success-light text-success rounded-pill px-3">Success</span></td>
                                    <td class="text-end pe-4">
                                        <a href="booking-details.php?id=<?php echo $pay['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">View Receipt</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
