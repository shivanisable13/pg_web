<?php
$pageTitle = "Forgot Password - CampusStay";
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="icon-box bg-primary-light text-primary mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-key fa-xl"></i>
                    </div>
                    <h2 class="h3 fw-bold">Forgot Password?</h2>
                    <p class="text-muted">No worries! Enter your registered email and we'll send you an OTP to reset it.</p>
                </div>

                <?php displayFlash(); ?>

                <form action="process-forgot-password.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" placeholder="yourname@example.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mb-3">Send Reset Code</button>
                    
                    <div class="text-center">
                        <a href="login.php" class="text-muted small text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
