<?php
require_once "connect.php";

function ensure_market_schema($conn) {
    $queries = [];

    $queries[] = "CREATE TABLE IF NOT EXISTS used_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(120) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL DEFAULT 0,
        category VARCHAR(50),
        condition_label VARCHAR(50),
        image_url VARCHAR(255),
        status ENUM('active','sold','inactive') NOT NULL DEFAULT 'active',
        buyer_id INT NULL,
        accepted_offer_id INT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_used_items_user (user_id),
        INDEX idx_used_items_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS used_offers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        buyer_id INT NOT NULL,
        offer_price DECIMAL(10,2) NOT NULL DEFAULT 0,
        status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_used_offers_item (item_id),
        INDEX idx_used_offers_buyer (buyer_id),
        INDEX idx_used_offers_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS bands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        owner_id INT NOT NULL,
        name VARCHAR(120) NOT NULL,
        genre VARCHAR(80),
        city VARCHAR(80),
        looking_for VARCHAR(120),
        description TEXT,
        contact VARCHAR(120),
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_bands_owner (owner_id),
        INDEX idx_bands_genre (genre),
        INDEX idx_bands_city (city)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS profile_ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        target_user_id INT NOT NULL,
        reviewer_id INT NOT NULL,
        rating TINYINT NOT NULL DEFAULT 0,
        comment TEXT,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uniq_profile_rating (target_user_id, reviewer_id),
        INDEX idx_profile_rating_target (target_user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS profile_meta (
        user_id INT PRIMARY KEY,
        bio TEXT,
        country VARCHAR(80),
        age TINYINT,
        city VARCHAR(80),
        instrument VARCHAR(80),
        experience VARCHAR(40),
        studio VARCHAR(120),
        tags_json TEXT,
        custom_tags_json TEXT,
        cover_url VARCHAR(255),
        background VARCHAR(20),
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $queries[] = "CREATE TABLE IF NOT EXISTS profile_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        target_user_id INT NOT NULL,
        author_user_id INT NULL,
        author_name VARCHAR(80),
        message TEXT NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_profile_messages_target (target_user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    foreach ($queries as $q) {
        if (!$conn->query($q)) {
            return $conn->error;
        }
    }
    return null;
}
