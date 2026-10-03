<?php
/**
 * SESSION SYNC ENDPOINT
 * Verifies that the current PHP session matches the user_id the client expects.
 * Called by profile.php (via JS) on page load to detect cross-tab session conflicts.
 *
 * POST body: { "user_id": <int>, "role": "user" }
 *
 * - If the session matches → responds with success + current user data.
 * - If the session differs → re-sets the session to match the requested user_id
 *   (after validating the user exists in the DB) and responds with success.
 * - If the user is not found → responds with logout.
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$clientUserId = (int)($input['user_id'] ?? 0);

if ($clientUserId <= 0) {
    echo json_encode(['success' => false, 'action' => 'logout', 'message' => 'No user_id provided']);
    exit;
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
$pdo = getDB();

// Verify the user actually exists in the DB
$stmt = $pdo->prepare("SELECT id, name, email, avatar, role FROM users WHERE id = ? AND (is_archived IS NULL OR is_archived = 0)");
$stmt->execute([$clientUserId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User no longer exists – force logout
    session_destroy();
    echo json_encode(['success' => false, 'action' => 'logout', 'message' => 'User not found']);
    exit;
}

// Re-set PHP session to the correct user for this tab's context
$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['role']    = $user['role'] ?? 'user';
$_SESSION['avatar']  = $user['avatar'] ?? null;

echo json_encode([
    'success' => true,
    'user' => [
        'id'     => $user['id'],
        'name'   => $user['name'],
        'email'  => $user['email'],
        'avatar' => $user['avatar'] ?? null,
        'role'   => $user['role'] ?? 'user',
    ]
]);
