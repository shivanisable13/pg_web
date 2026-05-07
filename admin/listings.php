<?php
$pageTitle = "Platform Listings";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('admin')) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$stmt = $pdo->query("SELECT p.*, u.full_name as owner_name, pi.image_url 
                    FROM pg_listings p 
                    JOIN users u ON p.owner_id = u.id 
                    LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
                    ORDER BY p.created_at DESC");
$listings = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="dashboard.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
            <h2 class="fw-bold mt-2">All PG Listings</h2>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach($listings as $pg): ?>
        <div class="col-12">
            <div class="glass-card p-3">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <img src="<?php echo getImageUrl($pg['image_url']); ?>" class="rounded-3 w-100" style="height: 100px; object-fit: cover;" alt="PG">
                    </div>
                    <div class="col-md-4">
                        <h5 class="fw-bold mb-1"><?php echo $pg['title']; ?></h5>
                        <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot"></i> <?php echo $pg['area'] . ', ' . $pg['city']; ?></p>
                        <span class="small text-muted">Owner: <?php echo $pg['owner_name']; ?></span>
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
                    <div class="col-md-4 text-end">
                        <a href="../pg-details.php?id=<?php echo $pg['id']; ?>" class="btn btn-sm btn-light rounded-pill px-3 me-2">View</a>
                        <?php if($pg['status'] === 'pending'): ?>
                        <a href="approvals.php" class="btn btn-sm btn-primary rounded-pill px-3">Review</a>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-danger rounded-circle ms-2"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>