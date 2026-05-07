<?php
$pageTitle = "Saved PGs";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$user_id = $_SESSION['user_id'];

// Fetch Saved PGs (Handling case where table might not exist yet)
$favorites = [];
try {
    $stmt = $pdo->prepare("SELECT p.*, pi.image_url, MIN(r.rent_per_month) as min_rent 
                          FROM favorites f
                          JOIN pg_listings p ON f.pg_id = p.id
                          LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
                          LEFT JOIN rooms r ON p.id = r.pg_id
                          WHERE f.user_id = ?
                          GROUP BY p.id");
    $stmt->execute([$user_id]);
    $favorites = $stmt->fetchAll();
} catch (PDOException $e) {
    // If table doesn't exist, we just show empty list
    $favorites = [];
}
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <a href="bookings.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
                    <a href="favorites.php" class="sidebar-link active"><i class="fa-solid fa-heart"></i> Saved PGs</a>
                    <a href="payments.php" class="sidebar-link"><i class="fa-solid fa-wallet"></i> Payments</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user"></i> My Profile</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">Saved Properties</h2>

            <?php if (empty($favorites)): ?>
                <div class="glass-card p-5 text-center">
                    <div class="icon-box bg-light text-muted mx-auto mb-3">
                        <i class="fa-solid fa-heart-crack fa-2x"></i>
                    </div>
                    <h4>No saved PGs yet</h4>
                    <p class="text-muted">Explore properties and click the heart icon to save them for later.</p>
                    <a href="../search.php" class="btn btn-primary rounded-pill mt-3">Explore PGs</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($favorites as $pg): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="pg-card h-100 position-relative">
                            <span class="pg-badge"><?php echo ucfirst($pg['gender_allowed']); ?></span>
                            <img src="<?php echo getImageUrl($pg['image_url']); ?>" class="card-img-top" alt="<?php echo $pg['title']; ?>">
                            <div class="card-body">
                                <h5 class="card-title text-truncate"><?php echo $pg['title']; ?></h5>
                                <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot"></i> <?php echo $pg['area'] . ', ' . $pg['city']; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-primary fw-bold">₹<?php echo number_format($pg['min_rent']); ?><small class="text-muted fs-6">/mo</small></h5>
                                    <a href="../pg-details.php?id=<?php echo $pg['id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">View</a>
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
