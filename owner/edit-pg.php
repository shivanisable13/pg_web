<?php
$pageTitle = "Edit PG Listing";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$pg_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$owner_id = $_SESSION['user_id'];

// Fetch PG Details
$stmt = $pdo->prepare("SELECT * FROM pg_listings WHERE id = ? AND owner_id = ?");
$stmt->execute([$pg_id, $owner_id]);
$pg = $stmt->fetch();

if (!$pg) {
    setFlash('danger', 'Unauthorized access.');
    redirect('/owner/manage-pgs.php');
}

// Fetch existing amenities
$stmt = $pdo->prepare("SELECT amenity_id FROM pg_amenities WHERE pg_id = ?");
$stmt->execute([$pg_id]);
$current_amenities = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="manage-pgs.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Properties</a>
            <h2 class="fw-bold mt-2">Edit Property Details</h2>
        </div>
    </div>

    <div class="glass-card p-4 p-md-5">
        <form action="process-edit-pg.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="pg_id" value="<?php echo $pg_id; ?>">
            
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Property Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo $pg['title']; ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="5" required><?php echo $pg['description']; ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">City</label>
                            <input type="text" name="city" class="form-control" value="<?php echo $pg['city']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Area</label>
                            <input type="text" name="area" class="form-control" value="<?php echo $pg['area']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Detailed Address</label>
                        <input type="text" name="address" class="form-control" value="<?php echo $pg['address']; ?>" required>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Gender Allowed</label>
                        <select name="gender_allowed" class="form-select" required>
                            <option value="male" <?php echo $pg['gender_allowed'] === 'male' ? 'selected' : ''; ?>>Male Only</option>
                            <option value="female" <?php echo $pg['gender_allowed'] === 'female' ? 'selected' : ''; ?>>Female Only</option>
                            <option value="both" <?php echo $pg['gender_allowed'] === 'both' ? 'selected' : ''; ?>>Any (Co-ed)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Amenities</label>
                        <div class="d-flex flex-column gap-2">
                            <?php 
                            $all_amenities = [
                                1 => ['name' => 'WiFi', 'icon' => 'fa-wifi'],
                                2 => ['name' => 'AC', 'icon' => 'fa-snowflake'],
                                3 => ['name' => 'Food', 'icon' => 'fa-utensils'],
                                4 => ['name' => 'Gym', 'icon' => 'fa-dumbbell'],
                                5 => ['name' => 'Laundry', 'icon' => 'fa-shirt'],
                                6 => ['name' => 'Security', 'icon' => 'fa-shield-halved']
                            ];
                            foreach($all_amenities as $id => $a): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="amenities[]" value="<?php echo $id; ?>" id="amenity-<?php echo $id; ?>" <?php echo in_array($id, $current_amenities) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="amenity-<?php echo $id; ?>">
                                    <i class="fa-solid <?php echo $a['icon']; ?> me-2 small text-primary"></i> <?php echo $a['name']; ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-5">
            
            <div class="d-flex justify-content-end gap-3">
                <a href="manage-pgs.php" class="btn btn-light rounded-pill px-4">Discard Changes</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Save & Update Listing</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
