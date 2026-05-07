<?php
// includes/recommendation_helper.php

/**
 * Basic AI Recommendation Logic
 * Suggests PGs based on budget, city, and gender preferences
 */
function getRecommendedPGs($pdo, $user_id = null) {
    if (!$user_id) {
        // Fallback for guests: Trending PGs (highest rated)
        $stmt = $pdo->query("SELECT p.*, pi.image_url, MIN(r.rent_per_month) as min_rent 
                            FROM pg_listings p 
                            LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
                            LEFT JOIN rooms r ON p.id = r.pg_id
                            WHERE p.status = 'approved'
                            GROUP BY p.id 
                            ORDER BY p.created_at DESC 
                            LIMIT 4");
        return $stmt->fetchAll();
    }

    // Logic for logged-in users:
    // 1. Get user's recent search or booking city
    $stmt = $pdo->prepare("SELECT city FROM bookings b JOIN pg_listings p ON b.pg_id = p.id WHERE b.user_id = ? ORDER BY b.booking_date DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $last_city = $stmt->fetchColumn();

    if ($last_city) {
        // Recommend in the same city
        $stmt = $pdo->prepare("SELECT p.*, pi.image_url, MIN(r.rent_per_month) as min_rent 
                              FROM pg_listings p 
                              LEFT JOIN pg_images pi ON p.id = pi.pg_id AND pi.is_featured = 1
                              LEFT JOIN rooms r ON p.id = r.pg_id
                              WHERE p.status = 'approved' AND p.city = ?
                              GROUP BY p.id 
                              LIMIT 4");
        $stmt->execute([$last_city]);
        return $stmt->fetchAll();
    }

    // Default
    return getRecommendedPGs($pdo, null);
}
?>
