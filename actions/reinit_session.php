<?php
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$user_id = $input['user_id'] ?? '';
$role = $input['role'] ?? '';
$name = $input['name'] ?? '';
$email = $input['email'] ?? '';

if (empty($user_id) || empty($role)) {
    echo json_encode(['success' => false, 'message' => 'Invalid user data']);
    exit;
}

// Only allow admin role to reinitialize session
if ($role !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Start session and reinitialize
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Reinitialize session variables
$_SESSION['user_id'] = $user_id;
$_SESSION['name'] = $name;
$_SESSION['email'] = $email;
$_SESSION['role'] = $role;

echo json_encode([
    'success' => true,
    'message' => 'Session reinitialized successfully'
]);


