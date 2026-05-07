<?php
$pageTitle = "Reset Password - CampusStay";
require_once '../includes/header.php';

if (!isset($_SESSION['can_reset_password']) || !isset($_SESSION['reset_user_id'])) {
    redirect('/auth/forgot-password.php');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="h3 fw-bold">New Password</h2>
                    <p class="text-muted">Create a strong password to secure your account.</p>
                </div>

                <?php displayFlash(); ?>

                <form action="process-new-password.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0" placeholder="Minimum 8 characters" minlength="8" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="confirm_password" class="form-control border-start-0" placeholder="Repeat new password" minlength="8" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mb-3">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
