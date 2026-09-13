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

