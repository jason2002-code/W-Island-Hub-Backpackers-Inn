<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle requests
$action = $_GET['action'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($action)) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}
$room_id = $_GET['room_id'] ?? null;

switch ($action) {
    case 'get_reviews':
        getReviews($room_id);
        break;
    case 'submit_review':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            submitReview();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        }
        break;
    case 'get_recent_reviews':
        getReviews(null);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getReviews($room_id = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    try {
        // Build WHERE clause
$where = "WHERE status = 'approved' AND visible = 1";
        $params = [];
        if ($room_id) {
            $where .= " AND room_id = ?";
            $params[] = $room_id;
        }

        // Get average rating and count
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews " . $where;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $avgRating = round(floatval($stats['avg_rating'] ?? 0), 1);
        $totalReviews = intval($stats['total'] ?? 0);

        // Get latest approved reviews
        $limitSql = "SELECT r.*, COALESCE(u.name, 'Anonymous') as user_name, DATE_FORMAT(r.created_at, '%b %d, %Y') as formatted_date 
                     FROM reviews r 
                     LEFT JOIN users u ON r.user_id = u.id 
                     " . $where . " 
                     ORDER BY r.created_at DESC 
                     LIMIT 6";
        $stmt = $pdo->prepare($limitSql);
        $stmt->execute($params);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'avg_rating' => $avgRating,
            'total_reviews' => $totalReviews,
            'reviews' => $reviews
        ]);
    } catch (PDOException $e) {
        error_log("Database error in getReviews: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred. Please try again.']);
    }
}

function submitReview() {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    error_log("DEBUG submitReview input: " . print_r($input, true));
    error_log("DEBUG SESSION user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Please login to submit a review']);
        return;
    }
    $userId = $_SESSION['user_id'];
    $rating = floatval($input['rating'] ?? 0);
    $comment = trim($input['comment'] ?? '');
    $roomId = intval($input['room_id'] ?? 0);
    
    error_log("DEBUG Parsed: userId=$userId, roomId=$roomId, rating=$rating, comment_len=" . strlen($comment));
    
    if (!$roomId) {
        echo json_encode(['success' => false, 'message' => 'Room ID required']);
        return;
    }
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
        return;
    }
    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Comment is required']);
        return;
    }

    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    // Check if the user has a past confirmed booking for this room
    $bookingCheckSql = "SELECT COUNT(*) FROM bookings WHERE user_id = ? AND room_id = ? AND status = 'Confirmed' AND check_out <= CURDATE()";
    $bookingCheckStmt = $pdo->prepare($bookingCheckSql);
    $bookingCheckStmt->execute([$userId, $roomId]);
    $hasPastBooking = $bookingCheckStmt->fetchColumn();

    if ($hasPastBooking == 0) {
        echo json_encode(['success' => false, 'message' => 'You can only review rooms after your stay has ended.']);
        return;
    }

    try {
        // Check spam
        $checkSql = "SELECT id FROM reviews WHERE user_id = ? AND room_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$userId, $roomId]);
        $spamCount = $checkStmt->rowCount();
        error_log("DEBUG Spam check: $spamCount existing reviews");
        if ($spamCount > 0) {
            echo json_encode(['success' => false, 'message' => 'You can only submit one review per room per week']);
            return;
        }

        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, room_id, rating, comment, status, visible) 
            VALUES (?, ?, ?, ?, 'approved', 1)
        ");
        $success = $stmt->execute([$userId, $roomId, $rating, $comment]);
        error_log("DEBUG INSERT success: " . ($success ? 'YES' : 'NO'));

        if ($success) {
            $insertId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true, 
                'message' => 'Thank you for your review! It is now live.',
                'id' => $insertId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review - check logs']);
        }
    } catch (PDOException $e) {
        error_log("Review submit ERROR for user {$userId}, room {$roomId}: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
