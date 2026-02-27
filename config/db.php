<?php
/**
 * Database Configuration
 * W Island Hub Backpackers Inn
 */

// Database credentials - CHECK THESE IN XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'w_island_hub');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP has no password

// Check if this is an API call that expects JSON
$isApiCall = false;
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    $isApiCall = true;
}
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $isApiCall = true;
}

// Try to connect to database
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
} catch (PDOException $e) {
    // Database connection failed
    $pdo = null;
    
    // Only output JSON error for API calls
    if ($isApiCall) {
        header('Content-Type: application/json');
        $errorMsg = "Database Connection Failed! Error: " . $e->getMessage();
        echo json_encode(['success' => false, 'message' => $errorMsg]);
        exit;
    }
    // For HTML pages, just continue with null $pdo - don't break the page
}

/**
 * Get database connection
 * @return PDO|null
 */
function getDB() {
    global $pdo;
    return $pdo;
}
