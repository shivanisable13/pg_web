<?php
$pageTitle = "Find PG Accommodation";

require_once 'includes/header.php';
require_once 'includes/config/db.php';

// Fetch a few featured PGs
$stmt = $pdo->query("
    SELECT 
        p.*,
        MIN(pi.image_url) AS image_url,
        MIN(r.rent_per_month) AS min_rent
    FROM pg_listings p
    LEFT JOIN pg_images pi 
        ON p.id = pi.pg_id 
        AND pi.is_featured = 1
    LEFT JOIN rooms r 
        ON p.id = r.pg_id
    WHERE p.status = 'approved'
    GROUP BY p.id
    LIMIT 6
");

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0 fade-in">
                <h1 class="display-3 mb-4 fw-bold">Find Your Perfect <span class="text-primary">Home Away From Home</span></h1>
                <p class="lead mb-5 text-secondary opacity-75">Verified PGs and Student Accommodations in top cities. Safe, comfortable, and affordable.</p>
                
                <!-- Search Bar -->
                <form action="search.php" method="GET" class="search-bar-container">
                    <i class="fa-solid fa-magnifying-glass text-primary ms-3"></i>
                    <input type="text" name="city" placeholder="Which city are you looking in?" required>
                    <button type="submit" class="btn btn-primary">Search Now</button>
                </form>
                
                <div class="mt-5 d-flex gap-4 align-items-center opacity-75">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-circle-check text-success me-2"></i>
                        <span class="small fw-semibold">100% Verified</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-shield-check text-success me-2"></i>
                        <span class="small fw-semibold">Secure Payments</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-bolt-lightning text-success me-2"></i>
                        <span class="small fw-semibold">Instant Booking</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="position-relative text-center text-lg-end">
                    <img src="https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&q=80&w=1000" alt="Student Living" class="img-fluid rounded-5 shadow-xl fade-in" style="animation-delay: 0.2s">
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Featured Listings -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="h1 mb-4">Featured PGs</h2>
        <div class="row g-4">
            <?php if(empty($featuredPGs)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No PGs listed yet. Start by adding one!</p>
                </div>
            <?php else: ?>
                <?php foreach($featuredPGs as $pg): ?>
                <div class="col-md-4">
                    <div class="pg-card h-100 position-relative">
                        <span class="pg-badge"><?php echo ucfirst($pg['gender_allowed']); ?></span>
                        <img src="<?php echo getImageUrl($pg['image_url']); ?>" class="card-img-top" alt="<?php echo $pg['title']; ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold"><?php echo $pg['title']; ?></h5>
                            </div>
                            <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot"></i> <?php echo $pg['area'] . ', ' . $pg['city']; ?></p>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-light text-dark border"><i class="fa-solid fa-wifi text-primary"></i> WiFi</span>
                                <span class="badge bg-light text-dark border"><i class="fa-solid fa-utensils text-primary"></i> Food</span>
                                <span class="badge bg-light text-dark border"><i class="fa-solid fa-snowflake text-primary"></i> AC</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small">Starting from</span>
                                    <h4 class="mb-0 text-primary fw-bold">₹<?php echo number_format($pg['min_rent'] ?? 0); ?><small class="text-muted fs-6">/mo</small></h4>
                                </div>
                                <a href="pg-details.php?id=<?php echo $pg['id']; ?>" class="btn btn-outline-primary rounded-pill px-4">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- How It Works -->
<section id="how-it-works" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="h1">How It Works</h2>
            <p class="text-muted">Booking your stay is as easy as 1-2-3</p>
        </div>
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <a href="search.php" class="text-decoration-none h-100 d-block">
                    <div class="glass-card p-5 h-100 hover-translate">
                        <div class="icon-box bg-primary-light text-primary mb-4 mx-auto">
                            <i class="fa-solid fa-magnifying-glass fa-2x"></i>
                        </div>
                        <h4 class="text-dark">Search</h4>
                        <p class="text-muted">Enter your location or university and browse through verified listings.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="search.php" class="text-decoration-none h-100 d-block">
                    <div class="glass-card p-5 h-100 hover-translate">
                        <div class="icon-box bg-primary-light text-primary mb-4 mx-auto">
                            <i class="fa-solid fa-calendar-check fa-2x"></i>
                        </div>
                        <h4 class="text-dark">Book</h4>
                        <p class="text-muted">Select your room type, move-in date and book instantly with a token amount.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?php echo isLoggedIn() ? 'user/dashboard.php' : 'auth/login.php'; ?>" class="text-decoration-none h-100 d-block">
                    <div class="glass-card p-5 h-100 hover-translate">
                        <div class="icon-box bg-primary-light text-primary mb-4 mx-auto">
                            <i class="fa-solid fa-truck-moving fa-2x"></i>
                        </div>
                        <h4 class="text-dark">Move-In</h4>
                        <p class="text-muted">Arrive at your new home and start your campus life hassle-free.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
