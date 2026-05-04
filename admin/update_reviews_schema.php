<?php
// One-time schema update for reviews table
// Run once: php admin/update_reviews_schema.php
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
if (!$pdo) {
    die('Database connection failed');
}

try {
    // 1. Update pending reviews to approved
    $stmt = $pdo->exec("UPDATE reviews SET status = 'approved' WHERE status = 'pending'");
    echo "Updated {$stmt} pending reviews to approved.\n";

    // 2. Modify status enum (safe, if 'rejected' used)
    $pdo->exec("ALTER TABLE reviews MODIFY COLUMN status ENUM('approved', 'rejected') DEFAULT 'approved'");
    echo "Updated status enum.\n";

    // 3. Add visible column if not exists
    $check = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reviews' AND COLUMN_NAME = 'visible'")->fetchColumn();
    if (!$check) {
        $pdo->exec("ALTER TABLE reviews ADD COLUMN visible TINYINT(1) DEFAULT 1 AFTER status");
        echo "Added visible column.\n";
    } else {
        echo "Visible column already exists.\n";
    }

    // 4. Add admin_reply if not exists
    $check = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reviews' AND COLUMN_NAME = 'admin_reply'")->fetchColumn();
    if (!$check) {
        $pdo->exec("ALTER TABLE reviews ADD COLUMN admin_reply TEXT NULL AFTER visible");
        echo "Added admin_reply column.\n";
    } else {
        echo "Admin_reply column already exists.\n";
    }

    // Set visible=1 for existing if NULL
    $pdo->exec("UPDATE reviews SET visible = 1 WHERE visible IS NULL");

    echo "Schema update completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

