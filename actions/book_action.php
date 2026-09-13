<?php
/**
 * Book Action - Handles direct booking requests
 * This is a supplementary file - main booking logic is in bookings_action.php
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/cron_expiry_action.php';

/**
 * Create a direct booking (alternative endpoint)
 */
function createDirectBooking($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $userId = !empty($data['user_id']) ? intval($data['user_id']) : null;
    $roomId = $data['room_id'] ?? null;
    $checkIn = $data['check_in'] ?? $data['checkIn'] ?? null;
    $checkOut = $data['check_out'] ?? $data['checkOut'] ?? null;
    $guestName = $data['guest_name'] ?? $data['guestName'] ?? 'Guest';
    $paymentMethod = $data['payment_method'] ?? $data['paymentMethod'] ?? 'Pay at Hotel';
    
    if (!$roomId || !$checkIn || !$checkOut || empty($guestName)) {
        echo json_encode(['success' => false, 'message' => 'Missing required booking details (Room, Dates, or Guest Name)']);
        exit;
    }

    $today = date('Y-m-d');
    if ($checkIn < $today) {
        echo json_encode(['success' => false, 'message' => 'Check-in date cannot be in the past or outdated.']);
        exit;
    }
    if ($checkOut <= $checkIn) {
        echo json_encode(['success' => false, 'message' => 'Check-out date must be strictly after check-in date.']);
        exit;
    }

    try {
        // Check for overlapping active bookings
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE room_id = ? 
            AND status IN ('Pending', 'Confirmed')
            AND booking_status NOT IN ('cancelled_expired', 'no_show', 'completed')
            AND COALESCE(check_in_date, check_in) < ?
            AND COALESCE(check_out_date, check_out) > ?
        ");
        $stmt->execute([$roomId, $checkOut, $checkIn]);
        if ((int)$stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Room is already booked on the selected dates. Please choose another date or room.']);
            exit;
        }

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

    $today = date('Y-m-d');
    if ($checkIn < $today) {
        echo json_encode([
            'success' => false,
            'available' => false,
            'is_booked' => false,
            'message' => 'Check-in date cannot be in the past or outdated.'
        ]);
        exit;
    }

    if ($checkOut <= $checkIn) {
        echo json_encode([
            'success' => false,
            'available' => false,
            'is_booked' => false,
            'message' => 'Check-out date must be strictly after check-in date.'
        ]);
        exit;
    }
    
    try {
        // Check for overlapping active bookings (excluding cancelled_expired, no_show, and completed)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM bookings 
            WHERE room_id = ? 
            AND status IN ('Pending', 'Confirmed')
            AND booking_status NOT IN ('cancelled_expired', 'no_show', 'completed')
            AND COALESCE(check_in_date, check_in) < ?
            AND COALESCE(check_out_date, check_out) > ?
        ");
        $stmt->execute([$roomId, $checkOut, $checkIn]);
        $overlapCount = (int)$stmt->fetchColumn();
        
        // Get room details
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            echo json_encode([
                'success' => false,
                'available' => false,
                'is_booked' => false,
                'message' => 'Room not found'
            ]);
            exit;
        }
        
        $requestedGuests = max(1, intval($data['guests'] ?? 1));
        $roomLimit = (int)($room['max_capacity'] ?? $room['capacity'] ?? 1);
        
        $isBooked = ($overlapCount > 0);
        $isMaintenance = ($room['status'] === 'Maintenance');
        $isCapacityMatch = ($roomLimit >= $requestedGuests);

        // A room is available ONLY IF it is not booked, not in maintenance, and fits requested guests
        $isAvailable = (!$isBooked) && (!$isMaintenance) && $isCapacityMatch;
        
        if ($isMaintenance) {
            $message = 'Room is under maintenance';
        } elseif ($isBooked) {
            $message = 'Room is already booked for selected dates';
        } elseif (!$isCapacityMatch) {
            $message = 'Room capacity is insufficient for selected guests';
        } else {
            $message = 'Room is available for selected dates';
        }

        echo json_encode([
            'success' => true,
            'available' => $isAvailable,
            'is_booked' => $isBooked,
            'room' => $room,
            'overlaps' => $overlapCount,
            'remaining_capacity' => $isBooked ? 0 : $roomLimit,
            'message' => $message
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Get detailed room availability with occupied date ranges
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

    // Optional date-range filtering
    $checkIn = $data['check_in'] ?? null;
    $checkOut = $data['check_out'] ?? null;

    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
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

        $occupiedRanges = [];
        $nextVacant = null;

        // If dates provided, check overlap with those exact dates.
        if (!empty($checkIn) && !empty($checkOut)) {
            $today = date('Y-m-d');
            if ($checkIn < $today) {
                echo json_encode([
                    'success' => false,
                    'room' => $room ?? ['name' => 'Room ' . $roomId],
                    'occupied_ranges' => [],
                    'next_vacant' => null,
                    'is_currently_available' => false,
                    'available' => false,
                    'message' => 'Check-in date cannot be in the past or outdated.'
                ]);
                return;
            }
            if ($checkOut <= $checkIn) {
                echo json_encode([
                    'success' => false,
                    'room' => $room ?? ['name' => 'Room ' . $roomId],
                    'occupied_ranges' => [],
                    'next_vacant' => null,
                    'is_currently_available' => false,
                    'available' => false,
                    'message' => 'Check-out date must be strictly after check-in date.'
                ]);
                return;
            }

            // Overlap condition: existing.check_in < desired.check_out AND existing.check_out > desired.check_in
            $stmt = $pdo->prepare("
                SELECT ref_id, guest_name, COALESCE(check_in_date, check_in) as check_in, COALESCE(check_out_date, check_out) as check_out, status, created_at
                FROM bookings
                WHERE room_id = ?
                  AND status IN ('Pending', 'Confirmed')
                  AND booking_status NOT IN ('cancelled_expired', 'no_show')
                  AND COALESCE(check_in_date, check_in) < ?
                  AND COALESCE(check_out_date, check_out) > ?
                ORDER BY check_in ASC
            ");
            $stmt->execute([$roomId, $checkOut, $checkIn]);
            $overlaps = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($overlaps as $booking) {
                $occupiedRanges[] = [
                    'ref' => $booking['ref_id'],
                    'guest' => $booking['guest_name'],
                    'from' => $booking['check_in'],
                    'to' => $booking['check_out']
                ];
            }

            $isAvailable = empty($overlaps);

            if (!$isAvailable) {
                // Find next vacant date after latest overlapping booking
                $lastCheckout = end($overlaps)['check_out'];
                $nextVacant = (new DateTime($lastCheckout))->modify('+1 day')->format('Y-m-d');
            }

            echo json_encode([
                'success' => true,
                'room' => $room ?? ['name' => 'Room ' . $roomId],
                'occupied_ranges' => $occupiedRanges,
                'next_vacant' => $nextVacant,
                'is_currently_available' => $isAvailable,
                'message' => $isAvailable ? 'Room available for selected dates' : 'Room not available - already booked on overlapping dates',
                'available' => $isAvailable
            ]);
            return;
        }

        // Default behavior: show current/next availability from today.
        $today = date('Y-m-d');

        $stmt = $pdo->prepare("
            SELECT ref_id, guest_name, COALESCE(check_in_date, check_in) as check_in, COALESCE(check_out_date, check_out) as check_out, status, created_at 
            FROM bookings 
            WHERE room_id = ? 
              AND status IN ('Pending', 'Confirmed') 
              AND booking_status NOT IN ('cancelled_expired', 'no_show')
              AND COALESCE(check_out_date, check_out) > ?
            ORDER BY check_in ASC
        ");
        $stmt->execute([$roomId, $today]);
        $upcoming = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
            $lastCheckout = end($upcoming)['check_out'];
            $nextVacantDate = (new DateTime($lastCheckout))->modify('+1 day')->format('Y-m-d');
            $nextVacant = $nextVacantDate;
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

// ----------------------------------------------------
// MAIN REQUEST DISPATCHER (Invoked on direct API call)
// ----------------------------------------------------
if (isset($_SERVER['REQUEST_METHOD'])) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }

    // Automatically clean up expired booking holds
    runExpiryEngine();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $action = $input['action'] ?? 'create';

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
}
