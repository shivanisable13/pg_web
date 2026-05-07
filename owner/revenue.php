<?php
$pageTitle = "Revenue & Earnings";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

require_once '../includes/header.php';
require_once '../includes/config/db.php';

$owner_id = $_SESSION['user_id'];

// Fetch Total Revenue
$stmt = $pdo->prepare("SELECT SUM(amount) as total_revenue FROM payments p JOIN bookings b ON p.booking_id = b.id JOIN pg_listings pg ON b.pg_id = pg.id WHERE pg.owner_id = ? AND p.payment_status = 'captured'");
$stmt->execute([$owner_id]);
$total_revenue = $stmt->fetchColumn() ?: 0;

// Fetch Monthly Breakdown (Last 6 Months)
$stmt = $pdo->prepare("SELECT DATE_FORMAT(p.created_at, '%b %Y') as month, SUM(p.amount) as revenue 
                      FROM payments p 
                      JOIN bookings b ON p.booking_id = b.id 
                      JOIN pg_listings pg ON b.pg_id = pg.id 
                      WHERE pg.owner_id = ? AND p.payment_status = 'captured'
                      GROUP BY DATE_FORMAT(p.created_at, '%Y-%m')
                      ORDER BY p.created_at DESC LIMIT 6");
$stmt->execute([$owner_id]);
$monthly_data = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="glass-card p-4 h-100">
                <nav class="nav flex-column gap-2">
                    <a href="dashboard.php" class="sidebar-link"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
                    <a href="manage-pgs.php" class="sidebar-link"><i class="fa-solid fa-building"></i> My PGs</a>
                    <a href="bookings.php" class="sidebar-link"><i class="fa-solid fa-calendar-check"></i> Bookings</a>
                    <a href="revenue.php" class="sidebar-link active"><i class="fa-solid fa-wallet"></i> Revenue</a>
                    <a href="profile.php" class="sidebar-link"><i class="fa-solid fa-user-gear"></i> Settings</a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <h2 class="fw-bold mb-4">Earnings Overview</h2>

            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <div class="glass-card p-5 text-center">
                        <span class="text-muted small fw-bold text-uppercase ls-1">Total Lifetime Earnings</span>
                        <h1 class="fw-bold text-primary mb-0 mt-2">₹<?php echo number_format($total_revenue); ?></h1>
                        <p class="text-muted small mt-2 mb-0">Total revenue generated from all confirmed bookings</p>
                    </div>
                </div>
            </div>

            </div>

            <div class="glass-card p-4 mt-4">
                <h5 class="fw-bold mb-4">Recent Transactions</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Student</th>
                                <th>PG Title</th>
                                <th>Amount</th>
                                <th>Transaction ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $stmt = $pdo->prepare("SELECT p.*, u.full_name as student_name, pg.title as pg_title 
                                                  FROM payments p 
                                                  JOIN bookings b ON p.booking_id = b.id 
                                                  JOIN users u ON b.user_id = u.id 
                                                  JOIN pg_listings pg ON b.pg_id = pg.id 
                                                  WHERE pg.owner_id = ? 
                                                  ORDER BY p.created_at DESC LIMIT 10");
                            $stmt->execute([$owner_id]);
                            $transactions = $stmt->fetchAll();
                            
                            foreach ($transactions as $t): ?>
                            <tr>
                                <td class="ps-3 small"><?php echo date('d M, Y', strtotime($t['created_at'])); ?></td>
                                <td><?php echo $t['student_name']; ?></td>
                                <td><?php echo $t['pg_title']; ?></td>
                                <td class="fw-bold">₹<?php echo number_format($t['amount']); ?></td>
                                <td><code class="text-primary"><?php echo $t['transaction_id']; ?></code></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>