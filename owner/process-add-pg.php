<?php
require_once '../includes/config/db.php';
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

// Auth Check
if (!isLoggedIn() || !hasRole('owner')) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = $_SESSION['user_id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $gender_allowed = sanitize($_POST['gender_allowed']);
    $property_type = sanitize($_POST['property_type']);
    $city = sanitize($_POST['city']);
    $area = sanitize($_POST['area']);
    $address = sanitize($_POST['address']);
    $pincode = sanitize($_POST['pincode']);
    $university_nearby = sanitize($_POST['university_nearby']);
    
    // Rooms data
    $rooms = $_POST['rooms'];
    // Amenities data
    $selected_amenities = isset($_POST['amenities']) ? $_POST['amenities'] : [];

    try {
        $pdo->beginTransaction();

        // 1. Insert into pg_listings
        $stmt = $pdo->prepare("INSERT INTO pg_listings (owner_id, title, description, city, area, address, pincode, university_nearby, gender_allowed, property_type, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$owner_id, $title, $description, $city, $area, $address, $pincode, $university_nearby, $gender_allowed, $property_type]);
        $pg_id = $pdo->lastInsertId();

        // 2. Insert into rooms
        $room_stmt = $pdo->prepare("INSERT INTO rooms (pg_id, room_type, rent_per_month, security_deposit, total_beds, available_beds) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($rooms as $room) {
            $deposit = $room['rent'] * 2; // Default 2 months deposit
            $room_stmt->execute([$pg_id, $room['type'], $room['rent'], $deposit, $room['beds'], $room['beds']]);
        }

        // 3. Insert into pg_amenities
        if (!empty($selected_amenities)) {
            $amenity_stmt = $pdo->prepare("INSERT INTO pg_amenities (pg_id, amenity_id) VALUES (?, ?)");
            foreach ($selected_amenities as $amenity_id) {
                $amenity_stmt->execute([$pg_id, $amenity_id]);
            }
        }

        // 4. Handle Image Uploads
        if (!is_dir(BASE_PATH . '/uploads/pgs')) {
            mkdir(BASE_PATH . '/uploads/pgs', 0777, true);
        }

        // Featured Image
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === 0) {
            $featured_url = uploadImage($_FILES['featured_image']);
            if ($featured_url) {
                $img_stmt = $pdo->prepare("INSERT INTO pg_images (pg_id, image_url, is_featured) VALUES (?, ?, 1)");
                $img_stmt->execute([$pg_id, $featured_url]);
            }
        }

        // Other Images
        if (isset($_FILES['other_images'])) {
            foreach ($_FILES['other_images']['name'] as $key => $name) {
                if ($_FILES['other_images']['error'][$key] === 0) {
                    $file = [
                        'name' => $_FILES['other_images']['name'][$key],
                        'type' => $_FILES['other_images']['type'][$key],
                        'tmp_name' => $_FILES['other_images']['tmp_name'][$key],
                        'error' => $_FILES['other_images']['error'][$key],
                        'size' => $_FILES['other_images']['size'][$key]
                    ];
                    $img_url = uploadImage($file);
                    if ($img_url) {
                        $img_stmt = $pdo->prepare("INSERT INTO pg_images (pg_id, image_url, is_featured) VALUES (?, ?, 0)");
                        $img_stmt->execute([$pg_id, $img_url]);
                    }
                }
            }
        }

        $pdo->commit();
        setFlash('success', 'Your PG has been submitted successfully and is pending admin approval.');
        redirect('/owner/manage-pgs.php');

    } catch (Exception $e) {
        $pdo->rollBack();
        setFlash('danger', 'Error adding PG: ' . $e->getMessage());
        redirect('/owner/add-pg.php');
    }
} else {
    redirect('/owner/dashboard.php');
}
?>
