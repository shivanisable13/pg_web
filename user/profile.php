<?php
$pageTitle = "My Profile";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-gauge"></i> Dashboard</a>
                    <a href="bookings.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> My Bookings</a>
                    <a href="profile.php" class="sidebar-link active"><i class="fa-solid fa-user-gear"></i> Settings</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">Account Settings</h2>

            <div class="glass-card p-4">
                <form action="process-profile.php" method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-4 text-center">
                            <img src="<?php echo APP_URL . '/' . $user['profile_image']; ?>" class="rounded-circle border mb-3" width="150" height="150" alt="Profile">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Update Photo</label>
                                <input type="file" name="profile_image" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo $user['email']; ?>" disabled>
                                <small class="text-muted">Email cannot be changed.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="fw-bold mb-3">Change Password</h5>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep current">
                            </div>
                            
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold mt-3">Update Profile</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
