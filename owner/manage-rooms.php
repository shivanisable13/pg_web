<?php
$pageTitle = "Manage Rooms";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$pg_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$owner_id = $_SESSION['user_id'];

// Verify Ownership
$stmt = $pdo->prepare("SELECT id, title FROM pg_listings WHERE id = ? AND owner_id = ?");
$stmt->execute([$pg_id, $owner_id]);
$pg = $stmt->fetch();

if (!$pg) {
    setFlash('danger', 'Unauthorized access.');
    redirect('/owner/manage-pgs.php');
}

// Fetch Rooms
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE pg_id = ?");
$stmt->execute([$pg_id]);
$rooms = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="manage-pgs.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Properties</a>
            <h2 class="fw-bold mt-2">Manage Rooms: <?php echo $pg['title']; ?></h2>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($rooms as $room): ?>
        <div class="col-md-4">
            <div class="glass-card p-4">
                <form action="process-update-room.php" method="POST">
                    <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                    <input type="hidden" name="pg_id" value="<?php echo $pg_id; ?>">
                    
                    <h5 class="fw-bold mb-3"><?php echo ucfirst($room['room_type']); ?> Sharing</h5>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Rent per Month (₹)</label>
                        <input type="number" name="rent" class="form-control" value="<?php echo $room['rent_per_month']; ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label small fw-bold">Total Beds</label>
                            <input type="number" name="total_beds" class="form-control" value="<?php echo $room['total_beds']; ?>" required>
                        </div>
                        <div class="col">
                            <label class="form-label small fw-bold">Available Beds</label>
                            <input type="number" name="available_beds" class="form-control" value="<?php echo $room['available_beds']; ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill">Update Room</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
