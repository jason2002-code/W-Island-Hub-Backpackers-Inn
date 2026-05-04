<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Admin check
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

$action = $_GET['action'] ?? '';
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}

switch ($action) {
    case 'get_reviews':
        getAllReviews();
        break;
    case 'toggle_visible':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            toggleVisible();
        }
        break;
    case 'save_reply':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            saveReply();
        }
        break;
    case 'delete_review':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            deleteReview();
        }
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getAllReviews() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB error']);
        return;
    }

    try {
        $sql = "SELECT r.*, u.name as user_name, ro.name as room_name, DATE_FORMAT(r.created_at, '%b %d %Y') as date
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                LEFT JOIN rooms ro ON r.room_id = ro.id 
                ORDER BY r.created_at DESC";
        $stmt = $pdo->query($sql);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'reviews' => $reviews]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

function toggleVisible() {
    $input = json_decode(file_get_contents('php://input'), true);
    $reviewId = intval($input['review_id'] ?? 0);
    if (!$reviewId) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE reviews SET visible = NOT visible WHERE id = ?");
    $success = $stmt->execute([$reviewId]);
    echo json_encode(['success' => $success]);
}

function saveReply() {
    $input = json_decode(file_get_contents('php://input'), true);
    $reviewId = intval($input['review_id'] ?? 0);
    $reply = trim($input['reply'] ?? '');
    if (!$reviewId) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        return;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("UPDATE reviews SET admin_reply = ? WHERE id = ?");
    $success = $stmt->execute([$reply, $reviewId]);
    echo json_encode(['success' => $success]);
}

function deleteReview() {
    $input = json_decode(file_get_contents('php://input'), true);
    $reviewId = intval($input['review_id'] ?? 0);
    if (!$reviewId) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }

    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
    $success = $stmt->execute([$reviewId]);
    echo json_encode(['success' => $success, 'message' => $success ? 'Deleted successfully' : 'Failed to delete review']);
}
?>
