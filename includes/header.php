<?php
// includes/header.php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    
    <script>
        const APP_URL = "<?php echo APP_URL; ?>";
        const currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    </script>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo APP_URL; ?>">
            <i class="fa-solid fa-house-chimney-window text-primary me-2"></i>
            <span>CampusStay</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>/search.php">Browse PGs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo APP_URL; ?>#how-it-works">How it Works</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-primary rounded-pill dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <?php if(hasRole('admin')): ?>
                                <i class="fa-solid fa-user-shield"></i>
                            <?php else: ?>
                                <img src="<?php echo getImageUrl($_SESSION['user_image']); ?>" 
                                     class="rounded-circle" width="28" height="28" 
                                     alt="Profile" style="object-fit:cover;"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                                <span style="width:28px;height:28px;border-radius:50%;background:rgba(255,255,255,0.3);display:none;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;">
                                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                </span>
                            <?php endif; ?>
                            <span><?php echo $_SESSION['user_name']; ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <?php if (hasRole('student')): ?>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/dashboard.php"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/user/bookings.php"><i class="fa-solid fa-calendar-check me-2"></i> My Bookings</a></li>
                            <?php elseif (hasRole('owner')): ?>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/owner/dashboard.php"><i class="fa-solid fa-gauge me-2"></i> Owner Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/owner/manage-pgs.php"><i class="fa-solid fa-building me-2"></i> My PGs</a></li>
                            <?php elseif (hasRole('admin')): ?>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/admin/dashboard.php"><i class="fa-solid fa-user-shield me-2"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-link text-decoration-none text-dark me-2">Login</a>
                    <a href="<?php echo APP_URL; ?>/auth/register.php" class="btn btn-primary px-4 rounded-pill">Join CampusStay</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="min-vh-100">
    <div class="container mt-4">
        <?php displayFlash(); ?>
    </div>