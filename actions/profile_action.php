<?php
/**
 * Guest Profile & Account Action Handler
 */

if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

/**
 * Get user profile data
 */
if (!function_exists('getProfile')) {
function getProfile() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    $userId = $_GET['id'] ?? $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        unset($user['password']);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
        $stmt->execute([$userId]);
        $bookingCount = $stmt->fetchColumn();
        
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
                'totalBookings' => (int)$bookingCount
            ],
            'recentBookings' => $recentBookings
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
}

/**
 * Update user profile details
 */
if (!function_exists('updateProfile')) {
function updateProfile() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input)) {
        $input = $_POST;
    }
    
    $userId = $input['id'] ?? $_SESSION['user_id'] ?? null;
    $name = trim($input['name'] ?? '');
    $email = trim($input['email'] ?? '');
    
    if (!$userId || empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'User ID, name, and email are required']);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    $phone = trim($input['phone'] ?? '');
    $nationality = trim($input['nationality'] ?? '');
    $gender = trim($input['gender'] ?? '');
    $birthdate = !empty($input['birthdate']) ? $input['birthdate'] : null;
    $address = trim($input['address'] ?? '');
    $bio = trim($input['bio'] ?? '');
    $emergency_name = trim($input['emergency_name'] ?? '');
    $emergency_relation = trim($input['emergency_relation'] ?? '');
    $emergency_phone = trim($input['emergency_phone'] ?? '');

    $bunk = $input['bunk'] ?? 'no_preference';
    $dietary = $input['dietary'] ?? 'none';
    $arrival_time = $input['arrival_time'] ?? 'standard';
    $notes = trim($input['notes'] ?? '');
    
    $travelPreferences = json_encode([
        'bunk' => $bunk,
        'dietary' => $dietary,
        'arrival_time' => $arrival_time,
        'notes' => $notes
    ]);
    
    $avatarUrl = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_base = __DIR__ . '/../assets/image/avatars/';
        if (!is_dir($upload_base)) {
            mkdir($upload_base, 0755, true);
        }
        
        $fileExt = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowedExt)) {
            $newName = 'user_' . $userId . '_' . uniqid() . '.' . $fileExt;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_base . $newName)) {
                $avatarUrl = 'assets/image/avatars/' . $newName;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Email is already in use by another account']);
            return;
        }
        
        if ($avatarUrl) {
            $updateSql = "
                UPDATE users SET 
                    name = ?, email = ?, phone = ?, nationality = ?, 
                    gender = ?, birthdate = ?, address = ?, bio = ?, 
                    emergency_name = ?, emergency_relation = ?, emergency_phone = ?, 
                    travel_preferences = ?, avatar = ? 
                WHERE id = ?
            ";
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([
                $name, $email, $phone, $nationality,
                $gender, $birthdate, $address, $bio,
                $emergency_name, $emergency_relation, $emergency_phone,
                $travelPreferences, $avatarUrl, $userId
            ]);
        } else {
            $updateSql = "
                UPDATE users SET 
                    name = ?, email = ?, phone = ?, nationality = ?, 
                    gender = ?, birthdate = ?, address = ?, bio = ?, 
                    emergency_name = ?, emergency_relation = ?, emergency_phone = ?, 
                    travel_preferences = ? 
                WHERE id = ?
            ";
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([
                $name, $email, $phone, $nationality,
                $gender, $birthdate, $address, $bio,
                $emergency_name, $emergency_relation, $emergency_phone,
                $travelPreferences, $userId
            ]);
        }
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        unset($updatedUser['password']);
        
        if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId) {
            $_SESSION['name'] = $name;
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile updated successfully',
            'user' => $updatedUser
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
}

/**
 * Update user password
 */
if (!function_exists('updatePassword')) {
function updatePassword() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input)) {
        $input = $_POST;
    }
    
    $userId = $input['id'] ?? $_SESSION['user_id'] ?? null;
    $currentPassword = $input['current_password'] ?? null;
    $newPassword = $input['new_password'] ?? null;
    $confirmPassword = $input['confirm_password'] ?? null;
    
    if (!$userId || !$currentPassword || !$newPassword) {
        echo json_encode(['success' => false, 'message' => 'All password fields are required']);
        return;
    }
    
    if ($confirmPassword !== null && $newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match']);
        return;
    }
    
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        if (!password_verify($currentPassword, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            return;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
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
}

/**
 * Handle review submission from profile
 */
if (!function_exists('handleProfileReview')) {
function handleProfileReview() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $userId = $input['user_id'] ?? $_SESSION['user_id'] ?? null;
    $roomId = intval($input['room_id'] ?? 0);
    $rating = floatval($input['rating'] ?? 5);
    $comment = trim($input['comment'] ?? '');
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
        return;
    }
    if (!$roomId) {
        echo json_encode(['success' => false, 'message' => 'Room is required']);
        return;
    }
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
        return;
    }
    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Review comment cannot be empty']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, room_id, rating, comment, status, visible, created_at)
            VALUES (?, ?, ?, ?, 'approved', 1, NOW())
        ");
        $stmt->execute([$userId, $roomId, $rating, $comment]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Thank you! Your review has been posted successfully.'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error submitting review: ' . $e->getMessage()]);
    }
}
}

// Request Routing
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';

if ($method === 'GET') {
    getProfile();
} elseif ($method === 'POST' || $method === 'PUT') {
    if ($action === 'update_password' || $method === 'PUT') {
        updatePassword();
    } elseif ($action === 'submit_review') {
        handleProfileReview();
    } else {
        updateProfile();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
