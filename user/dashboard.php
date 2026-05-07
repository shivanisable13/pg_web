<?php
$pageTitle = "My Dashboard";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch Active Stays
$stmt = $pdo->prepare("SELECT b.*, p.title, p.area, p.city, pi.image_url 
                      FROM bookings b 
                      JOIN pg_listings p ON b.pg_id = p.id 
                      LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
                      WHERE b.user_id = ? AND b.status = 'confirmed'
                      ORDER BY b.booking_date DESC");
$stmt->execute([$user_id]);
$active_stays = $stmt->fetchAll();

// Fetch Recent Notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="text-center mb-4">
                    <img src="<?php echo APP_URL . '/' . $_SESSION['user_image']; ?>" class="rounded-circle border mb-3" width="80" height="80" alt="Profile">
                    <h5 class="fw-bold mb-0"><?php echo $_SESSION['user_name']; ?></h5>
                    <p class="text-muted small">Student</p>
                </div>
                <hr>
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <a href="bookings.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
                    <a href="favorites.php" class="sidebar-link"><i class="fa-solid fa-heart"></i> Saved PGs</a>
                    <a href="payments.php" class="sidebar-link"><i class="fa-solid fa-wallet"></i> Payments</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user"></i> My Profile</a>
                    <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">Welcome back, <?php echo explode(' ', $_SESSION['user_name'])[0]; ?>!</h2>

            <div class="row g-4">
                <!-- Active Stays -->
                <div class="col-lg-8">
                    <h5 class="fw-bold mb-3">Your Stays</h5>
                    <?php if(empty($active_stays)): ?>
                        <div class="glass-card p-5 text-center mb-4">
                            <i class="fa-solid fa-bed fa-3x text-light mb-3"></i>
                            <h6>No active stays found</h6>
                            <p class="text-muted small">You haven't booked any PGs yet.</p>
                            <a href="../search.php" class="btn btn-primary btn-sm rounded-pill px-4">Start Searching</a>
                        </div>
                    <?php else: ?>
                        <?php foreach($active_stays as $stay): ?>
                        <div class="glass-card p-3 mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="<?php echo APP_URL . '/' . ($stay['image_url'] ?? 'assets/images/placeholder.png'); ?>" class="img-fluid rounded-3" alt="PG">
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold mb-1"><?php echo $stay['title']; ?></h6>
                                    <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot"></i> <?php echo $stay['area']; ?>, <?php echo $stay['city']; ?></p>
                                    <span class="badge bg-success-light text-success rounded-pill">Move-in: <?php echo date('d M', strtotime($stay['move_in_date'])); ?></span>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="booking-details.php?id=<?php echo $stay['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100">View Details</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Notifications -->
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">Recent Alerts</h5>
                    <div class="glass-card p-4">
                        <?php if(empty($notifications)): ?>
                            <p class="text-muted text-center small my-4">No notifications yet.</p>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach($notifications as $note): ?>
                                <div class="border-bottom pb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold small"><?php echo $note['title']; ?></span>
                                        <span class="text-muted" style="font-size: 10px;"><?php echo date('H:i', strtotime($note['created_at'])); ?></span>
                                    </div>
                                    <p class="small text-muted mb-0"><?php echo $note['message']; ?></p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <a href="notifications.php" class="btn btn-light w-100 btn-sm mt-3 rounded-pill">View All</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
