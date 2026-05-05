<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

$pdo = getDB();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Check if user exists in either table using UNION
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, 'admin' as role FROM admins WHERE email = ?
        UNION
        SELECT id, name, email, password, 'user' as role FROM users WHERE email = ?
    ");
    $stmt->execute([$email, $email]);
    $foundUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $authenticatedUser = null;
    // Verify password against found records (handles identical emails across tables)
    foreach ($foundUsers as $u) {
        if ($password === $u['password'] || password_verify($password, $u['password'])) {
            $authenticatedUser = $u;
            break;
        }
    }
    
    if ($authenticatedUser) {
        $user = $authenticatedUser;
        // Login successful - return user data (excluding password)
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Separate admin and user session keys to prevent cross-tab contamination
        if ($user['role'] === 'admin') {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_name'] = $user['name'];
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
        }

        $redirect = ($user['role'] === 'admin') ? 'admin/dashboard.php' : 'index.php';

        echo json_encode([
            'success' => true, 
            'message' => 'Login successful!',
            'redirect' => $redirect,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
