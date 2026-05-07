<?php
$pageTitle = "PG Approvals";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

// Auth Check
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

// Fetch Pending PGs
$stmt = $pdo->query("SELECT p.*, u.full_name as owner_name, pi.image_url 
                    FROM pg_listings p 
                    JOIN users u ON p.owner_id = u.id 
                    LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
                    WHERE p.status = 'pending' 
                    ORDER BY p.created_at ASC");
$pending_pgs = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="dashboard.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
            <h2 class="fw-bold mt-2">PG Approval Workflow</h2>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><?php echo count($pending_pgs); ?> Pending Requests</span>
    </div>

    <?php if(empty($pending_pgs)): ?>
    <div class="glass-card p-5 text-center">
        <div class="icon-box bg-success-light text-success mx-auto mb-3">
            <i class="fa-solid fa-check-double fa-2x"></i>
        </div>
        <h4>All caught up!</h4>
        <p class="text-muted">There are no pending PG approval requests at the moment.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach($pending_pgs as $pg): ?>
        <div class="col-12">
            <div class="glass-card p-4">
                <div class="row g-4">
                    <!-- Image -->
                    <div class="col-md-3">
                        <img src="<?php echo getImageUrl($pg['image_url']); ?>" class="img-fluid rounded-4 shadow-sm" style="height: 180px; width: 100%; object-fit: cover;" alt="PG Preview">
                    </div>
                    <!-- Details -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="fw-bold mb-0"><?php echo $pg['title']; ?></h4>
                            <span class="badge bg-light text-dark border"><?php echo ucfirst($pg['property_type']); ?></span>
                        </div>
                        <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot"></i> <?php echo $pg['address']; ?>, <?php echo $pg['city']; ?></p>
                        <div class="mb-3">
                            <span class="small fw-bold text-dark">Owner:</span>
                            <span class="small text-muted"><?php echo $pg['owner_name']; ?></span>
                        </div>
                        <div class="bg-light p-3 rounded-3 small">
                            <strong>Description Snippet:</strong><br>
                            <?php echo substr($pg['description'], 0, 150); ?>...
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="col-md-3 d-flex flex-column justify-content-center gap-2">
                        <a href="../pg-details.php?id=<?php echo $pg['id']; ?>" target="_blank" class="btn btn-outline-primary rounded-pill"><i class="fa-solid fa-eye me-2"></i> Preview Details</a>
                        
                        <form action="process-approval.php" method="POST">
                            <input type="hidden" name="pg_id" value="<?php echo $pg['id']; ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success w-100 rounded-pill"><i class="fa-solid fa-check me-2"></i> Approve Listing</button>
                        </form>

                        <button type="button" class="btn btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#rejectModal-<?php echo $pg['id']; ?>">
                            <i class="fa-solid fa-xmark me-2"></i> Reject
                        </button>
                    </div>
                </div>
            </div>

            <!-- Reject Modal -->
            <div class="modal fade" id="rejectModal-<?php echo $pg['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <form action="process-approval.php" method="POST">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Reject Listing</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="pg_id" value="<?php echo $pg['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Reason for Rejection</label>
                                    <textarea name="reason" class="form-control" rows="4" placeholder="e.g. Blurry images, invalid address, pricing too high..." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-4">Confirm Rejection</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
