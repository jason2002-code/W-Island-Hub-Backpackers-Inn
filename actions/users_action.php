<?php
/**
 * USER MANAGEMENT API
 * -------------------
 * Users are never hard-deleted from the Active list. Deleting now
 * archives the account (soft delete) so that bookings, reports and
 * reviews stay intact. Archived accounts live in the Archive view,
 * where an admin can restore them or permanently delete them.
 */
require_once '../config/db.php';

header('Content-Type: application/json');

define('PROTECTED_ROLE', 'admin');
define('AVATAR_DIRECTORY', 'assets/image/avatars/');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getUsers();
        break;
    case 'POST':
    case 'PUT':
        handleUserAction();
        break;
    case 'DELETE':
        permanentlyDeleteUser();
        break;
    default:
        respond(false, 'Invalid request method');
}

/**
 * Send a JSON response and stop.
 */
function respond($success, $message, array $extra = []) {
    echo json_encode(array_merge(
        ['success' => (bool) $success, 'message' => $message],
        $extra
    ));
    exit;
}

/**
 * Get the PDO handle or fail the request.
 * @return PDO
 */
function requireDb() {
    $pdo = getDB();
    if (!$pdo) {
        respond(false, 'Database connection failed');
    }
    return $pdo;
}

/**
 * Read the request payload from JSON body or regular POST fields.
 * @return array
 */
function getRequestInput() {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        return $decoded;
    }
    return array_merge($_GET, $_POST);
}

/**
 * ID of the admin currently using the panel (0 when unavailable).
 * @return int
 */
function currentAdminId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return (int) ($_SESSION['admin_id'] ?? 0);
}

/**
 * GET: list users for the requested view.
 */
function getUsers() {
    $pdo = requireDb();

    $view = ($_GET['view'] ?? 'active') === 'archived' ? 'archived' : 'active';
    $isArchived = $view === 'archived' ? 1 : 0;

    $orderBy = $view === 'archived'
        ? 'u.archived_at DESC, u.created_at DESC'
        : 'u.created_at DESC';

    $sql = "SELECT u.id, u.name, u.email, u.role, u.avatar, u.phone, u.created_at,
                   u.is_archived, u.archived_at,
                   (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS bookings_count
            FROM users u
            WHERE u.is_archived = ?
            ORDER BY {$orderBy}";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$isArchived]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $index => $user) {
            $users[$index] = decorateUser($user);
        }

        respond(true, 'Users loaded', [
            'view' => $view,
            'users' => $users,
            'counts' => getUserCounts($pdo)
        ]);
    } catch (PDOException $e) {
        respond(false, 'Database error: ' . $e->getMessage());
    }
}

/**
 * Casts types and flags accounts that must not be archived or deleted.
 */
function decorateUser(array $user) {
    $user['id'] = (int) $user['id'];
    $user['is_archived'] = (int) $user['is_archived'];
    $user['bookings_count'] = (int) $user['bookings_count'];
    $user['is_protected'] = ($user['role'] === PROTECTED_ROLE)
        || ((int) $user['id'] === currentAdminId());
    return $user;
}

/**
 * Active / archived totals for the tab badges.
 */
function getUserCounts(PDO $pdo) {
    $stmt = $pdo->query(
        "SELECT COALESCE(SUM(is_archived = 0), 0) AS active,
                COALESCE(SUM(is_archived = 1), 0) AS archived
         FROM users"
    );
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'active' => (int) $row['active'],
        'archived' => (int) $row['archived']
    ];
}
/**
 * POST/PUT: archive or restore a user account.
 */
