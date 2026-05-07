<?php
$pageTitle = "System Settings";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';
require_once '../includes/config/db.php';

if (!isLoggedIn() || !hasRole('admin')) redirect('/auth/login.php');

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commission = sanitize($_POST['commission_percent']);
    $token = sanitize($_POST['booking_token']);
    $auto_approval = isset($_POST['auto_approval']) ? '1' : '0';
    $maintenance = isset($_POST['maintenance_mode']) ? '1' : '0';

    updateSetting('commission_percent', $commission);
    updateSetting('booking_token', $token);
    updateSetting('auto_approval', $auto_approval);
    updateSetting('maintenance_mode', $maintenance);

    setFlash('success', 'System settings updated successfully.');
}

// Fetch Current Settings
$commission = getSetting('commission_percent', '10');
$token = getSetting('booking_token', '500');
$auto_approval = getSetting('auto_approval', '0');
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
                    <h5 class="fw-bold mb-4">Platform Configuration</h5>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Platform Commission (%)</label>
                        <div class="input-group">
                            <input type="number" name="commission_percent" class="form-control rounded-pill-start" value="<?php echo $commission; ?>" required>
                            <span class="input-group-text rounded-pill-end">%</span>
                        </div>
                        <div class="form-text">Percentage taken from each booking.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Default Booking Token (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text rounded-pill-start">₹</span>
                            <input type="number" name="booking_token" class="form-control rounded-pill-end" value="<?php echo $token; ?>" required>
                        </div>
                        <div class="form-text">Initial amount to be paid by student.</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch p-0 ps-5">
                            <input class="form-check-input" type="checkbox" name="auto_approval" id="autoApproval" <?php echo $auto_approval === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label small fw-bold" for="autoApproval" id="autoApprovalLabel">
                                <?php echo $auto_approval === '1' ? 'Enable Automatic PG Approval' : 'Enable Automatic PG Approval'; ?>
                            </label>
                        </div>
                        <div class="form-text ps-5">If enabled, PG listings will be live immediately without review.</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold mb-4">Security & API</h5>
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