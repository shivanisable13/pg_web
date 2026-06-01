<?php
$pageTitle = "Verify Owner Identities";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

// Auth Check
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

// Fetch Pending Owners
$stmt = $pdo->query("SELECT * FROM users WHERE identity_status = 'pending' AND role = 'owner' ORDER BY created_at ASC");
$pending_owners = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="dashboard.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
            <h2 class="fw-bold mt-2">Owner Identity Verification</h2>
        </div>
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><?php echo count($pending_owners); ?> Pending Verifications</span>
    </div>

    <?php if(empty($pending_owners)): ?>
    <div class="glass-card p-5 text-center">
        <div class="icon-box bg-success-light text-success mx-auto mb-3">
            <i class="fa-solid fa-user-check fa-2x"></i>
        </div>
        <h4 class="fw-bold">All Owner Identities Verified</h4>
        <p class="text-muted mb-4">There are no new ID/Selfie verification requests from owners at the moment.</p>
        
        <?php 
        $pending_pgs = $pdo->query("SELECT COUNT(*) FROM pg_listings WHERE status = 'pending'")->fetchColumn();
        if($pending_pgs > 0): ?>
            <div class="alert alert-warning border-0 d-inline-block px-4 py-3 rounded-4 mb-0">
                <p class="mb-2 small fw-bold text-dark"><i class="fa-solid fa-house-circle-check me-2"></i> However, you have <?php echo $pending_pgs; ?> Property Approval(s) waiting!</p>
                <a href="approvals.php" class="btn btn-warning rounded-pill fw-bold">Review PG Listings Now</a>
            </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach($pending_owners as $owner): ?>
        <div class="col-12">
            <div class="glass-card p-4">
                <div class="row g-4 align-items-center">
                    <!-- Owner Info -->
                    <div class="col-md-3">
                        <div class="text-center">
                            <img src="<?php echo getImageUrl($owner['profile_image']); ?>" class="rounded-circle mb-3 border shadow-sm" width="100" height="100" alt="Owner">
                            <h5 class="fw-bold mb-1"><?php echo $owner['full_name']; ?></h5>
                            <p class="text-muted small mb-0"><?php echo $owner['email']; ?></p>
                            <p class="text-muted small"><?php echo $owner['phone']; ?></p>
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    <div class="col-md-6">
                        <div class="row g-3">
                            <div class="col-6">
                                <p class="small fw-bold mb-2">ID Proof</p>
                                <a href="<?php echo getImageUrl($owner['identity_proof']); ?>" target="_blank">
                                    <img src="<?php echo getImageUrl($owner['identity_proof']); ?>" class="img-fluid rounded border shadow-sm" style="height: 150px; width: 100%; object-fit: cover;">
                                </a>
                                <p class="text-center mt-1 small text-muted"><i class="fa-solid fa-magnifying-glass-plus"></i> Click to enlarge</p>
                            </div>
                            <div class="col-6">
                                <p class="small fw-bold mb-2">Selfie with ID</p>
                                <a href="<?php echo getImageUrl($owner['selfie_with_id']); ?>" target="_blank">
                                    <img src="<?php echo getImageUrl($owner['selfie_with_id']); ?>" class="img-fluid rounded border shadow-sm" style="height: 150px; width: 100%; object-fit: cover;">
                                </a>
                                <p class="text-center mt-1 small text-muted"><i class="fa-solid fa-camera"></i> Verify face matches ID</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-md-3">
                        <div class="d-flex flex-column gap-2">
                            <form action="process-verify-owner.php" method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $owner['id']; ?>">
                                <button type="submit" name="action" value="verify" class="btn btn-success w-100 rounded-pill fw-bold">
                                    <i class="fa-solid fa-check me-2"></i> Approve Identity
                                </button>
                                <button type="button" class="btn btn-outline-danger w-100 rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#rejectOwnerModal-<?php echo $owner['id']; ?>">
                                    <i class="fa-solid fa-xmark me-2"></i> Reject
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reject Owner Modal -->
            <div class="modal fade" id="rejectOwnerModal-<?php echo $owner['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <form action="process-verify-owner.php" method="POST">
                            <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Reject Identity Verification</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="user_id" value="<?php echo $owner['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Reason for Rejection</label>
                                    <textarea name="reason" class="form-control" rows="4" placeholder="e.g. ID photo is blurry, name does not match, selfie is not clear..." required></textarea>
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
