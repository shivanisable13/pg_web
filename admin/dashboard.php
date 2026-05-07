<?php
$pageTitle = "Admin Control Center";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

// Auth Check
if (!isLoggedIn() || !hasRole('admin')) {
    setFlash('danger', 'Unauthorized access.');
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

// Platform Stats
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_owners = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();
$pending_approvals = $pdo->query("SELECT COUNT(*) FROM pg_listings WHERE status = 'pending'")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Admin Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="text-center mb-4">
                    <div class="icon-box bg-dark text-white mx-auto mb-3">
                        <i class="fa-solid fa-user-shield fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-0">System Admin</h5>
                    <p class="text-muted small">CampusStay Platform</p>
                </div>
                <hr>
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                    <a href="approvals.php" class="sidebar-link d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-clipboard-check"></i> PG Approvals</span>
                        <?php if($pending_approvals > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?php echo $pending_approvals; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="users.php" class="sidebar-link"><i class="fa-solid fa-users"></i> Management</a>
                    <a href="listings.php" class="sidebar-link"><i class="fa-solid fa-list"></i> All Listings</a>
                    <a href="settings.php" class="sidebar-link"><i class="fa-solid fa-gears"></i> System Settings</a>
                    <a href="../auth/logout.php" class="sidebar-link text-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">Platform Overview</h2>

            <!-- Stats Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="glass-card p-4 text-center border-bottom border-primary border-4" id="ownerStatsCard" style="cursor: pointer;">
                        <div class="icon-box bg-primary-light text-primary mx-auto mb-3">
                            <i class="fa-solid fa-users fa-2x"></i>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $total_owners; ?></h3>
                        <p class="text-muted mb-0">Registered PG Owners</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="glass-card p-4 text-center border-bottom border-warning border-4" id="pendingStatsCard" style="cursor: pointer;">
                        <div class="icon-box bg-warning-light text-warning mx-auto mb-3">
                            <i class="fa-solid fa-clipboard-check fa-2x"></i>
                        </div>
                        <h3 class="fw-bold mb-1"><?php echo $pending_approvals; ?></h3>
                        <p class="text-muted mb-0">Pending PG Approvals</p>
                    </div>
                </div>
            </div>

            <!-- Owner Management Section -->
            <div id="ownerDirectorySection" style="display: none;">
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="fw-bold mb-0">Owner Directory</h4>
                        <a href="users.php?role=owner" class="btn btn-sm btn-outline-primary rounded-pill px-3">View All Owners</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>PGs Listed</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stmt = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM pg_listings WHERE owner_id = u.id) as pg_count 
                                                    FROM users u WHERE role = 'owner' ORDER BY created_at DESC LIMIT 5");
                                $owners = $stmt->fetchAll();
                                foreach($owners as $owner): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo getImageUrl($owner['profile_image']); ?>" class="rounded-circle me-2" width="35" height="35" alt="Owner">
                                            <span class="fw-bold"><?php echo $owner['full_name']; ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo $owner['email']; ?></td>
                                    <td><?php echo $owner['phone']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $owner['pg_count']; ?> PGs</span></td>
                                    <td>
                                        <span class="badge <?php echo $owner['is_verified'] ? 'bg-success-light text-success' : 'bg-warning-light text-warning'; ?> rounded-pill">
                                            <?php echo $owner['is_verified'] ? 'Verified' : 'Pending'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pending Approvals Highlight -->
            <div id="pendingApprovalsSection" style="display: none;">
                <div class="alert alert-warning border-0 shadow-sm d-flex justify-content-between align-items-center p-4 mb-4 rounded-4">
                    <div>
                        <h5 class="alert-heading fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i> Pending PG Approvals</h5>
                        <p class="mb-0">
                            <?php if($pending_approvals > 0): ?>
                                There are <?php echo $pending_approvals; ?> new properties waiting for your verification.
                            <?php else: ?>
                                No pending property approvals at the moment.
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if($pending_approvals > 0): ?>
                        <a href="approvals.php" class="btn btn-warning fw-bold rounded-pill px-4">Review Now</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent System Activity (Bookings) -->
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Recent System Activity</h4>
                    <span class="badge bg-primary rounded-pill">Latest Alerts</span>
                </div>
                <div class="list-group list-group-flush">
                    <?php 
                    $recent_bookings = $pdo->query("SELECT b.*, u.full_name, p.title as pg_name 
                                                   FROM bookings b 
                                                   JOIN users u ON b.user_id = u.id 
                                                   JOIN pg_listings p ON b.pg_id = p.id 
                                                   WHERE b.payment_status = 'paid'
                                                   ORDER BY b.booking_date DESC LIMIT 5")->fetchAll();
                    
                    if(empty($recent_bookings)): ?>
                        <div class="text-center py-4 text-muted">No recent booking activity.</div>
                    <?php else: ?>
                        <?php foreach($recent_bookings as $booking): ?>
                        <div class="list-group-item bg-transparent border-0 px-0 py-3 d-flex align-items-center gap-3">
                            <div class="icon-box bg-success-light text-success" style="width: 45px; height: 45px; flex-shrink: 0;">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-0 fw-bold"><?php echo $booking['full_name']; ?></h6>
                                    <span class="text-muted small"><?php echo date('h:i A', strtotime($booking['booking_date'])); ?></span>
                                </div>
                                <p class="text-muted small mb-0">Booked <span class="text-primary fw-bold"><?php echo $booking['pg_name']; ?></span></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Owner Directory
document.getElementById('ownerStatsCard').addEventListener('click', function() {
    const ownerSection = document.getElementById('ownerDirectorySection');
    const pendingSection = document.getElementById('pendingApprovalsSection');
    
    // Hide pending section if open
    pendingSection.style.display = 'none';
    
    if (ownerSection.style.display === 'none') {
        ownerSection.style.display = 'block';
        ownerSection.scrollIntoView({ behavior: 'smooth' });
    } else {
        ownerSection.style.display = 'none';
    }
});

// Toggle Pending Approvals
document.getElementById('pendingStatsCard').addEventListener('click', function() {
    const pendingSection = document.getElementById('pendingApprovalsSection');
    const ownerSection = document.getElementById('ownerDirectorySection');
    
    // Hide owner section if open
    ownerSection.style.display = 'none';
    
    if (pendingSection.style.display === 'none') {
        pendingSection.style.display = 'block';
        pendingSection.scrollIntoView({ behavior: 'smooth' });
    } else {
        pendingSection.style.display = 'none';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>