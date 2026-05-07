<?php
$pageTitle = "Manage Bookings";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$owner_id = $_SESSION['user_id'];

// Fetch Bookings for Owner's PGs (Filtered for duplicates)
$stmt = $pdo->prepare("SELECT b.*, p.title as pg_title, u.full_name as student_name, u.phone as student_phone, r.room_type, pay.transaction_id 
                      FROM bookings b 
                      JOIN pg_listings p ON b.pg_id = p.id 
                      JOIN users u ON b.user_id = u.id 
                      JOIN rooms r ON b.room_id = r.id
                      LEFT JOIN payments pay ON b.id = pay.booking_id
                      WHERE p.owner_id = ? 
                      AND b.id IN (SELECT MAX(id) FROM bookings GROUP BY user_id, pg_id)
                      ORDER BY b.booking_date DESC");
$stmt->execute([$owner_id]);
$bookings = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                    <a href="manage-pgs.php" class="sidebar-link"><i class="fa-solid fa-building"></i> My PGs</a>
                    <a href="bookings.php" class="sidebar-link active"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> Settings</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">Bookings Management</h2>

            <div class="glass-card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Student</th>
                                <th>PG / Room</th>
                                <th>Move-in Date</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No bookings found.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo $b['student_name']; ?></div>
                                        <div class="small text-muted"><?php echo $b['student_phone']; ?></div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold"><?php echo $b['pg_title']; ?></div>
                                        <div class="badge bg-light text-dark border small"><?php echo ucfirst($b['room_type']); ?></div>
                                    </td>
                                    <td><?php echo date('d M, Y', strtotime($b['move_in_date'])); ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = 'bg-warning-light text-warning';
                                        if ($b['status'] === 'confirmed') $statusClass = 'bg-success-light text-success';
                                        if ($b['status'] === 'cancelled') $statusClass = 'bg-danger-light text-danger';
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?> rounded-pill px-3">
                                            <?php echo ucfirst($b['status']); ?>
                                        </span>
                                        <?php if ($b['transaction_id']): ?>
                                            <div class="mt-1">
                                                <code class="small text-muted" title="Payment ID"><?php echo $b['transaction_id']; ?></code>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                                <li><a class="dropdown-item" href="process-booking-status.php?id=<?php echo $b['id']; ?>&status=confirmed"><i class="fa-solid fa-check text-success me-2"></i> Confirm</a></li>
                                                <li><a class="dropdown-item" href="process-booking-status.php?id=<?php echo $b['id']; ?>&status=cancelled"><i class="fa-solid fa-xmark text-danger me-2"></i> Cancel</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
