<?php
$pageTitle = "Find PG Accommodation";

require_once 'includes/header.php';
require_once 'includes/config/db.php';

// Fetch Featured PGs
$stmt = $pdo->query("
    SELECT 
        p.*,
        MIN(pi.image_url) AS image_url,
        MIN(r.rent_per_month) AS min_rent
    FROM pg_listings p
    LEFT JOIN pg_images pi 
        ON p.id = pi.pg_id 
        AND pi.is_featured = 1
    LEFT JOIN rooms r 
        ON p.id = r.pg_id
    WHERE p.status = 'approved'
    GROUP BY p.id
    LIMIT 6
");

$featuredPGs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusStay</title>
</head>

<body>

<section class="py-5 bg-light">

    <div class="container">

        <h2 class="h1 mb-4">Featured PGs</h2>

        <div class="row g-4">

            <?php if(empty($featuredPGs)): ?>

                <div class="col-12 text-center py-5">
                    <p class="text-muted">
                        No PGs listed yet.
                    </p>
                </div>

            <?php else: ?>

                <?php foreach($featuredPGs as $pg): ?>

                <div class="col-md-4">

                    <div class="card h-100">

                        <img 
                            src="<?php echo $pg['image_url'] ?: 'uploads/default.jpg'; ?>" 
                            class="card-img-top"
                            alt="<?php echo htmlspecialchars($pg['title']); ?>"
                            style="height:250px;object-fit:cover;"
                        >

                        <div class="card-body">

                            <h5 class="card-title">
                                <?php echo htmlspecialchars($pg['title']); ?>
                            </h5>

                            <p class="text-muted">
                                <?php echo htmlspecialchars($pg['city']); ?>
                            </p>

                            <h4 class="text-primary">
                                ₹<?php echo number_format($pg['min_rent'] ?? 0); ?>/month
                            </h4>

                            <a 
                                href="pg-details.php?id=<?php echo $pg['id']; ?>" 
                                class="btn btn-primary"
                            >
                                View Details
                            </a>

                        </div>
                    </div>
                </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</section>

</body>
</html>
