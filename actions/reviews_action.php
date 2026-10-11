<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$rawInput = file_get_contents('php://input');
$jsonInput = !empty($rawInput) ? json_decode($rawInput, true) : [];
if (!is_array($jsonInput)) {
    $jsonInput = [];
}
$requestData = array_merge($_POST, $jsonInput);

// Handle requests
$action = $_GET['action'] ?? ($requestData['action'] ?? '');
$room_id = $_GET['room_id'] ?? ($requestData['room_id'] ?? null);

switch ($action) {
    case 'get_reviews':
        getReviews($room_id);
        break;
    case 'submit_review':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            submitReview($requestData);
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
        $where = "WHERE r.status = 'approved' AND r.visible = 1";
        $params = [];
        if ($room_id) {
            $where .= " AND r.room_id = ?";
            $params[] = $room_id;
        }

        // Get average rating and count
        $sql = "SELECT AVG(r.rating) as avg_rating, COUNT(*) as total FROM reviews r " . $where;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        $avgRating = round(floatval($stats['avg_rating'] ?? 0), 1);
        $totalReviews = intval($stats['total'] ?? 0);

        // Get latest approved reviews
        $limitSql = "SELECT r.*, COALESCE(u.name, 'Anonymous') as user_name, COALESCE(rm.name, 'Island Stay') as room_name, DATE_FORMAT(r.created_at, '%b %d, %Y') as formatted_date 
                     FROM reviews r 
                     LEFT JOIN users u ON r.user_id = u.id 
                     LEFT JOIN rooms rm ON r.room_id = rm.id 
                     " . $where . " 
                     ORDER BY r.created_at DESC 
                     LIMIT 15";
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

function submitReview($input = null) {
    if ($input === null) {
        $rawInput = file_get_contents('php://input');
        $jsonInput = !empty($rawInput) ? json_decode($rawInput, true) : [];
        if (!is_array($jsonInput)) {
            $jsonInput = [];
        }
        $input = array_merge($_POST, $jsonInput);
    }
    
    $userId = !empty($input['user_id']) ? intval($input['user_id']) : ($_SESSION['user_id'] ?? null);
    if (!$userId && !empty($_SESSION['user_id'])) {
        $userId = intval($_SESSION['user_id']);
    }

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Please log in to submit a review.']);
        return;
    }

    $roomId = intval($input['room_id'] ?? 0);
    $rating = floatval($input['rating'] ?? 0);
    $comment = trim($input['comment'] ?? '');

    if (!$roomId) {
        echo json_encode(['success' => false, 'message' => 'Please select a room to review.']);
        return;
    }
    if ($rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Please select a rating between 1 and 5 stars.']);
        return;
    }
    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Please enter your review comments.']);
        return;
    }

    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed. Please try again later.']);
        return;
    }

    // Get user's email to cross-reference bookings
    $userEmail = '';
    try {
        $uStmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $uStmt->execute([$userId]);
        $uRow = $uStmt->fetch(PDO::FETCH_ASSOC);
        if ($uRow && !empty($uRow['email'])) {
            $userEmail = trim($uRow['email']);
        }
    } catch (PDOException $e) {
        // Continue if query fails
    }

    // Check if the user has any valid bookings for this room
    $bookingSql = "
        SELECT id, status, booking_status,
               COALESCE(check_out_date, check_out) as end_date,
               COALESCE(check_in_date, check_in) as start_date
        FROM bookings
        WHERE (user_id = ? OR (? != '' AND guest_email = ?))
          AND room_id = ?
          AND status != 'Cancelled'
          AND booking_status NOT IN ('cancelled_expired', 'no_show')
        ORDER BY COALESCE(check_out_date, check_out) DESC
    ";
    $bookingStmt = $pdo->prepare($bookingSql);
    $bookingStmt->execute([$userId, $userEmail, $userEmail, $roomId]);
    $userBookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($userBookings)) {
        echo json_encode([
            'success' => false,
            'message' => 'You can only review rooms that you have booked and stayed in.'
        ]);
        return;
    }

    // Check if at least one stay is truly completed:
    // - booking_status is explicitly 'completed', OR
    // - checkout date is strictly BEFORE today (guest has fully departed, not just today)
    $today = date('Y-m-d');
    $hasCompletedStay = false;
    foreach ($userBookings as $b) {
        $bEnd    = $b['end_date'];
        $bStatus = strtolower($b['booking_status'] ?? '');
        $status  = strtolower($b['status'] ?? '');

        // Require checkout to be strictly before today — guests cannot review
        // a room on their checkout day (they may still be physically in the room).
        $stayActuallyOver = !empty($bEnd) && $bEnd < $today;

        if ($bStatus === 'completed' && $stayActuallyOver) {
            // Explicitly marked completed AND already checked out
            $hasCompletedStay = true;
            break;
        }

        if (($status === 'confirmed' || $bStatus === 'confirmed') && $stayActuallyOver) {
            // Confirmed booking whose checkout date has passed
            $hasCompletedStay = true;
            break;
        }
    }

    if (!$hasCompletedStay) {
        echo json_encode([
            'success' => false,
            'message' => 'You can only review this room after your stay has ended. Thank you for your patience!'
        ]);
        return;
    }

    try {
        // Spam prevention: Check if user has reviewed this room within last 7 days
        $checkSql = "SELECT id FROM reviews WHERE user_id = ? AND room_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$userId, $roomId]);
        if ($checkStmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'You have already submitted a review for this room recently. Thank you for your feedback!'
            ]);
            return;
        }

        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, room_id, rating, comment, status, visible) 
            VALUES (?, ?, ?, ?, 'approved', 1)
        ");
        $success = $stmt->execute([$userId, $roomId, $rating, $comment]);

        if ($success) {
            $insertId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true, 
                'message' => 'Thank you for your review! It is now live.',
                'id' => $insertId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review. Please try again.']);
        }
    } catch (PDOException $e) {
        error_log("Review submit ERROR for user {$userId}, room {$roomId}: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred while saving your review.']);
    }
}
?>
