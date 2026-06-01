<?php
// owner/notifications.php
$pageTitle = "Owner Notifications";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';
if (!isLoggedIn() || !hasRole('owner')) {
    redirect('/auth/login.php');
}
require_once '../includes/header.php';
require_once '../includes/config/db.php';
$user_id = $_SESSION['user_id'];
// Fetch All Notifications for Owner
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
                <div class="text-center mb-4">
                    <img src="<?php echo getImageUrl($_SESSION['user_image']); ?>" class="rounded-circle border mb-3" width="80" height="80" alt="Profile">
                    <h5 class="fw-bold mb-0"><?php echo $_SESSION['user_name']; ?></h5>
                    <p class="text-muted small">PG Owner</p>
                </div>
                <hr>
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                    <a href="manage-pgs.php" class="sidebar-link"><i class="fa-solid fa-building"></i> My PGs</a>
                    <a href="add-pg.php" class="sidebar-link text-primary fw-bold"><i class="fa-solid fa-plus-circle"></i> Add New PG</a>
                    <a href="bookings.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
                    <a href="revenue.php" class="sidebar-link"><i class="fa-solid fa-indian-rupee-sign"></i> Revenue</a>
                    <a href="notifications.php" class="sidebar-link active"><i class="fa-solid fa-bell"></i> Notifications</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> Settings</a>
                    <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </nav>
            </div>
        </div>
        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Notifications</h2>
                <span class="badge bg-primary text-white rounded-pill px-3 py-2"><?php echo count($notifications); ?> Alerts</span>
            </div>
            <?php if (empty($notifications)): ?>
                <div class="glass-card p-5 text-center">
                    <div class="icon-box bg-light text-muted mx-auto mb-3">
                        <i class="fa-solid fa-bell-slash fa-2x"></i>
                    </div>
                    <h4>All caught up!</h4>
                    <p class="text-muted">No booking alerts or system notifications at the moment.</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($notifications as $note): ?>
                    <div class="col-12">
                        <div class="glass-card p-4 <?php echo $note['is_read'] ? 'opacity-75' : 'border-start border-primary border-4'; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="fw-bold mb-0 text-dark"><?php echo $note['title']; ?></h5>
                                <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i> <?php echo date('d M, Y - h:i A', strtotime($note['created_at'])); ?></span>
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
