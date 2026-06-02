<?php
$pageTitle = "System Settings";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';
require_once '../includes/config/db.php';

if (!isLoggedIn() || !hasRole('admin')) redirect('/auth/login.php');

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maintenance = isset($_POST['maintenance_mode']) ? '1' : '0';
    updateSetting('maintenance_mode', $maintenance);
    setFlash('success', 'System settings updated successfully.');
}

// Fetch Current Settings
$maintenance = getSetting('maintenance_mode', '0');

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="dashboard.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
            <h2 class="fw-bold mt-2">System Settings</h2>
        </div>
    </div>

    <?php displayFlash(); ?>

    <form method="POST" action="settings.php">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold mb-4">Security &amp; API</h5>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Maintenance Mode</label>
                        <div class="form-check form-switch p-0 ps-5">
                            <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenanceMode" <?php echo $maintenance === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label fw-bold" for="maintenanceMode" id="maintenanceLabel">
                                <?php echo $maintenance === '1' ? '<span class="text-danger">Enabled (Site Offline)</span>' : '<span class="text-success">Disabled (Site Live)</span>'; ?>
                            </label>
                        </div>
                        <div class="form-text ps-5">When enabled, only administrators can access the front-end.</div>
                    </div>
                </div>
            </div>
            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow">
                    <i class="fa-solid fa-save me-2"></i> Save All Settings
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Dynamic label update for Maintenance Mode
document.getElementById('maintenanceMode').addEventListener('change', function() {
    const label = document.getElementById('maintenanceLabel');
    if (this.checked) {
        label.innerHTML = '<span class="text-danger">Enabled (Site Offline)</span>';
    } else {
        label.innerHTML = '<span class="text-success">Disabled (Site Live)</span>';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
