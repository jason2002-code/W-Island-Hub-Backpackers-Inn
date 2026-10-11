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
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$room) {
            echo json_encode(['success' => false, 'message' => 'Room not found']);
            exit;
        }

        $isDorm = ($room['type'] === 'Dorm' || (int)$roomId === 5);
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $nights = $checkOutDate->diff($checkInDate)->days;
        if ($nights < 1) $nights = 1;

        if ($isDorm) {
            $totalBeds = max(1, intval($room['max_capacity'] ?? $room['capacity'] ?? 8));
            $requestedBeds = max(1, min($totalBeds, intval($data['beds_count'] ?? $data['beds'] ?? $data['guests'] ?? 1)));
            $bedPrice = 460.00;

            $stmtDorm = $pdo->prepare("
                SELECT COALESCE(SUM(
                    CASE 
                        WHEN b.beds_count > 0 THEN b.beds_count
                        ELSE GREATEST(1, COALESCE(b.adults + b.children, b.adults, 1))
                    END
                ), 0) as occupied_beds
                FROM bookings b
                WHERE b.room_id = ?
                  AND b.status IN ('Pending', 'Confirmed')
                  AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
                  AND COALESCE(b.check_in_date, b.check_in) < ?
                  AND COALESCE(b.check_out_date, b.check_out) > ?
            ");
            $stmtDorm->execute([$roomId, $checkOut, $checkIn]);
            $occupiedBeds = (int)$stmtDorm->fetchColumn();
            $remainingBeds = max(0, $totalBeds - $occupiedBeds);

            if ($requestedBeds > $remainingBeds) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Only {$remainingBeds} bed(s) available in Room 5 for selected dates. You requested {$requestedBeds} bed(s)."
                ]);
                exit;
            }

            $totalPrice = $bedPrice * $requestedBeds * $nights;
            $refId = 'BK-' . strtoupper(substr(uniqid(), -6));
            
            $stmt = $pdo->prepare("
                INSERT INTO bookings (ref_id, user_id, room_id, guest_name, check_in, check_out, nights, beds_count, adults, total_price, total_amount, payment_method, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
            ");
            $stmt->execute([$refId, $userId, $roomId, $guestName, $checkIn, $checkOut, $nights, $requestedBeds, $requestedBeds, $totalPrice, $totalPrice, $paymentMethod]);

            echo json_encode([
                'success' => true, 
                'message' => 'Booking created successfully',
                'booking' => [
                    'ref_id' => $refId,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'nights' => $nights,
                    'beds_count' => $requestedBeds,
                    'total' => $totalPrice
                ]
            ]);
            exit;
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE room_id = ? 
                AND status IN ('Pending', 'Confirmed')
                AND booking_status NOT IN ('cancelled_expired', 'no_show')
                AND COALESCE(check_in_date, check_in) < ?
                AND COALESCE(check_out_date, check_out) > ?
            ");
            $stmt->execute([$roomId, $checkOut, $checkIn]);
            if ((int)$stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Room is already booked on the selected dates. Please choose another date or room.']);
                exit;
            }

            $guests = max(1, intval($data['guests'] ?? 1));
            $roomMaxCap = max(1, intval($room['max_capacity'] ?? $room['capacity'] ?? 2));
            if ($guests > $roomMaxCap) {
                echo json_encode([
                    'success' => false,
                    'message' => "The hotel does not accommodate {$guests} guests for this room. The room is limited to {$roomMaxCap} guests."
                ]);
                exit;
            }
            
            $totalPrice = $room['price'] * $nights;
            $refId = 'BK-' . strtoupper(substr(uniqid(), -6));
            
            $stmt = $pdo->prepare("
                INSERT INTO bookings (ref_id, user_id, room_id, guest_name, check_in, check_out, nights, total_price, total_amount, payment_method, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
            ");
            $stmt->execute([$refId, $userId, $roomId, $guestName, $checkIn, $checkOut, $nights, $totalPrice, $totalPrice, $paymentMethod]);
            
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
        }
        
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

        $isDorm = ($room['type'] === 'Dorm' || (int)$room['id'] === 5);
        $isMaintenance = ($room['status'] === 'Maintenance');
        $requestedGuests = max(1, intval($data['guests'] ?? 1));

        if ($isDorm) {
            $totalBeds = max(1, intval($room['max_capacity'] ?? $room['capacity'] ?? 8));
            $bedPrice = 460.00;

            $stmtDorm = $pdo->prepare("
                SELECT COALESCE(SUM(
                    CASE 
                        WHEN b.beds_count > 0 THEN b.beds_count
                        ELSE GREATEST(1, COALESCE(b.adults + b.children, b.adults, 1))
                    END
                ), 0) as occupied_beds
                FROM bookings b
                WHERE b.room_id = ?
                  AND b.status IN ('Pending', 'Confirmed')
                  AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
                  AND COALESCE(b.check_in_date, b.check_in) < ?
                  AND COALESCE(b.check_out_date, b.check_out) > ?
            ");
            $stmtDorm->execute([$roomId, $checkOut, $checkIn]);
            $occupiedBeds = (int)$stmtDorm->fetchColumn();
            $remainingBeds = max(0, $totalBeds - $occupiedBeds);

            $isBooked = ($remainingBeds <= 0);
            $isCapacityMatch = ($remainingBeds >= $requestedGuests);
            $isAvailable = (!$isMaintenance) && (!$isBooked);

            if ($isMaintenance) {
                $message = 'Room is under maintenance';
            } elseif ($isBooked) {
                $message = 'No beds available in Room 5 for selected dates';
            } elseif (!$isCapacityMatch) {
                $message = "Only {$remainingBeds} bed(s) available for selected dates (requested {$requestedGuests})";
            } else {
                $message = "Room 5 is available ({$remainingBeds} bed" . ($remainingBeds > 1 ? 's' : '') . " available)";
            }

            echo json_encode([
                'success' => true,
                'available' => $isAvailable,
                'is_booked' => $isBooked,
                'is_capacity_match' => $isCapacityMatch,
                'room' => $room,
                'is_dorm' => true,
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBeds,
                'remaining_beds' => $remainingBeds,
                'remaining_capacity' => $remainingBeds,
                'bed_price' => $bedPrice,
                'message' => $message
            ]);
            exit;
        }

        // Check for overlapping active bookings for standard room
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count FROM bookings 
            WHERE room_id = ? 
            AND status IN ('Pending', 'Confirmed')
            AND booking_status NOT IN ('cancelled_expired', 'no_show')
            AND COALESCE(check_in_date, check_in) < ?
            AND COALESCE(check_out_date, check_out) > ?
        ");
        $stmt->execute([$roomId, $checkOut, $checkIn]);
        $overlapCount = (int)$stmt->fetchColumn();
        
        $roomLimit = (int)($room['max_capacity'] ?? $room['capacity'] ?? 1);
        $isBooked = ($overlapCount > 0);
        $isCapacityMatch = ($roomLimit >= $requestedGuests);
        $isAvailable = (!$isBooked) && (!$isMaintenance);
        
        if ($isMaintenance) {
            $message = 'Room is under maintenance';
        } elseif ($isBooked) {
            $message = 'Room is already booked for selected dates';
        } elseif (!$isCapacityMatch) {
            $message = "Room accommodates up to {$roomLimit} guests (requested {$requestedGuests})";
        } else {
            $message = 'Room is available for selected dates';
        }

        echo json_encode([
            'success' => true,
            'available' => $isAvailable,
            'is_booked' => $isBooked,
            'is_capacity_match' => $isCapacityMatch,
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

            $isDorm = ($room['type'] === 'Dorm' || (int)$room['id'] === 5);
            $totalBeds = max(1, intval($room['max_capacity'] ?? $room['capacity'] ?? 8));
            $bedPrice = 460.00;

            if ($isDorm) {
                $stmtDorm = $pdo->prepare("
                    SELECT COALESCE(SUM(
                        CASE 
                            WHEN b.beds_count > 0 THEN b.beds_count
                            ELSE GREATEST(1, COALESCE(b.adults + b.children, b.adults, 1))
                        END
                    ), 0) as occupied_beds
                    FROM bookings b
                    WHERE b.room_id = ?
                      AND b.status IN ('Pending', 'Confirmed')
                      AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
                      AND COALESCE(b.check_in_date, b.check_in) < ?
                      AND COALESCE(b.check_out_date, b.check_out) > ?
                ");
                $stmtDorm->execute([$roomId, $checkOut, $checkIn]);
                $occupiedBeds = (int)$stmtDorm->fetchColumn();
                $remainingBeds = max(0, $totalBeds - $occupiedBeds);
                $isAvailable = ($remainingBeds > 0);

                if (!$isAvailable && !empty($overlaps)) {
                    $lastCheckout = end($overlaps)['check_out'];
                    $nextVacant = (new DateTime($lastCheckout))->modify('+1 day')->format('Y-m-d');
                }

                echo json_encode([
                    'success' => true,
                    'room' => $room ?? ['name' => 'Room ' . $roomId],
                    'occupied_ranges' => $occupiedRanges,
                    'next_vacant' => $isAvailable ? null : $nextVacant,
                    'is_currently_available' => $isAvailable,
                    'available' => $isAvailable,
                    'is_dorm' => true,
                    'total_beds' => $totalBeds,
                    'occupied_beds' => $occupiedBeds,
                    'remaining_beds' => $remainingBeds,
                    'bed_price' => $bedPrice,
                    'message' => $isAvailable ? "Room 5 available ({$remainingBeds} bed" . ($remainingBeds > 1 ? 's' : '') . " available)" : 'Room 5 has no available beds on overlapping dates'
                ]);
                return;
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

        $isDorm = ($room['type'] === 'Dorm' || (int)$room['id'] === 5);
        $totalBeds = max(1, intval($room['max_capacity'] ?? $room['capacity'] ?? 8));
        $bedPrice = 460.00;

        if ($isDorm) {
            $stmtDormToday = $pdo->prepare("
                SELECT COALESCE(SUM(
                    CASE 
                        WHEN b.beds_count > 0 THEN b.beds_count
                        ELSE GREATEST(1, COALESCE(b.adults + b.children, b.adults, 1))
                    END
                ), 0) as occupied_beds
                FROM bookings b
                WHERE b.room_id = ?
                  AND b.status IN ('Pending', 'Confirmed')
                  AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
                  AND COALESCE(b.check_in_date, b.check_in) <= ?
                  AND COALESCE(b.check_out_date, b.check_out) > ?
            ");
            $stmtDormToday->execute([$roomId, $today, $today]);
            $occupiedBedsToday = (int)$stmtDormToday->fetchColumn();
            $remainingBedsToday = max(0, $totalBeds - $occupiedBedsToday);
            $isOpenNow = ($remainingBedsToday > 0);

            echo json_encode([
                'success' => true,
                'room' => $room ?? ['name' => 'Room ' . $roomId],
                'occupied_ranges' => $occupiedRanges,
                'next_vacant' => $nextVacant,
                'is_currently_available' => $isOpenNow,
                'is_dorm' => true,
                'total_beds' => $totalBeds,
                'occupied_beds' => $occupiedBedsToday,
                'remaining_beds' => $remainingBedsToday,
                'bed_price' => $bedPrice,
                'message' => $isOpenNow ? "Room 5 available now ({$remainingBedsToday} bed" . ($remainingBedsToday > 1 ? 's' : '') . " available)" : 'No beds available in Room 5 today'
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

/**
 * Check total hotel capacity and available capacity for requested dates and guests
 */
function checkHotelCapacity($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $checkIn = $data['check_in'] ?? $data['checkIn'] ?? date('Y-m-d');
    $checkOut = $data['check_out'] ?? $data['checkOut'] ?? date('Y-m-d', strtotime('+1 day'));
    $guests = max(1, intval($data['guests'] ?? 1));

    $today = date('Y-m-d');
    if ($checkIn < $today) {
        $checkIn = $today;
    }
    if ($checkOut <= $checkIn) {
        $checkOut = date('Y-m-d', strtotime($checkIn . ' +1 day'));
    }

    try {
        // Fetch all rooms
        $stmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
        $allRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalHotelCapacity = 0;
        $totalAvailableCapacity = 0;
        $availableRooms = [];
        $bookedRooms = [];
        $maintenanceRooms = [];
        $maxSingleRoomCapacity = 0;

        foreach ($allRooms as $room) {
            $roomId = (int)$room['id'];
            $roomCap = max(1, intval($room['max_capacity'] ?? $room['capacity'] ?? 1));
            $isMaintenance = ($room['status'] === 'Maintenance');
            $isDorm = ($room['type'] === 'Dorm' || $roomId === 5);

            if (!$isMaintenance) {
                $totalHotelCapacity += $roomCap;
                if ($roomCap > $maxSingleRoomCapacity) {
                    $maxSingleRoomCapacity = $roomCap;
                }
            } else {
                $maintenanceRooms[] = $room;
                continue;
            }

            if ($isDorm) {
                $totalBeds = max(1, intval($room['max_capacity'] ?? $room['capacity'] ?? 8));
                $stmtDorm = $pdo->prepare("
                    SELECT COALESCE(SUM(
                        CASE 
                            WHEN b.beds_count > 0 THEN b.beds_count
                            ELSE GREATEST(1, COALESCE(b.adults + b.children, b.adults, 1))
                        END
                    ), 0) as occupied_beds
                    FROM bookings b
                    WHERE b.room_id = ?
                      AND b.status IN ('Pending', 'Confirmed')
                      AND b.booking_status NOT IN ('cancelled_expired', 'no_show')
                      AND COALESCE(b.check_in_date, b.check_in) < ?
                      AND COALESCE(b.check_out_date, b.check_out) > ?
                ");
                $stmtDorm->execute([$roomId, $checkOut, $checkIn]);
                $occupiedBeds = (int)$stmtDorm->fetchColumn();
                $remainingBeds = max(0, $totalBeds - $occupiedBeds);

                if ($remainingBeds <= 0) {
                    $bookedRooms[] = $room;
                } else {
                    $totalAvailableCapacity += $remainingBeds;
                    $availableRooms[] = [
                        'id' => $roomId,
                        'name' => $room['name'],
                        'type' => $room['type'],
                        'price' => (float)$room['price'],
                        'capacity' => $remainingBeds,
                        'max_capacity' => $totalBeds,
                        'remaining_beds' => $remainingBeds,
                        'total_beds' => $totalBeds,
                        'occupied_beds' => $occupiedBeds,
                        'bed_price' => 460.00,
                        'is_dorm' => true,
                        'status' => $room['status']
                    ];
                }
                continue;
            }

            // Check if standard room is booked between check_in and check_out
            $stmtOverlap = $pdo->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE room_id = ? 
                AND status IN ('Pending', 'Confirmed')
                AND booking_status NOT IN ('cancelled_expired', 'no_show')
                AND COALESCE(check_in_date, check_in) < ?
                AND COALESCE(check_out_date, check_out) > ?
            ");
            $stmtOverlap->execute([$roomId, $checkOut, $checkIn]);
            $isBooked = ((int)$stmtOverlap->fetchColumn() > 0);

            if ($isBooked) {
                $bookedRooms[] = $room;
            } else {
                $totalAvailableCapacity += $roomCap;
                $availableRooms[] = [
                    'id' => $roomId,
                    'name' => $room['name'],
                    'type' => $room['type'],
                    'price' => (float)$room['price'],
                    'capacity' => (int)($room['capacity'] ?? 1),
                    'max_capacity' => $roomCap,
                    'status' => $room['status']
                ];
            }
        }

        $canAccommodate = ($guests <= $totalAvailableCapacity);
        $warningMessage = null;

        if (!$canAccommodate) {
            if ($totalAvailableCapacity === 0) {
                $warningMessage = "The hotel has no available rooms for the selected dates.";
            } else {
                $warningMessage = "The hotel does not accommodate {$guests} guests as the available room capacity is limited to {$totalAvailableCapacity} guests.";
            }
        }

        echo json_encode([
            'success' => true,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => $guests,
            'total_hotel_capacity' => $totalHotelCapacity,
            'total_available_capacity' => $totalAvailableCapacity,
            'max_single_room_capacity' => $maxSingleRoomCapacity,
            'available_rooms_count' => count($availableRooms),
            'available_rooms' => $availableRooms,
            'can_accommodate' => $canAccommodate,
            'warning_message' => $warningMessage
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
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
        case 'check_hotel_capacity':
            checkHotelCapacity($input);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
}
