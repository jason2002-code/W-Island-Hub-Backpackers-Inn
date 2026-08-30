<?php
/**
 * Database Migration Script
 * Adds multi-tier booking and payment fields to the bookings table.
 */

require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
if (!$pdo) {
    die("Database connection failed\n");
}

try {
    echo "Running Bookings Schema Migration...\n";

    $existingCols = $pdo->query("DESCRIBE bookings")->fetchAll(PDO::FETCH_COLUMN);

    $columnsToAdd = [
        'guest_email' => "VARCHAR(150) NULL AFTER guest_name",
        'guest_phone' => "VARCHAR(30) NULL AFTER guest_email",
        'check_in_date' => "DATE NULL AFTER guest_phone",
        'check_out_date' => "DATE NULL AFTER check_in_date",
        'expected_arrival_time' => "TIME NULL AFTER check_out_date",
        'total_amount' => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER nights",
        'paid_amount' => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total_amount",
        'balance_due' => "DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER paid_amount",
        'gcash_reference_no' => "VARCHAR(100) NULL AFTER payment_method",
        'receipt_url' => "VARCHAR(255) NULL AFTER gcash_reference_no",
        'booking_status' => "ENUM('pending_verification','confirmed','deposit_paid','pending_arrival','completed','cancelled_expired','no_show') NOT NULL DEFAULT 'pending_verification' AFTER status",
        'expires_at' => "DATETIME NULL AFTER booking_status",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];

    foreach ($columnsToAdd as $col => $definition) {
        if (!in_array($col, $existingCols)) {
            $pdo->exec("ALTER TABLE bookings ADD COLUMN `$col` $definition");
            echo " - Added column `$col`\n";
        } else {
            echo " - Column `$col` already exists\n";
        }
    }

    // Populate new columns for any legacy records
    $pdo->exec("
        UPDATE bookings SET 
            check_in_date = COALESCE(check_in_date, check_in),
            check_out_date = COALESCE(check_out_date, check_out),
            total_amount = CASE WHEN total_amount = 0.00 THEN total_price ELSE total_amount END,
            paid_amount = CASE WHEN paid_amount = 0.00 AND status = 'Confirmed' THEN total_price ELSE paid_amount END,
            balance_due = CASE WHEN status = 'Confirmed' THEN 0.00 ELSE (total_price - paid_amount) END,
            booking_status = CASE 
                WHEN status = 'Confirmed' THEN 'confirmed'
                WHEN status = 'Cancelled' THEN 'cancelled_expired'
                ELSE 'pending_verification'
            END
        WHERE check_in_date IS NULL OR total_amount = 0.00
    ");

    // Add indexes for high-speed availability & hold queries
    $existingIndexes = [];
    $idxStmt = $pdo->query("SHOW INDEX FROM bookings");
    while ($row = $idxStmt->fetch(PDO::FETCH_ASSOC)) {
        $existingIndexes[] = $row['Key_name'];
    }

    $indexesToAdd = [
        'idx_bookings_status' => "ADD INDEX `idx_bookings_status` (`booking_status`)",
        'idx_bookings_expires' => "ADD INDEX `idx_bookings_expires` (`expires_at`)",
        'idx_bookings_dates' => "ADD INDEX `idx_bookings_dates` (`check_in_date`, `check_out_date`)",
        'idx_bookings_room_status' => "ADD INDEX `idx_bookings_room_status` (`room_id`, `booking_status`)"
    ];

    foreach ($indexesToAdd as $idxName => $idxSql) {
        if (!in_array($idxName, $existingIndexes)) {
            $pdo->exec("ALTER TABLE bookings $idxSql");
            echo " - Added index `$idxName`\n";
        } else {
            echo " - Index `$idxName` already exists\n";
        }
    }

    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    die("Migration Error: " . $e->getMessage() . "\n");
}

