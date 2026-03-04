<?php
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

// Store the user's role before destroying the session
$userRole = $_SESSION['role'] ?? 'user';

// Destroy the session
session_destroy();

// Redirect based on user role
if ($userRole === 'admin') {
-   // Admin logout - redirect to main site (index.php)
    header("Location: index.php");
} else {
    // Regular user logout - redirect to index.php (user dashboard/login)
    header("Location: index.php");
}
-exit;