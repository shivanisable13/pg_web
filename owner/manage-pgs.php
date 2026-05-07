<?php
$pageTitle = "Manage My PGs";
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

// Fetch Owner's PGs
$stmt = $pdo->prepare("SELECT p.*, pi.image_url, 
                      (SELECT COUNT(*) FROM bookings b WHERE b.pg_id = p.id AND b.status = 'confirmed') as active_tenants,
                      (SELECT SUM(available_beds) FROM rooms r WHERE r.pg_id = p.id) as total_available_beds
                      FROM pg_listings p 
                      LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
                      WHERE p.owner_id = ? 
                      ORDER BY p.created_at DESC");
$stmt->execute([$owner_id]);
$pgs = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                    <a href="manage-pgs.php" class="sidebar-link active"><i class="fa-solid fa-building"></i> My PGs</a>
                    <a href="add-pg.php" class="sidebar-link text-primary fw-bold"><i class="fa-solid fa-plus-circle"></i> Add New PG</a>
                    <a href="bookings.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> Settings</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">My Properties</h2>
                <a href="add-pg.php" class="btn btn-primary rounded-pill"><i class="fa-solid fa-plus me-2"></i> List New Property</a>
            </div>

            <?php if (empty($pgs)): ?>
                <div class="glass-card p-5 text-center">
                    <div class="icon-box bg-light text-muted mx-auto mb-3">
                        <i class="fa-solid fa-building-circle-exclamation fa-2x"></i>
                    </div>
                    <h4>No properties listed yet</h4>
                    <p class="text-muted">Start by adding your first PG to the platform.</p>
                    <a href="add-pg.php" class="btn btn-primary rounded-pill mt-3">Add Your First PG</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($pgs as $pg): ?>
                    <div class="col-12">
                        <div class="glass-card p-3">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="<?php echo getImageUrl($pg['image_url']); ?>" class="rounded-3 w-100" style="height: 100px; object-fit: cover;" alt="PG">
                                </div>
                                <div class="col-md-4">
                                    <h5 class="fw-bold mb-1"><?php echo $pg['title']; ?></h5>
                                    <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot"></i> <?php echo $pg['area'] . ', ' . $pg['city']; ?></p>
                                    <span class="small text-muted">Listed on <?php echo date('d M, Y', strtotime($pg['created_at'])); ?></span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <?php 
                                    $statusClass = 'bg-warning-light text-warning';
                                    if ($pg['status'] === 'approved') $statusClass = 'bg-success-light text-success';
                                    if ($pg['status'] === 'rejected') $statusClass = 'bg-danger-light text-danger';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?> rounded-pill px-3 py-2">
                                        <?php echo ucfirst($pg['status']); ?>
                                    </span>
                                </div>
                                <div class="col-md-2 text-center">
                                    <h6 class="fw-bold mb-0"><?php echo $pg['active_tenants']; ?></h6>
                                    <span class="small text-muted">Tenants</span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                            <li><a class="dropdown-item" href="../pg-details.php?id=<?php echo $pg['id']; ?>"><i class="fa-solid fa-eye me-2"></i> View Live</a></li>
                                            <li><a class="dropdown-item" href="edit-pg.php?id=<?php echo $pg['id']; ?>"><i class="fa-solid fa-pen-to-square me-2"></i> Edit Details</a></li>
                                            <li><a class="dropdown-item" href="manage-rooms.php?id=<?php echo $pg['id']; ?>"><i class="fa-solid fa-bed me-2"></i> Manage Rooms</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="delete-pg.php?id=<?php echo $pg['id']; ?>"><i class="fa-solid fa-trash me-2"></i> Delete</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php if ($pg['status'] === 'rejected'): ?>
                            <div class="alert alert-danger mt-3 mb-0 small py-2">
                                <strong>Reason for rejection:</strong> <?php echo $pg['rejection_reason']; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
