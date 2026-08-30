<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    getProfile();
} elseif ($method === 'POST') {
    updateProfile();
} elseif ($method === 'PUT') {
    updatePassword();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

/**
 * Get user profile data
 */
function getProfile() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Get user ID from session or query
    $userId = $_GET['id'] ?? null;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    try {
        // Check if avatar column exists
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $selectCols = "id, name, email, role, created_at";
        if (in_array('avatar', $columns)) {
            $selectCols .= ", avatar";
        }
        
        $stmt = $pdo->prepare("SELECT $selectCols FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        // Get user's booking count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
        $stmt->execute([$userId]);
        $bookingCount = $stmt->fetchColumn();
        
        // Get user's recent bookings
        $stmt = $pdo->prepare("
            SELECT b.*, r.name as room_name, r.image as room_image 
            FROM bookings b 
            LEFT JOIN rooms r ON b.room_id = r.id 
            WHERE b.user_id = ? 
            ORDER BY b.created_at DESC 
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'user' => $user,
            'stats' => [
                'totalBookings' => $bookingCount
            ],
            'recentBookings' => $recentBookings
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Update user profile
 */
function updateProfile() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Handle both JSON and multipart form data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $userId = $input['id'] ?? null;
    $name = $input['name'] ?? null;
    $email = $input['email'] ?? null;
    $avatarUrl = null;
    
    if (!$userId || !$name || !$email) {
        echo json_encode(['success' => false, 'message' => 'User ID, name, and email are required']);
        exit;
    }
    
    // Handle avatar upload if present
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_base = __DIR__ . '/../assets/image/avatars/';
        if (!is_dir($upload_base)) mkdir($upload_base, 0755, true);
        
        $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowedExt)) {
            $newName = 'user_' . $userId . '_' . uniqid() . '.' . $fileExt;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_base . $newName)) {
                $avatarUrl = 'assets/image/avatars/' . $newName;
            }
        }
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    try {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already in use by another account']);
            exit;
        }
        
        // Ensure avatar column exists
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $hasAvatar = in_array('avatar', $columns);
        
        if ($avatarUrl && !$hasAvatar) {
            $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL AFTER email");
            $hasAvatar = true;
        }
        
        // Update user profile
        if ($hasAvatar && $avatarUrl) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, avatar = ? WHERE id = ?");
            $stmt->execute([$name, $email, $avatarUrl, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $userId]);
        }
        
        // Fetch current avatar if not updated to return in response
        if ($hasAvatar && !$avatarUrl) {
            $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $avatarUrl = $stmt->fetchColumn();
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'avatar' => $hasAvatar ? $avatarUrl : null
            ]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Update user password
 */
function updatePassword() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = $input['id'] ?? null;
    $currentPassword = $input['current_password'] ?? null;
    $newPassword = $input['new_password'] ?? null;
    
    if (!$userId || !$currentPassword || !$newPassword) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required']);
        exit;
    }
    
    // Validate new password strength
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        exit;
    }
    
    try {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        // Check if current password matches
        if (!password_verify($currentPassword, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Password updated successfully'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
