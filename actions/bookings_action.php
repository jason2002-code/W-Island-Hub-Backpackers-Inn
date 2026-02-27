<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    getBookings();
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
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

function getBookings() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
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
        
        // Format for compatibility
        $formattedBookings = array_map(function($b) {
            return [
                'id' => $b['id'],
                'ref_id' => $b['ref_id'],
                'guestName' => $b['guest_name'],
                'roomName' => $b['room_name'],
                'checkIn' => $b['check_in'],
                'checkOut' => $b['check_out'],
                'total' => $b['total_price'],
                'status' => $b['status'],
                'created_at' => $b['created_at']
            ];
        }, $bookings);
        
        echo json_encode([
            'success' => true, 
            'bookings' => $formattedBookings
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateBookingStatus($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $bookingId = $data['id'] ?? null;
    $status = $data['status'] ?? null;
    
    if (!$bookingId || !$status) {
        echo json_encode(['success' => false, 'message' => 'Booking ID and status are required']);
        exit;
    }
    
    // Validate status
    $allowedStatuses = ['Pending', 'Confirmed', 'Cancelled'];
    if (!in_array($status, $allowedStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $bookingId]);
        
        echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createBooking($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $userId = $data['user_id'] ?? null;
    $roomId = $data['room_id'] ?? null;
    $checkIn = $data['checkIn'] ?? null;
    $checkOut = $data['checkOut'] ?? null;
    $total = $data['total'] ?? 0;
    
    if (!$userId || !$roomId || !$checkIn || !$checkOut) {
        echo json_encode(['success' => false, 'message' => 'Missing required booking details']);
        exit;
    }

    try {
        $refId = 'BK-' . strtoupper(substr(uniqid(), -6));
        
        $stmt = $pdo->prepare("INSERT INTO bookings (ref_id, user_id, room_id, check_in, check_out, total_price, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())");
        $stmt->execute([$refId, $userId, $roomId, $checkIn, $checkOut, $total]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Booking created successfully',
            'ref_id' => $refId
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteBooking() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $bookingId = $_GET['id'] ?? null;
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        
        echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
