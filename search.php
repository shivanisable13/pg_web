<?php
$pageTitle = "Browse PGs";
require_once 'includes/header.php';
require_once 'includes/config/db.php';

// Get Filters
$city = isset($_GET['city']) ? sanitize($_GET['city']) : '';
$gender = isset($_GET['gender']) ? sanitize($_GET['gender']) : '';
$min_price = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 50000;

// Build Query
$query = "SELECT p.*, MIN(pi.image_url) as image_url, MIN(r.rent_per_month) as min_rent 
          FROM pg_listings p 
          LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
          LEFT JOIN rooms r ON p.id = r.pg_id
          WHERE p.status = 'approved'";

$params = [];

if (!empty($city)) {
    $query .= " AND p.city LIKE ?";
    $params[] = "%$city%";
}

if (!empty($gender)) {
    $query .= " AND p.gender_allowed = ?";
    $params[] = $gender;
}

$query .= " GROUP BY p.id HAVING min_rent >= ? AND min_rent <= ?";
$params[] = $min_price;
$params[] = $max_price;

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
if ($sort === 'price_low') {
    $query .= " ORDER BY min_rent ASC";
} elseif ($sort === 'price_high') {
    $query .= " ORDER BY min_rent DESC";
} else {
    $query .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 mb-4">
                <h5 class="fw-bold mb-4">Filters</h5>
                <form action="search.php" method="GET">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">City</label>
                        <input type="text" name="city" class="form-control" value="<?php echo $city; ?>" placeholder="Enter city">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">All</option>
                            <option value="male" <?php echo $gender === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $gender === 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="both" <?php echo $gender === 'both' ? 'selected' : ''; ?>>Both</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Price Range</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" name="min_price" class="form-control form-control-sm" value="<?php echo $min_price; ?>" placeholder="Min">
                            <span>-</span>
                            <input type="number" name="max_price" class="form-control form-control-sm" value="<?php echo $max_price; ?>" placeholder="Max">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill mt-3">Apply Filters</button>
                    <a href="search.php" class="btn btn-link w-100 text-muted small text-decoration-none mt-2">Clear All</a>
                </form>
            </div>
        </div>

        <!-- Listings Area -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0"><?php echo count($listings); ?> PGs found <?php echo !empty($city) ? "in $city" : ""; ?></h4>
            </div>

            <div class="row g-4">
                <?php if (empty($listings)): ?>
                    <div class="col-12 text-center py-5">
                        <div class="icon-box bg-light text-muted mb-3 mx-auto">
                            <i class="fa-solid fa-building-circle-exclamation fa-2x"></i>
                        </div>
                        <h5>No PGs found</h5>
                        <p class="text-muted">Try adjusting your filters or search for another city.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($listings as $pg): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="pg-card h-100 position-relative">
                            <span class="pg-badge"><?php echo ucfirst($pg['gender_allowed']); ?></span>
                            
                            <!-- Save Button -->
                            <button class="btn-save <?php echo isFavorited($_SESSION['user_id'] ?? null, $pg['id']) ? 'active' : ''; ?>" 
                                    onclick="toggleFavorite(this, <?php echo $pg['id']; ?>)">
                                <i class="fa-<?php echo isFavorited($_SESSION['user_id'] ?? null, $pg['id']) ? 'solid' : 'regular'; ?> fa-heart"></i>
                            </button>

                            <img src="<?php echo getImageUrl($pg['image_url']); ?>" class="card-img-top" alt="<?php echo $pg['title']; ?>">
                            <div class="card-body">
                                <h5 class="card-title text-truncate"><?php echo $pg['title']; ?></h5>
                                <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot"></i> <?php echo $pg['area'] . ', ' . $pg['city']; ?></p>
                                <div class="d-flex gap-2 mb-3">
                                    <span class="badge bg-light text-dark border small">WiFi</span>
                                    <span class="badge bg-light text-dark border small">AC</span>
                                    <span class="badge bg-light text-dark border small">+4 more</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-primary fw-bold">₹<?php echo number_format($pg['min_rent']); ?><small class="text-muted fs-6">/mo</small></h5>
                                    <a href="pg-details.php?id=<?php echo $pg['id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFavorite(btn, pgId) {
    // Prevent the click from triggering the PG card link
    if (event) event.stopPropagation();
    if (event) event.preventDefault();

    if (!currentUserId) {
        window.location.href = APP_URL + '/auth/login.php';
        return;
    }

    const icon = btn.querySelector('i');
    
    // Optimistic UI update
    btn.classList.toggle('active');
    const isNowActive = btn.classList.contains('active');
    icon.className = isNowActive ? 'fa-solid fa-heart' : 'fa-regular fa-heart';

    fetch(APP_URL + '/includes/api/toggle-favorite.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `pg_id=${pgId}`
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert(data.message);
            // Revert UI if failed
            btn.classList.toggle('active');
            icon.className = !isNowActive ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
        }
    })
    .catch(err => {
        console.error('Save failed:', err);
        // Revert UI if error
        btn.classList.toggle('active');
        icon.className = !isNowActive ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
