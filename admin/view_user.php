<?php
$pageTitle = "User Details";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';
require_once '../includes/config/db.php';

// Auth Check
if (!isLoggedIn() || !hasRole('admin')) {
    setFlash('danger', 'Unauthorized access.');
    redirect('/auth/login.php');
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$user_id) {
    setFlash('danger', 'Invalid user ID.');
    redirect('users.php');
}

// Fetch User Data with PG Count
$stmt = $pdo->prepare("SELECT u.*, (SELECT COUNT(*) FROM pg_listings WHERE owner_id = u.id) as pg_count FROM users u WHERE u.id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User not found.');
    redirect('users.php');
}

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="users.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Users</a>
                    <h2 class="fw-bold mt-2">User Profile Details</h2>
                </div>
                <button onclick="window.print()" class="btn btn-outline-dark rounded-pill btn-sm">
                    <i class="fa-solid fa-print me-1"></i> Print Details
                </button>
            </div>

            <div class="glass-card overflow-hidden">
                <!-- Header Profile Section -->
                <div class="bg-primary-light p-4 text-center border-bottom">
                    <img src="<?php echo getImageUrl($user['profile_image']); ?>" class="rounded-circle border border-4 border-white mb-3 shadow-sm" width="120" height="120" alt="Profile">
                    <h3 class="fw-bold mb-1"><?php echo $user['full_name']; ?></h3>
                    <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo ucfirst($user['role']); ?></span>
                </div>

                <!-- Info Grid -->
                <div class="p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 bg-light">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Email Address</label>
                                <span class="fw-bold"><?php echo $user['email']; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 bg-light">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Phone Number</label>
                                <span class="fw-bold"><?php echo $user['phone']; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 bg-light">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Verification Status</label>
                                <span class="badge <?php echo $user['is_verified'] ? 'bg-success text-white' : 'bg-warning text-dark'; ?> rounded-pill">
                                    <?php echo $user['is_verified'] ? 'Verified Account' : 'Pending Verification'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded-4 bg-light">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Joined Date</label>
                                <span class="fw-bold"><?php echo date('d F, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <?php if($user['role'] === 'owner'): ?>
                        <div class="col-12">
                            <div class="p-3 border rounded-4 bg-primary-light border-primary">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="text-primary small fw-bold text-uppercase d-block mb-1">Properties Listed</label>
                                        <h4 class="fw-bold mb-0 text-primary"><?php echo $user['pg_count']; ?> PGs Registered</h4>
                                    </div>
                                    <a href="listings.php?owner_id=<?php echo $user['id']; ?>" class="btn btn-primary rounded-pill btn-sm">View Listings</a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-muted small">Account ID: #USR-<?php echo str_pad($user['id'], 5, '0', STR_PAD_LEFT); ?></p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