function handleUserAction() {
    $pdo = requireDb();
    $input = getRequestInput();

    $action = strtolower(trim($input['action'] ?? ''));
    $userId = (int) ($input['id'] ?? $input['user_id'] ?? 0);

    if ($userId <= 0) {
        respond(false, 'User ID is required');
    }

    if (!in_array($action, ['archive', 'restore'], true)) {
        respond(false, 'Unsupported action. Use "archive" or "restore".');
    }

    $user = findUser($pdo, $userId);

    if (!$user) {
        respond(false, 'User not found');
    }

    if (isProtectedUser($user)) {
        respond(false, 'This account is protected and cannot be archived.');
    }

    $isArchived = (int) $user['is_archived'];

    if ($action === 'archive' && $isArchived === 1) {
        respond(false, 'This user is already archived.');
    }

    if ($action === 'restore' && $isArchived === 0) {
        respond(false, 'This user is not archived.');
    }

    try {
        if ($action === 'archive') {
            $stmt = $pdo->prepare(
                "UPDATE users SET is_archived = 1, archived_at = NOW() WHERE id = ?"
            );
        } else {
            $stmt = $pdo->prepare(
                "UPDATE users SET is_archived = 0, archived_at = NULL WHERE id = ?"
            );
        }
        $stmt->execute([$userId]);

        $message = $action === 'archive'
            ? 'User archived successfully. You can restore them from the Archive tab.'
            : 'User restored successfully.';

        respond(true, $message, [
            'action' => $action,
            'user_id' => $userId,
            'counts' => getUserCounts($pdo)
        ]);
    } catch (PDOException $e) {
        respond(false, 'Database error: ' . $e->getMessage());
    }
}

/**
 * DELETE: permanently remove an archived user.
 * Only archived accounts can be deleted, so an active account can
 * never be destroyed by a single accidental click.
 */
function permanentlyDeleteUser() {
    $pdo = requireDb();

    $userId = (int) ($_GET['id'] ?? $_GET['user_id'] ?? 0);

    if ($userId <= 0) {
        respond(false, 'User ID is required');
    }

    $user = findUser($pdo, $userId);

    if (!$user) {
        respond(false, 'User not found');
    }

    if (isProtectedUser($user)) {
        respond(false, 'This account is protected and cannot be deleted.');
    }

    if ((int) $user['is_archived'] !== 1) {
        respond(false, 'Only archived users can be permanently deleted. Archive this user first.');
    }

    try {
        // bookings, reports and reviews reference users with ON DELETE SET NULL,
        // so their records survive and simply lose the link to this account.
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        removeAvatarFile($user['avatar'] ?? null);

        respond(true, 'User permanently deleted.', [
            'user_id' => $userId,
            'counts' => getUserCounts($pdo)
        ]);
    } catch (PDOException $e) {
        respond(false, 'Database error: ' . $e->getMessage());
    }
}

/**
 * Fetch a single user row.
 * @return array|null
 */
function findUser(PDO $pdo, $userId) {
    $stmt = $pdo->prepare(
        "SELECT id, name, email, role, avatar, is_archived FROM users WHERE id = ? LIMIT 1"
    );
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return $user ?: null;
}

/**
 * Admin accounts and the signed-in admin are never archivable or deletable.
 */
function isProtectedUser(array $user) {
    return ($user['role'] === PROTECTED_ROLE)
        || ((int) $user['id'] === currentAdminId());
}

/**
 * Delete a user's avatar file, but only from inside the avatar directory.
 */
function removeAvatarFile($avatarPath) {
    if (empty($avatarPath) || !is_string($avatarPath)) {
        return;
    }

    $normalized = str_replace('\\', '/', $avatarPath);

    if (strpos($normalized, AVATAR_DIRECTORY) !== 0) {
        return;
    }

    $avatarRoot = realpath(__DIR__ . '/../' . rtrim(AVATAR_DIRECTORY, '/'));
    if ($avatarRoot === false) {
        return;
    }

    $relativeName = substr($normalized, strlen(AVATAR_DIRECTORY));
    $targetPath = realpath($avatarRoot . DIRECTORY_SEPARATOR . $relativeName);

    if ($targetPath === false || !is_file($targetPath)) {
        return;
    }

    // Guard against directory traversal outside the avatar folder.
    if (strpos($targetPath, $avatarRoot) !== 0) {
        return;
    }

    @unlink($targetPath);
}
