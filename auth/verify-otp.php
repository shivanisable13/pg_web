<?php
$pageTitle = "Verify Your Account";
require_once '../includes/header.php';

if (!isset($_SESSION['temp_email'])) {
    redirect('/auth/register.php');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="glass-card p-4 p-md-5 text-center">
                <div class="icon-box bg-primary-light text-primary mx-auto mb-4">
                    <i class="fa-solid fa-envelope-circle-check fa-2x"></i>
                </div>
                <h2 class="fw-bold mb-2">Check Your Email</h2>
                <p class="text-muted mb-4">We've sent a 6-digit verification code to <br><strong><?php echo $_SESSION['temp_email']; ?></strong></p>

                <form action="process-verify-otp.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase">Enter 6-Digit OTP</label>
                        <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold rounded-4" 
                               placeholder="0 0 0 0 0 0" maxlength="6" pattern="\d{6}" required style="letter-spacing: 10px;">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mb-3 shadow-sm">Verify Account</button>
                    
                    <p class="text-muted small">
                        Didn't receive the code? <a href="resend-otp.php" class="text-primary fw-bold text-decoration-none">Resend</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
