<?php
$pageTitle = "Verify Reset Code - CampusStay";
require_once '../includes/header.php';

if (!isset($_SESSION['reset_email'])) {
    redirect('/auth/forgot-password.php');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="icon-box bg-success-light text-success mx-auto mb-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-shield-check fa-xl"></i>
                    </div>
                    <h2 class="h3 fw-bold">Verify Reset Code</h2>
                    <p class="text-muted">Enter the 6-digit code sent to <strong><?php echo $_SESSION['reset_email']; ?></strong></p>
                </div>

                <?php displayFlash(); ?>

                <form action="process-verify-reset.php" method="POST">
                    <div class="mb-4">
                        <div class="d-flex gap-2 justify-content-center">
                            <input type="text" name="otp" class="form-control text-center fw-bold fs-4 rounded-3" maxlength="6" placeholder="000000" style="letter-spacing: 5px;" required autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mb-3">Verify Code</button>
                    
                    <div class="text-center">
                        <p class="small text-muted mb-0">Didn't receive code? <a href="process-forgot-password.php" class="text-primary fw-bold text-decoration-none">Resend</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
