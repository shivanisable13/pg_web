<?php
$pageTitle = "User Management";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('admin')) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$role_filter = isset($_GET['role']) ? sanitize($_GET['role']) : 'all';
$users = [];

$query = "SELECT u.*, (SELECT COUNT(*) FROM pg_listings WHERE owner_id = u.id) as pg_count FROM users u";
if ($role_filter !== 'all') {
    $query .= " WHERE role = '$role_filter'";
}
$query .= " ORDER BY created_at DESC";
$users = $pdo->query($query)->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="dashboard.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
            <h2 class="fw-bold mt-2">Management</h2>
        </div>
        <div class="bg-light p-1 rounded-pill d-flex gap-1">
            <a href="users.php?role=all" class="btn rounded-pill px-4 btn-sm <?php echo $role_filter === 'all' ? 'btn-primary shadow-sm' : 'btn-light'; ?>">All Users</a>
            <a href="users.php?role=owner" class="btn rounded-pill px-4 btn-sm <?php echo $role_filter === 'owner' ? 'btn-primary shadow-sm' : 'btn-light'; ?>">Owners</a>
            <a href="users.php?role=student" class="btn rounded-pill px-4 btn-sm <?php echo $role_filter === 'student' ? 'btn-primary shadow-sm' : 'btn-light'; ?>">Students</a>
        </div>
    </div>

    <div class="glass-card p-0 overflow-hidden shadow-sm">
            <?php if (count($users) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">User Details</th>
                                <th>Role</th>
                                <th>PGs Listed</th>
                                <th>Joined Date</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo getImageUrl($u['profile_image']); ?>" class="rounded-circle me-3 border" width="45" height="45" alt="User">
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo $u['full_name']; ?></div>
                                            <div class="small text-muted"><?php echo $u['email']; ?> | <?php echo $u['phone']; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?php echo ucfirst($u['role']); ?></span>
                                </td>
                                <td><span class="badge bg-primary-light text-primary"><?php echo $u['pg_count']; ?> PGs</span></td>
                                <td><?php echo date('d M, Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <span class="badge <?php echo $u['is_verified'] ? 'bg-success-light text-success' : 'bg-warning-light text-warning'; ?> rounded-pill">
                                        <?php echo $u['is_verified'] ? 'Verified' : 'Pending'; ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="view_user.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-light rounded-pill border me-1" title="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-5 text-center">
                    <p class="text-muted mb-0">No users found in this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>