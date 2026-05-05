<?php
/**
 * Book Action - Handles direct booking requests
 * This is a supplementary file - main booking logic is in bookings_action.php
 */

require_once '../config/db.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'create';

// Route to appropriate function in bookings_action.php
// This file provides a cleaner API endpoint for direct bookings
switch ($action) {
    case 'create':
        createDirectBooking($input);
        break;
    case 'check_availability':
        checkAvailability($input);
        break;
    case 'get_room_availability':
        getRoomAvailability($input);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

/**
 * Create a direct booking (alternative endpoint)
 */
function createDirectBooking($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $userId = $data['user_id'] ?? null;
    $roomId = $data['room_id'] ?? null;
    $checkIn = $data['check_in'] ?? $data['checkIn'] ?? null;
    $checkOut = $data['check_out'] ?? $data['checkOut'] ?? null;
    $guestName = $data['guest_name'] ?? $data['guestName'] ?? null;
    $paymentMethod = $data['payment_method'] ?? $data['paymentMethod'] ?? 'Pay at Hotel';
    
    if (!$userId || !$roomId || !$checkIn || !$checkOut) {
        echo json_encode(['success' => false, 'message' => 'Missing required booking details']);
        exit;
    }

    try {
        // Get room price
        $stmt = $pdo->prepare("SELECT price FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Room not found']);
            exit;
        }
        
        // Calculate nights and total
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $nights = $checkOutDate->diff($checkInDate)->days;
        $totalPrice = $room['price'] * $nights;
        
        // Generate reference ID
$refId = 'BK-' . strtoupper(substr(uniqid(), -6));
        
        // Insert booking
        $stmt = $pdo->prepare("
            INSERT INTO bookings (ref_id, user_id, room_id, guest_name, check_in, check_out, nights, total_price, payment_method, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
        ");
        $stmt->execute([$refId, $userId, $roomId, $guestName, $checkIn, $checkOut, $nights, $totalPrice, $paymentMethod]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Booking created successfully',
            'booking' => [
                'ref_id' => $refId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $nights,
                'total' => $totalPrice
            ]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Check room availability for dates
 */
function checkAvailability($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $roomId = $data['room_id'] ?? null;
    $checkIn = $data['check_in'] ?? $data['checkIn'] ?? null;
    $checkOut = $data['check_out'] ?? $data['checkOut'] ?? null;
    
    if (!$roomId || !$checkIn || !$checkOut) {
        echo json_encode(['success' => false, 'message' => 'Room ID and dates are required']);
        exit;
    }
    
    try {
        // Check for overlapping confirmed bookings
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM bookings 
            WHERE room_id = ? 
            AND status IN ('Pending', 'Confirmed')
            AND (
                (check_in <= ? AND check_out > ?) 
                OR (check_in < ? AND check_out >= ?)
                OR (check_in >= ? AND check_out <= ?)
            )
        ");
        $stmt->execute([$roomId, $checkIn, $checkIn, $checkOut, $checkOut, $checkIn, $checkOut]);
        $overlapCount = $stmt->fetchColumn();
        
        // Get room details
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $isAvailable = $overlapCount == 0 && $room;
        
        if ($room && $room['status'] === 'Maintenance') {
            $isAvailable = false;
            $message = 'Room is under maintenance';
        } else {
            $message = $isAvailable ? 'Room is available for selected dates' : 'Room not available - already booked on overlapping dates';
        }

        echo json_encode([
            'success' => true,
            'available' => $isAvailable,
            'room' => $room,
            'overlaps' => $overlapCount,
            'message' => $message
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Get upcoming availability info for a specific room
 * Returns occupied periods and next vacant date
 */
function getRoomAvailability($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $roomId = $data['room_id'] ?? null;
    
    if (!$roomId) {
        echo json_encode(['success' => false, 'message' => 'Room ID required']);
        exit;
    }
    
    try {
        $today = date('Y-m-d');
        
        // Get upcoming bookings
        $stmt = $pdo->prepare("
            SELECT ref_id, guest_name, check_in, check_out, status, created_at 
            FROM bookings 
            WHERE room_id = ? AND status IN ('Pending', 'Confirmed') 
            AND check_out > ?
            ORDER BY check_in ASC
        ");
        $stmt->execute([$roomId, $today]);
        $upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $occupiedRanges = [];
        $nextVacant = null;
        $isOpenNow = true;
        
        foreach ($upcoming as $booking) {
            $occupiedRanges[] = [
                'ref' => $booking['ref_id'],
                'guest' => $booking['guest_name'],
                'from' => $booking['check_in'],
                'to' => $booking['check_out']
            ];
            
            if ($booking['check_in'] <= $today) {
                $isOpenNow = false;
            }
        }
        
        if (!empty($occupiedRanges)) {
            // Next vacant is after last checkout +1 day
            $lastCheckout = end($upcoming)['check_out'];
            $nextVacantDate = (new DateTime($lastCheckout))->modify('+1 day')->format('Y-m-d');
            $nextVacant = $nextVacantDate;
        }
        
        // Get room details
        $stmt = $pdo->prepare("SELECT name, status FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($room && $room['status'] === 'Maintenance') {
            echo json_encode([
                'success' => true,
                'room' => $room,
                'occupied_ranges' => [],
                'next_vacant' => null,
                'is_currently_available' => false,
                'message' => 'Room is currently under maintenance'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'room' => $room ?? ['name' => 'Room ' . $roomId],
            'occupied_ranges' => $occupiedRanges,
            'next_vacant' => $nextVacant,
            'is_currently_available' => $isOpenNow && empty($occupiedRanges),
            'message' => $isOpenNow && empty($occupiedRanges) ? 'Room available now' : 'Check occupied periods'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
