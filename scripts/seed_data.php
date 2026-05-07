<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';

echo "Seeding initial data...\n";

try {
    // 1. Create Admin
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (full_name, email, phone, password, role, is_verified) VALUES ('System Admin', 'admin@campusstay.com', '1234567890', ?, 'admin', 1)");
    $stmt->execute([$admin_pass]);

    // 2. Create Owner
    $owner_pass = password_hash('owner123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (full_name, email, phone, password, role, is_verified) VALUES ('Rajesh Kumar', 'owner@campusstay.com', '9876543210', ?, 'owner', 1)");
    $stmt->execute([$owner_pass]);
    $owner_id = $pdo->lastInsertId();

    // 3. Create Student
    $student_pass = password_hash('student123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (full_name, email, phone, password, role, is_verified) VALUES ('Rahul Sharma', 'student@campusstay.com', '8876543210', ?, 'student', 1)");
    $stmt->execute([$student_pass]);

    // 4. Create a PG Listing
    $stmt = $pdo->prepare("INSERT IGNORE INTO pg_listings (owner_id, title, description, city, area, address, pincode, gender_allowed, status) 
                          VALUES (?, 'Luxury Heights PG', 'Modern PG with all amenities including gym, high-speed WiFi, and home-style food. Near Christ University.', 'Bangalore', 'HSR Layout', '123, 27th Main, Sector 2', '560102', 'male', 'approved')");
    $stmt->execute([$owner_id]);
    $pg_id = $pdo->lastInsertId();

    // 5. Add Rooms for the PG
    $stmt = $pdo->prepare("INSERT IGNORE INTO rooms (pg_id, room_type, rent_per_month, security_deposit, total_beds, available_beds) VALUES 
                          (?, 'single', 15000, 30000, 5, 5),
                          (?, 'double', 9000, 18000, 10, 8),
                          (?, 'triple', 7000, 14000, 15, 12)");
    $stmt->execute([$pg_id, $pg_id, $pg_id]);

    // 6. Map Amenities
    $stmt = $pdo->prepare("INSERT IGNORE INTO pg_amenities (pg_id, amenity_id) VALUES (?, 1), (?, 2), (?, 3), (?, 4)");
    $stmt->execute([$pg_id, $pg_id, $pg_id, $pg_id]);

    // 7. Add a Featured Image
    $stmt = $pdo->prepare("INSERT IGNORE INTO pg_images (pg_id, image_url, is_featured) VALUES (?, 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af', 1)");
    $stmt->execute([$pg_id]);

    echo "Seeding completed successfully!\n";
    echo "Login credentials:\n";
    echo "Admin: admin@campusstay.com / admin123\n";
    echo "Owner: owner@campusstay.com / owner123\n";
    echo "Student: student@campusstay.com / student123\n";

} catch (PDOException $e) {
    echo "Error seeding data: " . $e->getMessage() . "\n";
}
?>
