<?php
$pageTitle = "Platform Transactions";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('admin')) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$stmt = $pdo->query("SELECT p.*, b.total_amount, u.full_name as student_name, pg.title as pg_title 
                    FROM payments p 
                    JOIN bookings b ON p.booking_id = b.id 
                    JOIN users u ON b.user_id = u.id 
                    JOIN pg_listings pg ON b.pg_id = pg.id 
                    ORDER BY p.created_at DESC");
$transactions = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="dashboard.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard</a>
            <h2 class="fw-bold mt-2">Platform Transactions</h2>
        </div>
    </div>

    <div class="glass-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Transaction ID</th>
                        <th>Student</th>
                        <th>Property</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($transactions)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">No transactions recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($transactions as $tx): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary"><?php echo $tx['transaction_id']; ?></td>
                            <td><?php echo $tx['student_name']; ?></td>
                            <td><?php echo $tx['pg_title']; ?></td>
                            <td class="fw-bold">₹<?php echo number_format($tx['amount']); ?></td>
                            <td>
                                <span class="badge bg-success-light text-success rounded-pill px-3">
                                    <?php echo ucfirst($tx['payment_status']); ?>
                                </span>
                            </td>
                            <td class="text-end pe-4 small text-muted"><?php echo date('d M, Y H:i', strtotime($tx['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>