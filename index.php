<?php
$pageTitle = "Find PG Accommodation";

require_once 'includes/header.php';
require_once 'includes/config/db.php';

// Fetch Featured PGs
$stmt = $pdo->query("
    SELECT 
        p.*, 
        MIN(pi.image_url) as image_url, 
        MIN(r.rent_per_month) as min_rent 
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

$featuredPGs = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">

            <!-- Left Content -->
            <div class="col-lg-6 mb-5 mb-lg-0 fade-in">

                <h1 class="display-2 fw-bold">
                    Find Your Perfect
                    <span class="text-primary d-block">
                        Home Away From Home
                    </span>
                </h1>

                <p class="lead text-secondary mt-4 mb-5">
                    Verified PGs and Student Accommodations in top cities.
                    Safe, comfortable, and affordable.
                </p>

                <!-- Search -->
                <form action="search.php" method="GET" class="search-bar-container">

                    <i class="fa-solid fa-magnifying-glass text-primary ms-3"></i>

                    <input 
                        type="text" 
                        name="city"
                        placeholder="Which city are you looking in?"
                        required
                    >

                    <button type="submit" class="btn btn-primary">
                        Search Now
                    </button>

                </form>

                <!-- Features -->
                <div class="d-flex flex-wrap gap-4 mt-4">

                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check text-success"></i>
                        <span class="fw-semibold text-muted">100% Verified</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-success"></i>
                        <span class="fw-semibold text-muted">Secure Payments</span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bolt text-success"></i>
                        <span class="fw-semibold text-muted">Instant Booking</span>
                    </div>

                </div>
            </div>

            <!-- Right Image -->
            <div class="col-lg-6 text-center fade-in">

                <img 
                    src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop"
                    class="img-fluid rounded-5 shadow-lg"
                    alt="Luxury PG"
                    style="max-height:650px; object-fit:cover;"
                >

            </div>

        </div>
    </div>
</section>

<!-- Featured Listings -->
<section class="py-5">

    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-5">

            <div>
                <h2 class="fw-bold mb-2">Featured PGs</h2>
                <p class="text-muted mb-0">
                    Explore the best verified accommodations
                </p>
            </div>

            <a href="search.php" class="btn btn-outline-primary rounded-pill px-4">
                Browse All
            </a>

        </div>

        <div class="row g-4">

            <?php if(empty($featuredPGs)): ?>

                <div class="col-12 text-center py-5">
                    <h5>No PGs Found</h5>
                    <p class="text-muted">
                        No listings available right now.
                    </p>
                </div>

            <?php else: ?>

                <?php foreach($featuredPGs as $pg): ?>

                <div class="col-md-6 col-lg-4">

                    <div class="pg-card h-100 position-relative">

                        <!-- Badge -->
                        <span class="pg-badge">
                            <?php echo ucfirst($pg['gender_allowed']); ?>
                        </span>

                        <!-- Save Button -->
                        <button 
                            class="btn-save <?php echo isFavorited($_SESSION['user_id'] ?? null, $pg['id']) ? 'active' : ''; ?>" 
                            onclick="toggleFavorite(this, <?php echo $pg['id']; ?>)"
                        >
                            <i class="fa-<?php echo isFavorited($_SESSION['user_id'] ?? null, $pg['id']) ? 'solid' : 'regular'; ?> fa-heart"></i>
                        </button>

                        <!-- Image -->
                        <div class="overflow-hidden">

                            <img 
                                src="<?php echo getImageUrl($pg['image_url']); ?>" 
                                class="card-img-top"
                                alt="<?php echo htmlspecialchars($pg['title']); ?>"
                            >

                        </div>

                        <!-- Body -->
                        <div class="card-body p-4">

                            <h5 class="fw-bold mb-2">
                                <?php echo htmlspecialchars($pg['title']); ?>
                            </h5>

                            <p class="text-muted small mb-3">
                                <i class="fa-solid fa-location-dot me-1"></i>

                                <?php 
                                echo htmlspecialchars(
                                    $pg['area'] . ', ' . $pg['city']
                                ); 
                                ?>
                            </p>

                            <!-- Amenities -->
                            <div class="d-flex flex-wrap gap-2 mb-4">

                                <span class="badge bg-light text-dark border">
                                    WiFi
                                </span>

                                <span class="badge bg-light text-dark border">
                                    AC
                                </span>

                                <span class="badge bg-light text-dark border">
                                    Food
                                </span>

                            </div>

                            <!-- Bottom -->
                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <span class="small text-muted">
                                        Starting From
                                    </span>

                                    <h4 class="fw-bold text-primary mb-0">

                                        ₹<?php echo number_format($pg['min_rent'] ?? 0); ?>

                                        <small class="fs-6 text-muted">
                                            /month
                                        </small>

                                    </h4>

                                </div>

                                <a 
                                    href="pg-details.php?id=<?php echo $pg['id']; ?>"
                                    class="btn btn-primary rounded-pill px-4"
                                >
                                    View
                                </a>

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
<section id="how-it-works" class="py-5 bg-light">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">How It Works</h2>

            <p class="text-muted">
                Find and book your ideal PG in 3 simple steps
            </p>

        </div>

        <div class="row g-4">

            <!-- Search -->
            <div class="col-md-4">

                <a href="search.php" class="text-decoration-none d-block">

                    <div class="glass-card p-5 text-center h-100 hover-translate">

                        <div class="icon-box bg-primary-light text-primary mb-4 mx-auto">
                            <i class="fa-solid fa-magnifying-glass fa-2x"></i>
                        </div>

                        <h4 class="fw-bold text-dark">Search</h4>

                        <p class="text-muted">
                            Explore verified PGs by city, budget and amenities.
                        </p>

                    </div>

                </a>

            </div>

            <!-- Book -->
            <div class="col-md-4">

                <a href="search.php" class="text-decoration-none d-block">

                    <div class="glass-card p-5 text-center h-100 hover-translate">

                        <div class="icon-box bg-success-light text-success mb-4 mx-auto">
                            <i class="fa-solid fa-calendar-check fa-2x"></i>
                        </div>

                        <h4 class="fw-bold text-dark">Book</h4>

                        <p class="text-muted">
                            Select your room and confirm instantly online.
                        </p>

                    </div>

                </a>

            </div>

            <!-- Move In -->
            <div class="col-md-4">

                <a 
                    href="<?php echo isLoggedIn() ? 'user/dashboard.php' : 'auth/login.php'; ?>" 
                    class="text-decoration-none d-block"
                >

                    <div class="glass-card p-5 text-center h-100 hover-translate">

                        <div class="icon-box bg-warning-light text-warning mb-4 mx-auto">
                            <i class="fa-solid fa-house fa-2x"></i>
                        </div>

                        <h4 class="fw-bold text-dark">Move In</h4>

                        <p class="text-muted">
                            Shift smoothly into your new comfortable home.
                        </p>

                    </div>

                </a>

            </div>

        </div>
    </div>
</section>

<script>
function toggleFavorite(btn, pgId) {

    if (event) event.stopPropagation();
    if (event) event.preventDefault();

    if (!currentUserId) {
        window.location.href = APP_URL + '/auth/login.php';
        return;
    }

    const icon = btn.querySelector('i');

    btn.classList.toggle('active');

    const isNowActive = btn.classList.contains('active');

    icon.className = isNowActive
        ? 'fa-solid fa-heart'
        : 'fa-regular fa-heart';

    fetch(APP_URL + '/includes/api/toggle-favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `pg_id=${pgId}`
    })
    .then(res => res.json())
    .then(data => {

        if (!data.success) {

            btn.classList.toggle('active');

            icon.className = !isNowActive
                ? 'fa-solid fa-heart'
                : 'fa-regular fa-heart';
        }
    })
    .catch(() => {

        btn.classList.toggle('active');

        icon.className = !isNowActive
            ? 'fa-solid fa-heart'
            : 'fa-regular fa-heart';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
