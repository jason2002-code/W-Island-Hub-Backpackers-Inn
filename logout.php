<?php
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

// Store the user's role before destroying the session
$logoutType = $_GET['type'] ?? 'user';
$userRole = $logoutType === 'admin' ? 'admin' : 'user';

// Unset specific session based on role, avoiding destroying the concurrent session
if ($logoutType === 'admin') {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_role']);
} else {
    unset($_SESSION['user_id']);
    unset($_SESSION['name']);
    unset($_SESSION['role']);
}

// Redirect based on user role
if ($userRole === 'admin') {
    // Admin logout - redirect to main site (index.php)
    header("Location: index.php");
} else {
    // Regular user logout - redirect to index.php (user dashboard/login)
    header("Location: index.php");
}
exit;