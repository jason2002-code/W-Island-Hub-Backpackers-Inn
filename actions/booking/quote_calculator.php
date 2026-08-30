<?php
/**
 * Quote Calculator Module
 * Calculates nights, room pricing, 30% deposits, and evaluates the 24-hour check-in rule.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/availability.php';

if (!function_exists('calculateQuote')) {
function calculateQuote($data = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    if (!$data) {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST ?? $_GET;
    }

    $roomId = intval($data['room_id'] ?? 0);
    $checkIn = trim($data['check_in'] ?? $data['check_in_date'] ?? '');
    $checkOut = trim($data['check_out'] ?? $data['check_out_date'] ?? '');
    $guests = intval($data['guests'] ?? 1);

    if (!$roomId || empty($checkIn) || empty($checkOut)) {
        echo json_encode(['success' => false, 'message' => 'Room, check-in, and check-out dates are required']);
        return;
    }

    $checkInTime = strtotime($checkIn);
    $checkOutTime = strtotime($checkOut);
    $todayMidnight = strtotime(date('Y-m-d'));

    if (!$checkInTime || !$checkOutTime) {
        echo json_encode(['success' => false, 'message' => 'Invalid dates provided']);
        return;
    }

    if ($checkInTime < $todayMidnight) {
        echo json_encode(['success' => false, 'message' => 'Check-in date cannot be in the past']);
        return;
    }

    if ($checkOutTime <= $checkInTime) {
        echo json_encode(['success' => false, 'message' => 'Check-out date must be after check-in date']);
        return;
    }

    $diffDays = ($checkOutTime - $checkInTime) / 86400;
    $nights = max(1, (int)round($diffDays));

    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Selected room was not found']);
            return;
        }

        $pricePerNight = floatval($room['price']);
        $totalAmount = round($pricePerNight * $nights, 2);
        
        // 30% Downpayment calculation
        $depositAmount = round($totalAmount * 0.30, 2);
        $depositBalanceDue = round($totalAmount - $depositAmount, 2);

        // Check 24-Hour Rule:
        // Check-in date standard time is assumed 2:00 PM (14:00)
        // If (check_in_date_time - current_time) <= 86400 seconds (24 hours), Option C is unlocked.
        $checkInDateTime = strtotime($checkIn . ' 14:00:00');
        $currentTime = time();
        $isWithin24h = ($checkInDateTime - $currentTime) <= 86400;

        // Check availability
        $availability = checkRoomAvailability($pdo, $roomId, $checkIn, $checkOut);

        echo json_encode([
            'success' => true,
            'is_available' => $availability['is_available'],
            'conflict_reason' => $availability['message'] ?? '',
            'is_within_24h' => $isWithin24h,
            'quote' => [
                'room_id' => $room['id'],
                'room_name' => $room['name'],
                'room_type' => $room['type'],
                'price_per_night' => $pricePerNight,
                'nights' => $nights,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_amount' => $totalAmount,
                'deposit_amount' => $depositAmount,
                'deposit_balance_due' => $depositBalanceDue,
                'payment_options' => [
                    'option_a' => [
                        'name' => 'Full Payment (GCash)',
                        'description' => 'Pay 100% upfront via GCash. Held for 2 hours for verification.',
                        'amount_due_now' => $totalAmount,
                        'balance_at_hotel' => 0.00,
                        'is_available' => true
                    ],
                    'option_b' => [
                        'name' => 'Partial Downpayment (30% GCash)',
                        'description' => 'Pay 30% now via GCash. Balance collected at front desk upon check-in.',
                        'amount_due_now' => $depositAmount,
                        'balance_at_hotel' => $depositBalanceDue,
                        'is_available' => true
                    ],
                    'option_c' => [
                        'name' => 'Pay at Hotel (Strict 24h)',
                        'description' => 'Reserve now and pay 100% upon arrival. Held strictly until Expected Arrival + 1 hour.',
                        'amount_due_now' => 0.00,
                        'balance_at_hotel' => $totalAmount,
                        'is_available' => $isWithin24h,
                        'restriction_notice' => $isWithin24h ? '' : 'Only available for reservations checking in within 24 hours.'
                    ]
                ]
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Quote calculation error: ' . $e->getMessage()]);
    }
}
}

