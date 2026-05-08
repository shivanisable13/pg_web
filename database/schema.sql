SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS campusstay;

USE campusstay;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'owner', 'admin') NOT NULL,
    profile_image VARCHAR(255) DEFAULT 'default_user.png',
    is_verified BOOLEAN DEFAULT FALSE,
    otp_code VARCHAR(10),
    otp_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS pg_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    area VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    university_nearby VARCHAR(255),
    gender_allowed ENUM('male', 'female', 'both') NOT NULL,
    property_type ENUM('pg', 'hostel', 'flat') DEFAULT 'pg',
    lat DECIMAL(10,8),
    lng DECIMAL(11,8),
    status ENUM('pending', 'approved', 'rejected', 'hidden') DEFAULT 'pending',
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pg_id INT NOT NULL,
    room_type ENUM('single', 'double', 'triple', 'quad') NOT NULL,
    rent_per_month DECIMAL(10,2) NOT NULL,
    security_deposit DECIMAL(10,2) NOT NULL,
    total_beds INT NOT NULL,
    available_beds INT NOT NULL,
    is_ac BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (pg_id) REFERENCES pg_listings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon_class VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS pg_amenities (
    pg_id INT NOT NULL,
    amenity_id INT NOT NULL,
    PRIMARY KEY (pg_id, amenity_id),
    FOREIGN KEY (pg_id) REFERENCES pg_listings(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS pg_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pg_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (pg_id) REFERENCES pg_listings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pg_id INT NOT NULL,
    room_id INT NOT NULL,
    move_in_date DATE NOT NULL,
    duration_months INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pg_id) REFERENCES pg_listings(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pg_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pg_id) REFERENCES pg_listings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS favorites (
    user_id INT NOT NULL,
    pg_id INT NOT NULL,
    PRIMARY KEY (user_id, pg_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pg_id) REFERENCES pg_listings(id) ON DELETE CASCADE
);

INSERT IGNORE INTO amenities (name, icon_class) VALUES
('WiFi', 'fa-wifi'),
('AC', 'fa-snowflake'),
('Food', 'fa-utensils'),
('Parking', 'fa-parking'),
('Laundry', 'fa-tshirt'),
('Gym', 'fa-dumbbell'),
('CCTV', 'fa-video'),
('Power Backup', 'fa-bolt');

INSERT IGNORE INTO users (
    full_name,
    email,
    phone,
    password,
    role,
    profile_image,
    is_verified
) VALUES (
    'Admin User',
    'admin@campusstay.com',
    '9876543210',
    '$2y$10$SSDM/qb57QewttpJVzlPvebyhRXyw4T2kqvSIFcPeWHIEMwzsg8aW',
    'admin',
    'default_user.png',
    TRUE
);

SET FOREIGN_KEY_CHECKS = 1;
