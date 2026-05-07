<?php
$pageTitle = "My Bookings";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch All Bookings (One latest entry per PG to avoid duplicates)
$stmt = $pdo->prepare("SELECT b.*, p.title as pg_title, r.room_type, r.rent_per_month, pi.image_url 
                      FROM bookings b 
                      JOIN pg_listings p ON b.pg_id = p.id 
                      JOIN rooms r ON b.room_id = r.id 
                      LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
                      WHERE b.user_id = ? 
                      AND b.id IN (SELECT MAX(id) FROM bookings WHERE user_id = ? GROUP BY pg_id)
                      ORDER BY b.booking_date DESC");
$stmt->execute([$user_id, $user_id]);
$bookings = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <a href="bookings.php" class="sidebar-link active"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
                    <a href="favorites.php" class="sidebar-link"><i class="fa-solid fa-heart"></i> Saved PGs</a>
                    <a href="payments.php" class="sidebar-link"><i class="fa-solid fa-wallet"></i> Payments</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user"></i> My Profile</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Booking History</h2>
                <span class="text-muted small"><?php echo count($bookings); ?> total bookings</span>
            </div>

            <?php if (empty($bookings)): ?>
                <div class="glass-card p-5 text-center">
                    <div class="icon-box bg-light text-muted mx-auto mb-3">
                        <i class="fa-solid fa-calendar-xmark fa-2x"></i>
                    </div>
                    <h4>No bookings found</h4>
                    <p class="text-muted">You haven't made any bookings yet.</p>
                    <a href="../search.php" class="btn btn-primary rounded-pill mt-3">Browse PGs</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($bookings as $b): ?>
                    <div class="col-12">
                        <div class="glass-card p-3">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="<?php echo APP_URL . '/' . ($b['image_url'] ?? 'assets/images/placeholder.png'); ?>" class="rounded-3 w-100" style="height: 100px; object-fit: cover;" alt="PG">
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h5 class="fw-bold mb-0"><?php echo $b['pg_title']; ?></h5>
                                        <span class="badge bg-light text-dark border small">ID: CS-<?php echo $b['id']; ?></span>
                                    </div>
                                    <p class="text-muted small mb-0"><?php echo ucfirst($b['room_type']); ?> Sharing Room</p>
                                    <span class="small text-muted">Booked on <?php echo date('d M, Y', strtotime($b['booking_date'])); ?></span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <?php 
                                    $statusClass = 'bg-warning-light text-warning';
                                    if ($b['status'] === 'confirmed') $statusClass = 'bg-success-light text-success';
                                    if ($b['status'] === 'cancelled') $statusClass = 'bg-danger-light text-danger';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?> rounded-pill px-3 py-2">
                                        <?php echo ucfirst($b['status']); ?>
                                    </span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="fw-bold d-block">₹<?php echo number_format($b['total_amount']); ?></span>
                                    <span class="small <?php echo $b['payment_status'] === 'paid' ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo ucfirst($b['payment_status']); ?>
                                    </span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <a href="booking-details.php?id=<?php echo $b['id']; ?>" class="btn btn-light rounded-pill btn-sm px-3">Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
