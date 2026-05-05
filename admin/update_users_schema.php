<?php
// One-time schema update for users and admins tables
// Run once: php admin/update_users_schema.php
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
if (!$pdo) {
    die('Database connection failed');
}

try {
    // 1. Create admins table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `email` varchar(100) NOT NULL,
      `password` varchar(255) NOT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "1. Checked/Created 'admins' table.<br>\n";

    // 2. Check if 'role' column still exists in users
    $checkRole = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'role'")->fetchColumn();
    
    if ($checkRole) {
        // 3. Migrate existing admins to the admins table
        $stmt = $pdo->exec("INSERT IGNORE INTO admins (name, email, password, created_at)
                            SELECT name, email, password, created_at FROM users WHERE role = 'admin'");
        echo "2. Migrated {$stmt} admins from 'users' to 'admins' table.<br>\n";

        // 4. Delete admins from users table
        $deleted = $pdo->exec("DELETE FROM users WHERE role = 'admin'");
        echo "3. Removed {$deleted} admin records from 'users' table.<br>\n";

        // 5. Drop role column safely
        $pdo->exec("ALTER TABLE users DROP COLUMN role");
        echo "4. Dropped 'role' column from 'users' table.<br>\n";
    } else {
        echo "2. The 'role' column has already been removed from the 'users' table.<br>\n";
    }

    echo "<strong>Schema update completed successfully!</strong><br>\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>