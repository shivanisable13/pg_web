<?php
require_once 'includes/config/db.php';
require_once 'includes/config/config.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch PG Details
$stmt = $pdo->prepare("SELECT p.*, u.full_name as owner_name, u.phone as owner_phone, u.profile_image as owner_image 
                      FROM pg_listings p 
                      JOIN users u ON p.owner_id = u.id 
                      WHERE p.id = ? AND p.status = 'approved'");
$stmt->execute([$id]);
$pg = $stmt->fetch();

if (!$pg) {
    setFlash('danger', 'PG not found or not approved.');
    redirect('/search.php');
}

$pageTitle = $pg['title'];
require_once 'includes/header.php';

// Fetch Images
$stmt = $pdo->prepare("SELECT * FROM pg_images WHERE pg_id = ?");
$stmt->execute([$id]);
$images = $stmt->fetchAll();

// Fetch Amenities
$stmt = $pdo->prepare("SELECT a.* FROM amenities a JOIN pg_amenities pa ON a.id = pa.amenity_id WHERE pa.pg_id = ?");
$stmt->execute([$id]);
$amenities = $stmt->fetchAll();

// Fetch Room Plans
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE pg_id = ?");
$stmt->execute([$id]);
$rooms = $stmt->fetchAll();
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="search.php">Search</a></li>
            <li class="breadcrumb-item active"><?php echo $pg['city']; ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left: Gallery and Details -->
        <div class="col-lg-8">
            <!-- Gallery -->
            <div class="glass-card overflow-hidden mb-4">
                <?php if(!empty($images)): ?>
                <div id="pgGallery" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach($images as $index => $img): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo getImageUrl($img['image_url']); ?>" class="d-block w-100" style="height: 450px; object-fit: cover;" alt="PG Image">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#pgGallery" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#pgGallery" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
                <?php else: ?>
                <img src="https://via.placeholder.com/800x450" class="w-100" style="height: 450px; object-fit: cover;" alt="Placeholder">
                <?php endif; ?>
            </div>

            <!-- Title & Info -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="fw-bold"><?php echo $pg['title']; ?></h1>
                    <p class="text-muted fs-5"><i class="fa-solid fa-location-dot text-primary"></i> <?php echo $pg['address']; ?>, <?php echo $pg['city']; ?> - <?php echo $pg['pincode']; ?></p>
                </div>
                <div class="text-end">
                    <div class="badge bg-primary px-3 py-2 fs-6 rounded-pill mb-2"><?php echo ucfirst($pg['gender_allowed']); ?> Only</div>
                </div>
            </div>

            <!-- Amenities -->
            <div class="glass-card p-4 mb-4">
                <h4 class="fw-bold mb-4">Amenities</h4>
                <div class="row g-3">
                    <?php foreach($amenities as $amenity): ?>
                    <div class="col-6 col-md-3">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                            <i class="fa-solid <?php echo $amenity['icon_class']; ?> text-primary fs-4"></i>
                            <span class="fw-semibold"><?php echo $amenity['name']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="glass-card p-4 mb-4">
                <h4 class="fw-bold mb-3">About this PG</h4>
                <p class="text-muted leading-relaxed"><?php echo nl2br($pg['description']); ?></p>
            </div>

            <!-- Map (OpenStreetMap + Leaflet - No API Key Required) -->
            <div class="glass-card p-4 mb-4">
                <h4 class="fw-bold mb-1">Location</h4>
                <p class="text-muted small mb-3">
                    <i class="fa-solid fa-location-dot text-primary me-1"></i>
                    <?php echo $pg['address'] . ', ' . $pg['area'] . ', ' . $pg['city']; ?>
                </p>
                <div id="leaflet-map" style="height: 350px; border-radius: 1rem; overflow: hidden; z-index: 1;" class="shadow-sm"></div>
            </div>
        </div>

        <!-- Right: Booking & Owner -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px; z-index: 10;">
                <!-- Booking Card -->
                <div class="glass-card p-4 mb-4 border-primary border-2">
                    <?php if(isLoggedIn() && hasRole('admin')): ?>
                        <div class="text-center py-4">
                            <div class="icon-box bg-primary-light text-primary mx-auto mb-3">
                                <i class="fa-solid fa-user-shield fa-2x"></i>
                            </div>
                            <h5 class="fw-bold">Admin Preview</h5>
                            <p class="text-muted small">You are viewing this property as an administrator. Booking is disabled for admin accounts.</p>
                            <a href="admin/listings.php" class="btn btn-outline-primary btn-sm rounded-pill px-4">Back to Admin</a>
                        </div>
                    <?php elseif (isLoggedIn() && $_SESSION['user_id'] == $pg['owner_id']): ?>
                        <div class="text-center py-4">
                            <div class="icon-box bg-primary-light text-primary mx-auto mb-3">
                                <i class="fa-solid fa-user-tie fa-2x"></i>
                            </div>
                            <h5 class="fw-bold">Property Owner View</h5>
                            <p class="text-muted small">You are viewing your own property listing.</p>
                            <hr>
                            <div class="d-grid gap-2">
                                <a href="owner/manage-pgs.php" class="btn btn-primary rounded-pill">
                                    <i class="fa-solid fa-gear me-2"></i> Manage Listing
                                </a>
                                <a href="owner/dashboard.php" class="btn btn-outline-primary rounded-pill">
                                    <i class="fa-solid fa-chart-line me-2"></i> View Analytics
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <h4 class="fw-bold mb-4">Select Your Plan</h4>
                        <form action="includes/api/booking.php" method="POST">
                            <input type="hidden" name="pg_id" value="<?php echo $pg['id']; ?>">
                            
                            <div class="mb-4">
                                <?php foreach($rooms as $room): ?>
                                <div class="form-check p-0 mb-3">
                                    <input type="radio" class="btn-check room-selector" name="room_id" id="room-<?php echo $room['id']; ?>" value="<?php echo $room['id']; ?>" data-rent="<?php echo $room['rent_per_month']; ?>" required>
                                    <label class="btn btn-outline-light text-dark w-100 text-start p-3 rounded-4 border" for="room-<?php echo $room['id']; ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-bold d-block"><?php echo ucfirst($room['room_type']); ?> Sharing</span>
                                                <span class="small text-muted"><?php echo $room['available_beds']; ?> beds left</span>
                                            </div>
                                            <div class="text-end">
                                                <span class="h5 fw-bold text-primary mb-0">₹<?php echo number_format($room['rent_per_month']); ?></span>
                                                <span class="small d-block text-muted">/mo</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold">Move-in Date</label>
                                <input type="date" name="move_in_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Duration (Months)</label>
                                <select name="duration" id="durationSelector" class="form-select">
                                    <option value="1">1 Month</option>
                                    <option value="3">3 Months</option>
                                    <option value="6">6 Months</option>
                                    <option value="12">12 Months</option>
                                </select>
                            </div>

                            <!-- Dynamic Summary -->
                            <div id="bookingSummary" class="bg-light rounded-4 p-3 mb-4 d-none">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-muted">Monthly Rent</span>
                                    <span class="small fw-bold" id="summaryRent">₹0</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small text-muted">Duration</span>
                                    <span class="small fw-bold" id="summaryDuration">1 Month</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                    <span class="small text-muted">Move-out Date</span>
                                    <span class="small fw-bold text-dark" id="summaryMoveOut">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Total Booking Amount</span>
                                    <span class="h5 fw-bold text-primary mb-0" id="summaryTotal">₹0</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill mb-3 shadow">Book This Stay</button>
                            <p class="text-center small text-muted mb-0">You won't be charged yet</p>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Owner Card -->
                <div class="glass-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <?php if(!empty($pg['owner_image'])): ?>
                            <img src="<?php echo getImageUrl($pg['owner_image']); ?>" class="rounded-circle border border-2 shadow-sm" width="60" height="60" style="object-fit: cover;" alt="<?php echo $pg['owner_name']; ?>">
                        <?php else: ?>
                            <div class="rounded-circle border border-2 shadow-sm bg-primary text-white d-flex align-items-center justify-content-center fw-bold" width="60" height="60" style="width: 60px; height: 60px; font-size: 1.2rem;">
                                <?php echo substr($pg['owner_name'], 0, 1); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h6 class="fw-bold mb-0"><?php echo $pg['owner_name']; ?></h6>
                            <span class="badge bg-success-light text-success small" style="font-size: 0.7rem;">Verified Owner</span>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $pg['owner_phone']); ?>" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm py-2">
                            <i class="fa-brands fa-whatsapp me-2"></i> Chat with Owner
                        </a>
                        <a href="tel:<?php echo $pg['owner_phone']; ?>" class="btn btn-light rounded-pill border btn-sm py-2">
                            <i class="fa-solid fa-phone me-2"></i> <?php echo $pg['owner_phone']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomSelectors = document.querySelectorAll('.room-selector');
    const durationSelector = document.getElementById('durationSelector');
    const summaryDiv = document.getElementById('bookingSummary');
    const summaryRent = document.getElementById('summaryRent');
    const summaryDuration = document.getElementById('summaryDuration');
    const summaryTotal = document.getElementById('summaryTotal');

    const moveInInput = document.querySelector('input[name="move_in_date"]');
    const summaryMoveOut = document.getElementById('summaryMoveOut');

    function updateSummary() {
        let selectedRoom = document.querySelector('.room-selector:checked');
        let duration = parseInt(durationSelector.value);
        let moveInDate = moveInInput.value;

        if (selectedRoom) {
            let rent = parseInt(selectedRoom.dataset.rent);
            let total = rent * duration;

            summaryRent.innerText = '₹' + rent.toLocaleString();
            summaryDuration.innerText = duration + (duration > 1 ? ' Months' : ' Month');
            summaryTotal.innerText = '₹' + total.toLocaleString();

            if (moveInDate) {
                let date = new Date(moveInDate);
                date.setMonth(date.getMonth() + duration);
                date.setDate(date.getDate() - 1);
                const options = { day: '2-digit', month: 'short', year: 'numeric' };
                summaryMoveOut.innerText = date.toLocaleDateString('en-GB', options);
            }

            summaryDiv.classList.remove('d-none');
        }
    }

    roomSelectors.forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });

    durationSelector.addEventListener('change', updateSummary);
    moveInInput.addEventListener('change', updateSummary);
});
</script>

