<?php
/**
 * BOOKINGS API ACTION
 * Handles CRUD for bookings + dashboard counts.
 */
require_once '../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'count') {
        getBookingCounts();
    } else {
        getBookings();
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['action']) && $input['action'] === 'create') {
        createBooking($input);
    } else {
        updateBookingStatus($input);
    }
} elseif ($method === 'DELETE') {
    deleteBooking();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Gets all bookings with user/room details for admin list
function getBookings() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, r.name as room_name, r.price as room_price, u.name as guest_name, u.email as guest_email 
            FROM bookings b 
            LEFT JOIN rooms r ON b.room_id = r.id 
            LEFT JOIN users u ON b.user_id = u.id 
            ORDER BY b.created_at DESC
        ");
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $formatted = array_map(function($b) {
            return [
                'id' => $b['id'],
                'ref_id' => $b['ref_id'],
                'guestName' => $b['guest_name'],
                'guestEmail' => $b['guest_email'] ?? '',
                'roomName' => $b['room_name'],
                'checkIn' => $b['check_in'],
                'checkOut' => $b['check_out'],
                'total' => $b['total_price'],
                'status' => $b['status'],
                'created_at' => $b['created_at']
            ];
        }, $bookings);
        echo json_encode(['success' => true, 'bookings' => $formatted]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
    }
}

// Updates booking status (Pending->Confirmed->Cancelled) with validation
function updateBookingStatus($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    $bookingId = $data['id'] ?? null;
    $status = $data['status'] ?? null;
    if (!$bookingId || !$status) {
        echo json_encode(['success' => false, 'message' => 'ID and status required']);
        exit;
    }
    $allowed = ['Pending', 'Confirmed', 'Cancelled'];
    if (!in_array($status, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $bookingId]);
        echo json_encode(['success' => true, 'message' => 'Status updated']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error']);
    }
}

// Creates new booking reservation with validation and ref ID
function createBooking($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    $userId = $data['user_id'] ?? null;
    $roomId = $data['room_id'] ?? null;
    $checkIn = $data['check_in'] ?? $data['checkIn'] ?? null;
    $checkOut = $data['check_out'] ?? $data['checkOut'] ?? null;
    $total = $data['total'] ?? 0;
    $nights = $data['nights'] ?? 1;
    $guestName = $data['guest_name'] ?? $data['guestName'] ?? 'Guest';
    $paymentMethod = $data['payment_method'] ?? $data['paymentMethod'] ?? 'Pay at Hotel';
    if (!$userId || !$roomId || !$checkIn || !$checkOut) {
        echo json_encode(['success' => false, 'message' => 'Missing details']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Admins cannot book']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit;
    }
    try {
        $refId = 'BK-' . strtoupper(substr(uniqid(), -6));
        $stmt = $pdo->prepare("INSERT INTO bookings (ref_id, user_id, room_id, guest_name, check_in, check_out, nights, total_price, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->execute([$refId, $userId, $roomId, $guestName, $checkIn, $checkOut, $nights, $total, $paymentMethod]);
        echo json_encode(['success' => true, 'message' => 'Booking created', 'ref_id' => $refId]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

// Deletes specific booking
function deleteBooking() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    $bookingId = $_GET['id'] ?? null;
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        echo json_encode(['success' => true, 'message' => 'Deleted']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error']);
    }
}

// Counts bookings by status for dashboard badges
function getBookingCounts() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM bookings GROUP BY status");
        $stmt->execute();
        $counts = ['total' => 0, 'pending' => 0, 'confirmed' => 0, 'cancelled' => 0];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status = strtolower($row['status']);
            $counts[$status] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }
        echo json_encode(['success' => true, 'counts' => $counts]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error']);
    }
}
?>
