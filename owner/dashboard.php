<?php
$pageTitle = "Owner Dashboard";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

// Auth Check
if (!isLoggedIn() || !hasRole('owner')) {
    setFlash('danger', 'Access denied.');
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$owner_id = $_SESSION['user_id'];

// Fetch Stats
$total_pgs = $pdo->prepare("SELECT COUNT(*) FROM pg_listings WHERE owner_id = ?");
$total_pgs->execute([$owner_id]);
$total_pgs = $total_pgs->fetchColumn();

$active_bookings = $pdo->prepare("SELECT COUNT(*) FROM bookings b JOIN pg_listings p ON b.pg_id = p.id WHERE p.owner_id = ? AND b.status = 'confirmed'");
$active_bookings->execute([$owner_id]);
$active_bookings = $active_bookings->fetchColumn();

$total_revenue = $pdo->prepare("SELECT SUM(amount) FROM payments pay JOIN bookings b ON pay.booking_id = b.id JOIN pg_listings p ON b.pg_id = p.id WHERE p.owner_id = ? AND pay.payment_status = 'captured'");
$total_revenue->execute([$owner_id]);
$total_revenue = $total_revenue->fetchColumn() ?? 0;
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
                    <a href="dashboard.php" class="sidebar-link active"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                    <a href="manage-pgs.php" class="sidebar-link"><i class="fa-solid fa-building"></i> My PGs</a>
                    <a href="add-pg.php" class="sidebar-link text-primary fw-bold"><i class="fa-solid fa-plus-circle"></i> Add New PG</a>
                    <a href="bookings.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
                    <a href="revenue.php" class="sidebar-link"><i class="fa-solid fa-indian-rupee-sign"></i> Revenue</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> Settings</a>
                    <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Dashboard Overview</h2>
                <a href="add-pg.php" class="btn btn-primary rounded-pill"><i class="fa-solid fa-plus me-2"></i> List New Property</a>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <a href="manage-pgs.php" class="text-decoration-none d-block h-100">
                        <div class="glass-card p-4 text-center border-bottom border-primary border-4 hover-translate">
                            <div class="icon-box bg-primary-light text-primary mx-auto mb-3">
                                <i class="fa-solid fa-building fa-2x"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo $total_pgs; ?></h3>
                            <p class="text-muted mb-0">Total Properties</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="bookings.php" class="text-decoration-none d-block h-100">
                        <div class="glass-card p-4 text-center border-bottom border-success border-4 hover-translate">
                            <div class="icon-box bg-success-light text-success mx-auto mb-3">
                                <i class="fa-solid fa-user-check fa-2x"></i>
                            </div>
                            <h3 class="fw-bold mb-1"><?php echo $active_bookings; ?></h3>
                            <p class="text-muted mb-0">Active Tenants</p>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="revenue.php" class="text-decoration-none d-block h-100">
                        <div class="glass-card p-4 text-center border-bottom border-warning border-4 hover-translate">
                            <div class="icon-box bg-warning-light text-warning mx-auto mb-3">
                                <i class="fa-solid fa-indian-rupee-sign fa-2x"></i>
                            </div>
                            <h3 class="fw-bold mb-1">₹<?php echo number_format($total_revenue); ?></h3>
                            <p class="text-muted mb-0">Total Earnings</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Quick Links & System Status -->
            <div class="row g-4">
                <div class="col-lg-12">
                    <div class="glass-card p-4">
                        <h5 class="fw-bold mb-4">Quick Management Links</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="generate-report.php" class="btn btn-light text-start border rounded-4 p-4 h-100 d-block text-decoration-none hover-translate">
                                    <div class="icon-box bg-primary-light text-primary mb-3">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Monthly Reports</h6>
                                    <p class="text-muted small mb-0">Generate earnings and tax summaries.</p>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="send-notification.php" class="btn btn-light text-start border rounded-4 p-4 h-100 d-block text-decoration-none hover-translate">
                                    <div class="icon-box bg-warning-light text-warning mb-3">
                                        <i class="fa-solid fa-bell"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Send Alerts</h6>
                                    <p class="text-muted small mb-0">Broadcast messages to all your tenants.</p>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="support-chat.php" class="btn btn-light text-start border rounded-4 p-4 h-100 d-block text-decoration-none hover-translate">
                                    <div class="icon-box bg-success-light text-success mb-3">
                                        <i class="fa-solid fa-headset"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark">Support Desk</h6>
                                    <p class="text-muted small mb-0">Chat with administrators or view tickets.</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>