<!-- Leaflet.js - Real Interactive Map (No API Key Required) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var pgArea   = "<?php echo addslashes($pg['area']); ?>";
    var pgCity   = "<?php echo addslashes($pg['city']); ?>";
    var pgTitle  = "<?php echo addslashes($pg['title']); ?>";
    var pgAddress = "<?php echo addslashes($pg['address'] . ', ' . $pg['area'] . ', ' . $pg['city']); ?>";
    var pgLat    = <?php echo !empty($pg['lat']) ? $pg['lat'] : 'null'; ?>;
    var pgLng    = <?php echo !empty($pg['lng']) ? $pg['lng'] : 'null'; ?>;

    function initMap(lat, lng, zoom) {
        var map = L.map('leaflet-map').setView([lat, lng], zoom || 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        var icon = L.divIcon({
            className: '',
            html: '<div style="background:#4f46e5;width:36px;height:36px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 10px rgba(79,70,229,0.5);"></div>',
            iconSize: [36, 36],
            iconAnchor: [18, 36],
            popupAnchor: [0, -36]
        });

        L.marker([lat, lng], {icon: icon})
            .addTo(map)
            .bindPopup('<strong>' + pgTitle + '</strong><br><small>' + pgAddress + '</small>')
            .openPopup();
    }

    function geocode(query, zoom) {
        return fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(data => {
                if (data && data.length > 0) {
                    initMap(parseFloat(data[0].lat), parseFloat(data[0].lon), zoom);
                    return true;
                }
                return false;
            });
    }

    if (pgLat && pgLng) {
        initMap(pgLat, pgLng, 15);
    } else {
        // Try from most specific to least specific
        var queries = [
            { q: pgArea + ', ' + pgCity + ', Karnataka, India', zoom: 15 },
            { q: pgArea + ', ' + pgCity + ', India', zoom: 15 },
            { q: pgCity + ', Karnataka, India', zoom: 13 }
        ];

        (function tryNext(i) {
            if (i >= queries.length) return;
            geocode(queries[i].q, queries[i].zoom).then(found => {
                if (!found) tryNext(i + 1);
            });
        })(0);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
