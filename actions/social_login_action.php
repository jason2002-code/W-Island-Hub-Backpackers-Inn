<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$email = $input['email'] ?? '';
$name = $input['name'] ?? '';
$provider = $input['provider'] ?? '';

if (empty($email) || empty($provider)) {
    echo json_encode(['success' => false, 'message' => 'Email and provider are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

$pdo = getDB();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // If provider is Google, ensure the user has verified the email via the code/link sent to their Gmail
    if (strtolower($provider) === 'google') {
        $stmtVer = $pdo->prepare("
            SELECT id FROM email_verifications 
            WHERE email = ? AND verified = 1 AND verified_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY id DESC LIMIT 1
        ");
        $stmtVer->execute([$email]);
        if (!$stmtVer->fetch()) {
            echo json_encode([
                'success' => false,
                'requires_verification' => true,
                'email' => $email,
                'name' => $name,
                'provider' => $provider,
                'message' => 'Please enter the verification code sent to your Gmail to confirm your account.'
            ]);
            exit;
        }
    }

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        // User exists, fetch details
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // User doesn't exist, register a new account automatically
        $name = empty($name) ? explode('@', $email)[0] : $name;
        // Generate a random secure password for social accounts
        $randomPassword = bin2hex(random_bytes(10));
        $hashedPassword = password_hash($randomPassword, PASSWORD_DEFAULT);
        
        $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $insertStmt->execute([$name, $email, $hashedPassword]);
        
        $user = [
            'id' => $pdo->lastInsertId(),
            'name' => $name,
            'email' => $email,
            'role' => 'user'
        ];
    }
    
    // Start session 
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    
    echo json_encode(['success' => true, 'message' => "Authenticated with $provider", 'user' => $user]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}