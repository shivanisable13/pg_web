<?php
$pageTitle = "My Notifications";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch All Notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Mark all as read
$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$user_id]);
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
                    <a href="payments.php" class="sidebar-link"><i class="fa-solid fa-wallet"></i> Payments</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user"></i> My Profile</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Notifications</h2>
                <span class="text-muted small"><?php echo count($notifications); ?> total alerts</span>
            </div>

            <?php if (empty($notifications)): ?>
                <div class="glass-card p-5 text-center">
                    <div class="icon-box bg-light text-muted mx-auto mb-3">
                        <i class="fa-solid fa-bell-slash fa-2x"></i>
                    </div>
                    <h4>No notifications yet</h4>
                    <p class="text-muted">You're all caught up! Important alerts will appear here.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($notifications as $note): ?>
                    <div class="col-12">
                        <div class="glass-card p-4 <?php echo $note['is_read'] ? 'opacity-75' : 'border-start border-primary border-4'; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-0"><?php echo $note['title']; ?></h5>
                                <span class="text-muted small"><?php echo date('d M, Y - h:i A', strtotime($note['created_at'])); ?></span>
                            </div>
                            <p class="text-muted mb-0"><?php echo $note['message']; ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
