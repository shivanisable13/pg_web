<?php
$pageTitle = "Send Notification";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$owner_id = $_SESSION['user_id'];

// Fetch Owner's PGs to filter tenants
$stmt = $pdo->prepare("SELECT id, title FROM pg_listings WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$pgs = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-5">
                <h2 class="fw-bold mb-4">Broadcast Notification</h2>
                <p class="text-muted mb-4">Send a message to all verified tenants in your properties.</p>

                <form action="process-notification.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Property</label>
                        <select name="pg_id" class="form-select" required>
                            <option value="all">All Properties</option>
                            <?php foreach ($pgs as $pg): ?>
                            <option value="<?php echo $pg['id']; ?>"><?php echo $pg['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Message Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Rent Reminder, Maintenance Notice" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Message Content</label>
                        <textarea name="message" class="form-control" rows="6" placeholder="Type your message here..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold w-100">Send Notification Now</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
