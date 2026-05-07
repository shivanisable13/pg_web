<?php
$pageTitle = "Join CampusStay";
require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="h3 fw-bold">Create Account</h2>
                    <p class="text-muted">Join the community of students and PG owners</p>
                </div>

                <form action="process-register.php" method="POST" id="registerForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                            <input type="text" name="full_name" class="form-control border-start-0" placeholder="John Doe" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-muted"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" placeholder="john@example.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                            <input type="tel" name="phone" class="form-control border-start-0" placeholder="+91 98765 43210" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required minlength="8">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">I am a...</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="role-student" value="student" checked>
                                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="role-student">
                                    <i class="fa-solid fa-graduation-cap d-block mb-2 fs-4"></i>
                                    Student
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="role" id="role-owner" value="owner">
                                <label class="btn btn-outline-primary w-100 py-3 rounded-4" for="role-owner">
                                    <i class="fa-solid fa-house-user d-block mb-2 fs-4"></i>
                                    Owner
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mb-3">Register Now</button>
                    
                    <div class="text-center">
                        <p class="mb-0 text-muted">Already have an account? <a href="login.php" class="text-primary fw-bold text-decoration-none">Login</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
