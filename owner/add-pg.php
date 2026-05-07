<?php
$pageTitle = "Add New PG";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

// Auth Check
if (!isLoggedIn() || !hasRole('owner')) {
    setFlash('danger', 'Access denied.');
    redirect('/auth/login.php');
}

require_once '../includes/header.php';
require_once '../includes/config/db.php';

// Fetch Amenities for selection
$amenities_stmt = $pdo->query("SELECT * FROM amenities");
$all_amenities = $amenities_stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="dashboard.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
                    <h2 class="fw-bold mt-2">List Your Property</h2>
                </div>
            </div>

            <form action="process-add-pg.php" method="POST" enctype="multipart/form-data" class="row g-4">
                <!-- Basic Information -->
                <div class="col-md-8">
                    <div class="glass-card p-4 mb-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-2">Basic Information</h5>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">PG Name / Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Royal Luxury PG for Gents" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="5" placeholder="Tell us about your PG, rules, and nearby landmarks..." required></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Gender Allowed</label>
                                <select name="gender_allowed" class="form-select" required>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="both">Both (Co-ed)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Property Type</label>
                                <select name="property_type" class="form-select" required>
                                    <option value="pg">PG</option>
                                    <option value="hostel">Hostel</option>
                                    <option value="flat">Shared Flat</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="glass-card p-4 mb-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-2">Location Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" name="city" class="form-control" placeholder="Bangalore" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Area</label>
                                <input type="text" name="area" class="form-control" placeholder="HSR Layout" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Full Address</label>
                                <textarea name="address" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pincode</label>
                                <input type="text" name="pincode" class="form-control" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nearby University/Colleges</label>
                                <input type="text" name="university_nearby" class="form-control" placeholder="Christ University, NIFT, etc.">
                            </div>
                        </div>
                    </div>

                    <!-- Room Plans -->
                    <div class="glass-card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                            <h5 class="fw-bold mb-0">Room Pricing Plans</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="addRoomPlan"><i class="fa-solid fa-plus me-1"></i> Add Another</button>
                        </div>
                        <div id="roomPlansContainer">
                            <div class="room-plan-item bg-light p-3 rounded-4 mb-3 position-relative">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Room Type</label>
                                        <select name="rooms[0][type]" class="form-select form-select-sm">
                                            <option value="single">Single Sharing</option>
                                            <option value="double">Double Sharing</option>
                                            <option value="triple">Triple Sharing</option>
                                            <option value="quad">Four Sharing</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Monthly Rent (₹)</label>
                                        <input type="number" name="rooms[0][rent]" class="form-control form-control-sm" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Total Beds</label>
                                        <input type="number" name="rooms[0][beds]" class="form-control form-control-sm" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Controls -->
                <div class="col-md-4">
                    <!-- Photos -->
                    <div class="glass-card p-4 mb-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-2">Property Photos</h5>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Featured Image</label>
                            <input type="file" name="featured_image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Other Images (Multiple)</label>
                            <input type="file" name="other_images[]" class="form-control" accept="image/*" multiple>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="glass-card p-4 mb-4">
                        <h5 class="fw-bold mb-4 border-bottom pb-2">Amenities</h5>
                        <div class="row g-2">
                            <?php foreach($all_amenities as $amenity): ?>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="amenities[]" value="<?php echo $amenity['id']; ?>" id="amenity-<?php echo $amenity['id']; ?>">
                                    <label class="form-check-label small" for="amenity-<?php echo $amenity['id']; ?>">
                                        <i class="fa-solid <?php echo $amenity['icon_class']; ?> text-primary me-1"></i> <?php echo $amenity['name']; ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="glass-card p-4 sticky-top" style="top: 100px;">
                        <div class="alert alert-info small mb-4">
                            <i class="fa-solid fa-circle-info me-2"></i> Your listing will be reviewed by admins before going live.
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold mb-3 shadow">Submit for Approval</button>
                        <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2">Save as Draft</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let roomPlanCount = 1;
document.getElementById('addRoomPlan').addEventListener('click', function() {
    const container = document.getElementById('roomPlansContainer');
    const newItem = document.createElement('div');
    newItem.className = 'room-plan-item bg-light p-3 rounded-4 mb-3 position-relative';
    newItem.innerHTML = `
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()"></button>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Room Type</label>
                <select name="rooms[${roomPlanCount}][type]" class="form-select form-select-sm">
                    <option value="single">Single Sharing</option>
                    <option value="double">Double Sharing</option>
                    <option value="triple">Triple Sharing</option>
                    <option value="quad">Four Sharing</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Monthly Rent (₹)</label>
                <input type="number" name="rooms[${roomPlanCount}][rent]" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Total Beds</label>
                <input type="number" name="rooms[${roomPlanCount}][beds]" class="form-control form-control-sm" required>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    roomPlanCount++;
});
</script>

<?php require_once '../includes/footer.php'; ?>
