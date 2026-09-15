<?php
/**
 * DATABASE MIGRATION RUNNER
 * Applies any missing schema updates to ensure full compatibility
 * with Multi-Tier Reservation Payment Engine.
 */
require_once __DIR__ . '/db.php';

function runMigrations() {
    $pdo = getDB();
    if (!$pdo) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }

    try {
        // List of columns to ensure on bookings table
        $columnsToEnsure = [
            'guest_email' => "VARCHAR(100) NULL AFTER `guest_name`",
            'guest_phone' => "VARCHAR(50) NULL AFTER `guest_email`",
            'check_in_date' => "DATE NULL AFTER `guest_phone`",
            'check_out_date' => "DATE NULL AFTER `check_in_date`",
            'expected_arrival_time' => "TIME NULL AFTER `check_out_date`",
            'total_amount' => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `nights`",
            'paid_amount' => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`",
            'balance_due' => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `paid_amount`",
            'gcash_reference_no' => "VARCHAR(100) NULL AFTER `payment_method`",
            'paymongo_session_id' => "VARCHAR(150) NULL AFTER `gcash_reference_no`",
            'paymongo_payment_id' => "VARCHAR(150) NULL AFTER `paymongo_session_id`",
            'receipt_url' => "VARCHAR(255) NULL AFTER `paymongo_payment_id`",
            'booking_status' => "ENUM('pending_verification','confirmed','deposit_paid','pending_arrival','completed','cancelled_expired','no_show') NOT NULL DEFAULT 'pending_verification' AFTER `status`",
            'expires_at' => "DATETIME NULL AFTER `booking_status`",
            'special_requests' => "TEXT NULL AFTER `expires_at`",
            'updated_at' => "TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`"
        ];

        // Fetch existing columns
        $stmt = $pdo->query("SHOW COLUMNS FROM `bookings`");
        $existingCols = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $added = [];
        foreach ($columnsToEnsure as $colName => $colDef) {
            if (!in_array($colName, $existingCols)) {
                $pdo->exec("ALTER TABLE `bookings` ADD COLUMN `{$colName}` {$colDef}");
                $added[] = $colName;
            }
        }

        // Ensure user_id is nullable for guest reservations
        $pdo->exec("ALTER TABLE `bookings` MODIFY COLUMN `user_id` INT(11) NULL");

        // Sync check_in_date and total_amount for existing records
        $pdo->exec("UPDATE `bookings` SET `check_in_date` = `check_in` WHERE (`check_in_date` IS NULL OR `check_in_date` = '0000-00-00') AND `check_in` IS NOT NULL");
        $pdo->exec("UPDATE `bookings` SET `check_out_date` = `check_out` WHERE (`check_out_date` IS NULL OR `check_out_date` = '0000-00-00') AND `check_out` IS NOT NULL");
        $pdo->exec("UPDATE `bookings` SET `total_amount` = `total_price` WHERE `total_amount` = 0.00 AND `total_price` > 0.00");
        $pdo->exec("UPDATE `bookings` SET `total_price` = `total_amount` WHERE (`total_price` IS NULL OR `total_price` = 0.00) AND `total_amount` > 0.00");

        // Sync legacy status mappings
        $pdo->exec("UPDATE `bookings` SET `booking_status` = 'confirmed', `paid_amount` = `total_amount`, `balance_due` = 0.00 WHERE `status` = 'Confirmed' AND `booking_status` = 'pending_verification'");
        $pdo->exec("UPDATE `bookings` SET `booking_status` = 'cancelled_expired' WHERE `status` = 'Cancelled' AND `booking_status` = 'pending_verification'");
        $pdo->exec("UPDATE `bookings` SET `booking_status` = 'pending_arrival', `balance_due` = `total_amount` WHERE `status` = 'Pending' AND (`payment_method` LIKE '%Hotel%' OR `payment_method` = 'pay_at_hotel') AND `booking_status` = 'pending_verification'");

        // Ensure amenities table exists
        $pdo->exec("CREATE TABLE IF NOT EXISTS `amenities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(150) NOT NULL,
            `description` text NOT NULL,
            `icon` varchar(100) NOT NULL DEFAULT 'fas fa-star',
            `category` varchar(50) NOT NULL DEFAULT 'General',
            `image_url` varchar(255) DEFAULT NULL,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed initial amenities if empty
        $checkAmenities = $pdo->query("SELECT COUNT(*) FROM `amenities`")->fetchColumn();
        if ($checkAmenities == 0) {
            $defaultAmenities = [
                [1, 'Beachfront & Coral Coast', 'Steps away from pristine white sands. Enjoy early morning ocean dips, snorkeling among reef life, and front-row seats to world-famous sunsets.', 'fas fa-umbrella-beach', 'Location', 'assets/image/amenity_beachfront.jpg', 1],
                [2, 'Nomad-Grade Starlink WiFi', 'Lightning-fast, reliable satellite internet across all dorms, private rooms, and chill lounges. Seamless for remote work, video calls, and streaming.', 'fas fa-wifi', 'Connectivity', 'assets/image/amenity_starlink.jpg', 2],
                [3, 'Sunset Social Lounge & Bar', 'Vibrant open-air communal hub with board games, craft beers, refreshing tropical fruit shakes, and evening acoustic music jams.', 'fas fa-cocktail', 'Social', 'assets/image/amenity_lounge_bar.jpg', 3],
                [4, 'Scooter Rentals & Island Tours', 'Rent scooters right from the front desk. Book hassle-free tours to Chocolate Hills, Tarsier Sanctuaries, and dolphin watching expeditions.', 'fas fa-motorcycle', 'Adventures', 'assets/image/amenity_scooter_tour.jpg', 4],
                [5, 'Keycard Security & Lockers', 'Travel with total peace of mind. Every dorm guest receives an oversized security locker, personal bedside power socket, and reading lamp.', 'fas fa-key', 'Security', 'assets/image/amenity_lockers.jpg', 5],
                [6, 'Free Coffee & Water Refills', 'Complimentary fresh-brewed local island coffee every morning. Unlimited filtered cold drinking water stations to keep you energized and eco-friendly.', 'fas fa-mug-hot', 'Dining', 'assets/image/amenity_coffee_water.jpg', 6]
            ];
            $stmtInsert = $pdo->prepare("INSERT INTO `amenities` (`id`, `title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            foreach ($defaultAmenities as $amenity) {
                $stmtInsert->execute($amenity);
            }
        }

        return [
            'success' => true,
            'message' => 'Migration complete',
            'columns_added' => $added
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Migration failed: ' . $e->getMessage()
        ];
    }
}

// If run via CLI, execute directly
if (php_sapi_name() === 'cli') {
    $result = runMigrations();
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
}

