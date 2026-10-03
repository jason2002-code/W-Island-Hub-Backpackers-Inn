<?php
/**
 * BOOKINGS API ACTION
 * Multi-Tier Reservation Payment Engine Controller.
 * Handles creation, validation, proof uploads, status transitions, and CRUD.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/paymongo.php';
require_once __DIR__ . '/cron_expiry_action.php';

/**
 * Active / archived totals for booking tab badges
 */
function getArchiveBookingCounts(PDO $pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                COALESCE(SUM(COALESCE(is_archived, 0) = 0), 0) AS active,
                COALESCE(SUM(is_archived = 1), 0) AS archived
            FROM bookings
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'active' => (int) ($row['active'] ?? 0),
            'archived' => (int) ($row['archived'] ?? 0)
        ];
    } catch (Exception $e) {
        return ['active' => 0, 'archived' => 0];
    }
}

/**
 * Gets all bookings with comprehensive multi-tier and user details
 * Supports ?view=active (default), ?view=archived, or ?view=all
 */
function getBookings() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $view = strtolower($_GET['view'] ?? 'active');
    if (!in_array($view, ['active', 'archived', 'all'], true)) {
        $view = 'active';
    }

    $whereClause = "WHERE COALESCE(b.is_archived, 0) = 0";
    $orderBy = "b.created_at DESC";
    if ($view === 'archived') {
        $whereClause = "WHERE b.is_archived = 1";
        $orderBy = "b.archived_at DESC, b.created_at DESC";
    } elseif ($view === 'all') {
        $whereClause = "";
        $orderBy = "b.created_at DESC";
    }

    try {
        $stmt = $pdo->prepare("
            SELECT b.*, 
                   r.name as room_name, 
                   r.type as room_type,
                   r.price as room_price,
                   r.image as room_image,
                   COALESCE(b.guest_name, u.name) as final_guest_name, 
                   COALESCE(b.guest_email, u.email) as final_guest_email,
                   COALESCE(b.guest_phone, u.phone) as final_guest_phone
            FROM bookings b 
            LEFT JOIN rooms r ON b.room_id = r.id 
            LEFT JOIN users u ON b.user_id = u.id 
            {$whereClause}
            ORDER BY {$orderBy}
        ");
        $stmt->execute();
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formatted = array_map(function($b) {
            $checkIn = $b['check_in_date'] ?: $b['check_in'];
            $checkOut = $b['check_out_date'] ?: $b['check_out'];
            $total = (float)($b['total_amount'] > 0 ? $b['total_amount'] : $b['total_price']);
            $paid = (float)($b['paid_amount'] ?? 0);
            $balance = (float)($b['balance_due'] ?? ($total - $paid));

            // Normalize payment method for display
            $paymentMethodRaw = $b['payment_method'] ?? 'full_online';
            $paymentMethodLabel = 'Full Payment (Online)';
            if ($paymentMethodRaw === 'pay_at_hotel' || stripos($paymentMethodRaw, 'hotel') !== false) {
                $paymentMethodLabel = 'Pay at Hotel (20% Deposit)';
            } elseif (stripos($paymentMethodRaw, 'deposit') !== false) {
                $paymentMethodLabel = 'Deposit Paid (20%)';
            } elseif (stripos($paymentMethodRaw, 'paymongo') !== false) {
                $paymentMethodLabel = $paymentMethodRaw;
            }

            return [
                'id' => (int)$b['id'],
                'ref_id' => $b['ref_id'],
                'guestName' => $b['final_guest_name'],
                'guestEmail' => $b['final_guest_email'] ?? '',
                'guestPhone' => $b['final_guest_phone'] ?? '',
                'roomName' => $b['room_name'] ?? 'Room #' . $b['room_id'],
                'roomId' => (int)$b['room_id'],
                'roomType' => $b['room_type'] ?? 'Standard',
                'roomImage' => $b['room_image'] ?? 'assets/image/background.jpg',
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'expectedArrivalTime' => $b['expected_arrival_time'] ? substr($b['expected_arrival_time'], 0, 5) : null,
                'nights' => (int)$b['nights'],
                'total' => $total,
                'paidAmount' => $paid,
                'balanceDue' => max(0, $balance),
                'paymentMethod' => $paymentMethodLabel,
                'paymentMethodRaw' => $paymentMethodRaw,
                'gcashRefNo' => $b['gcash_reference_no'] ?? null,
                'paymongoSessionId' => $b['paymongo_session_id'] ?? null,
                'paymongoPaymentId' => $b['paymongo_payment_id'] ?? null,
                'receiptUrl' => $b['receipt_url'] ?? null,
                'bookingStatus' => $b['booking_status'] ?? 'pending_verification',
                'status' => $b['status'] ?? 'Pending',
                'expiresAt' => $b['expires_at'],
                'specialRequests' => $b['special_requests'] ?? '',
                'isArchived' => (int)($b['is_archived'] ?? 0),
                'archivedAt' => $b['archived_at'] ?? null,
                'created_at' => $b['created_at'],
                'updated_at' => $b['updated_at'] ?? $b['created_at'],
                'room_price' => (float)($b['room_price'] ?? 0),
                'adults' => (int)($b['adults'] ?? 1),
                'children' => (int)($b['children'] ?? 0),
                'children_ages' => $b['children_ages'] ?? '[]',
                'childrenAges' => !empty($b['children_ages']) ? (json_decode($b['children_ages'], true) ?: []) : [],
                'roomsCount' => (int)($b['rooms_count'] ?? 1),
                'bedsCount' => (int)($b['beds_count'] ?? 1)
            ];
        }, $bookings);

        $counts = getArchiveBookingCounts($pdo);
        echo json_encode([
            'success' => true, 
            'view' => $view,
            'bookings' => $formatted,
            'counts' => $counts
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
    }
}

/**
 * Gets a single booking by ID
 */
function getSingleBooking($bookingId) {
    $pdo = getDB();
    if (!$pdo || !$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID or Ref ID required']);
        exit;
    }
    try {
        if (is_numeric($bookingId)) {
            $stmt = $pdo->prepare("
                SELECT b.*, r.name as room_name, r.type as room_type, r.price as room_price, r.image as room_image
                FROM bookings b
                LEFT JOIN rooms r ON b.room_id = r.id
                WHERE b.id = ?
            ");
            $stmt->execute([(int)$bookingId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT b.*, r.name as room_name, r.type as room_type, r.price as room_price, r.image as room_image
                FROM bookings b
                LEFT JOIN rooms r ON b.room_id = r.id
                WHERE b.ref_id = ?
            ");
            $stmt->execute([$bookingId]);
        }
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            return;
        }

        echo json_encode(['success' => true, 'booking' => $booking]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

/**
 * Creates new booking with Multi-Tier Reservation Engine validation
 */
function createBooking($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $userId = !empty($data['user_id']) ? intval($data['user_id']) : null;
    $roomId = $data['room_id'] ?? null;
    $checkIn = $data['check_in'] ?? $data['checkIn'] ?? null;
    $checkOut = $data['check_out'] ?? $data['checkOut'] ?? null;
    $guestName = trim($data['guest_name'] ?? $data['guestName'] ?? 'Guest');
    $guestEmail = trim($data['guest_email'] ?? $data['guestEmail'] ?? '');
    $guestPhone = trim($data['guest_phone'] ?? $data['guestPhone'] ?? '');
    $specialRequests = trim($data['special_requests'] ?? $data['requests'] ?? '');
    $paymentMethod = trim($data['payment_method'] ?? $data['paymentMethod'] ?? 'full_gcash');
    $expectedArrivalTime = $data['expected_arrival_time'] ?? $data['expectedArrivalTime'] ?? null;
    $gcashRefNo = trim($data['gcash_reference_no'] ?? $data['gcash_ref_no'] ?? $data['gcashRefNo'] ?? '');

    // Basic Field Validations (Allows bookings without login; requires complete guest contact info)
    if (!$roomId || !$checkIn || !$checkOut || empty($guestName) || empty($guestEmail) || empty($guestPhone)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required guest details (Full Name, Email Address, and Phone Number).']);
        exit;
    }

    // If user is not logged in, but their email matches an existing registered user, associate user_id
    if (!$userId && !empty($guestEmail)) {
        $uStmt = $pdo->prepare("SELECT id, role FROM users WHERE email = ? LIMIT 1");
        $uStmt->execute([$guestEmail]);
        $matchedUser = $uStmt->fetch(PDO::FETCH_ASSOC);
        if ($matchedUser && ($matchedUser['role'] ?? '') !== 'admin') {
            $userId = intval($matchedUser['id']);
        }
    }

    // Role Validation: Admins cannot book
    if ($userId) {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        if ($stmt->fetchColumn() === 'admin') {
            echo json_encode(['success' => false, 'message' => 'Admins cannot create guest bookings.']);
            exit;
        }
    }

    // Room Validation
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Selected room was not found.']);
        exit;
    }
    if (($room['status'] ?? '') === 'Maintenance') {
        echo json_encode(['success' => false, 'message' => 'This room is currently under maintenance.']);
        exit;
    }

    // Date Calculation & Validation
    $today = date('Y-m-d');
    if ($checkIn < $today) {
        echo json_encode(['success' => false, 'message' => 'Check-in date cannot be in the past or outdated.']);
        exit;
    }

    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    if ($checkOutDate <= $checkInDate) {
        echo json_encode(['success' => false, 'message' => 'Check-out date must be strictly after check-in date.']);
        exit;
    }

    $adults = max(1, intval($data['adults'] ?? 1));
    $children = max(0, intval($data['children'] ?? 0));
    $childrenAgesRaw = $data['children_ages'] ?? '[]';
    $childrenAges = is_array($childrenAgesRaw) ? json_encode($childrenAgesRaw) : (string)$childrenAgesRaw;
    if ($children === 0) {
        $childrenAges = '[]';
    }
    $totalGuests = max(1, intval($data['guests'] ?? ($adults + $children)));
    $roomsCount = max(1, intval($data['rooms_count'] ?? $data['roomsCount'] ?? 1));
    $bedsCount = max(1, intval($data['beds_count'] ?? $data['bedsCount'] ?? $totalGuests));

    $isDorm = ($room['type'] === 'Dorm' || (int)$roomId === 5);
    $roomMaxCap = max(1, intval($room['max_capacity'] ?? $room['capacity'] ?? ($isDorm ? 8 : 2)));
    if ($isDorm && $roomMaxCap <= 0) $roomMaxCap = 8;

    $nights = (int)$checkOutDate->diff($checkInDate)->days;
    if ($nights < 1) $nights = 1;

    if ($isDorm) {
        $bedPrice = 460.00;
        $bedsCount = max(1, min($roomMaxCap, $bedsCount));

        // Check overlapping beds booked
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
              AND COALESCE(b.is_archived, 0) = 0
              AND COALESCE(b.check_in_date, b.check_in) < ?
              AND COALESCE(b.check_out_date, b.check_out) > ?
        ");
        $stmtDorm->execute([$roomId, $checkOut, $checkIn]);
        $occupiedBeds = (int)$stmtDorm->fetchColumn();
        $remainingBeds = max(0, $roomMaxCap - $occupiedBeds);

        if ($bedsCount > $remainingBeds) {
            echo json_encode([
                'success' => false, 
                'message' => "Only {$remainingBeds} bed(s) are available in Room 5 for your selected dates. You requested {$bedsCount} bed(s)."
            ]);
            exit;
        }

        // Pricing for Room 5: bed_price (460) * beds * nights + 15
        $totalAmount = ($bedPrice * $bedsCount * $nights) + 15.00;
        $adults = $bedsCount;
        $children = 0;
        $roomsCount = 1;
    } else {
        $bedsCount = 1;
        $totalAllowedCapacity = $roomMaxCap * $roomsCount;
        if ($totalGuests > $totalAllowedCapacity) {
            echo json_encode([
                'success' => false, 
                'message' => "The hotel does not accommodate {$totalGuests} guests for this reservation. The room is limited to {$totalAllowedCapacity} guests."
            ]);
            exit;
        }

        $pricePerNight = (float)$room['price'];
        $totalAmount = ($pricePerNight * $nights * $roomsCount) + 15.00; // room subtotal * rooms_count + standard taxes/fees

        // Check Room Overlap Availability (Active holds and confirmed stays)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings
            WHERE room_id = ?
              AND status IN ('Pending', 'Confirmed')
              AND booking_status NOT IN ('cancelled_expired', 'no_show')
              AND COALESCE(is_archived, 0) = 0
              AND COALESCE(check_in_date, check_in) < ?
              AND COALESCE(check_out_date, check_out) > ?
        ");
        $stmt->execute([$roomId, $checkOut, $checkIn]);
        $overlapCount = (int)$stmt->fetchColumn();
        if ($overlapCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Room is already booked on the selected dates. Please choose another date or room.']);
            exit;
        }
    }

    // Payment Pathway Handling
    $paidAmount = 0.00;
    $bookingStatus = 'pending_verification';
    $legacyStatus = 'Pending';
    $expiresAt = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');
    $receiptUrl = null;

    // Normalizing payment method identifier
    if ($paymentMethod === 'pay_at_hotel' || stripos($paymentMethod, 'hotel') !== false) {
        $paymentMethod = 'pay_at_hotel';
        $isDeposit = true;
        $depositAmount = round($totalAmount * 0.20, 2);
        $balanceDue = round($totalAmount - $depositAmount, 2);
        $amountToCharge = $depositAmount;
    } elseif ($paymentMethod === 'GCash' || $paymentMethod === 'Full Payment' || $paymentMethod === 'full_gcash') {
        echo json_encode([
            'success' => false, 
            'message' => 'Manual GCash receipt submission has been retired. Please select PayMongo Online to pay instantly with GCash, Cards, Maya, or GrabPay.'
        ]);
        exit;
    } elseif ($paymentMethod === 'Deposit' || $paymentMethod === 'deposit_gcash') {
        echo json_encode([
            'success' => false, 
            'message' => 'Manual GCash deposit has been upgraded. Please select Pay at Hotel to pay the 20% deposit online via PayMongo.'
        ]);
        exit;
    } else {
        $paymentMethod = 'PayMongo (Online)';
        $isDeposit = false;
        $depositAmount = $totalAmount;
        $balanceDue = 0.00;
        $amountToCharge = $totalAmount;
    }

    try {
        $refId = 'BK-' . strtoupper(substr(uniqid(), -6));

        $stmt = $pdo->prepare("
            INSERT INTO bookings (
                ref_id, user_id, room_id, guest_name, guest_email, guest_phone,
                adults, children, children_ages, rooms_count, beds_count,
                check_in_date, check_out_date, check_in, check_out, expected_arrival_time,
                nights, total_amount, paid_amount, balance_due, total_price,
                payment_method, gcash_reference_no, receipt_url,
                status, booking_status, expires_at, special_requests, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?, NOW(), NOW()
            )
        ");

        $stmt->execute([
            $refId, $userId, $roomId, $guestName, $guestEmail, $guestPhone,
            $adults, $children, $childrenAges, $roomsCount, $bedsCount,
            $checkIn, $checkOut, $checkIn, $checkOut, $expectedArrivalTime,
            $nights, $totalAmount, $paidAmount, $balanceDue, $totalAmount,
            $paymentMethod, $gcashRefNo, $receiptUrl,
            $legacyStatus, $bookingStatus, $expiresAt, $specialRequests
        ]);

        $bookingId = $pdo->lastInsertId();

        $checkoutUrl = null;
        $sessionId = null;

        // Initialize PayMongo Checkout (Both Full Payment and 20% Deposit are processed online)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $rootDir = dirname($scriptDir);
        $rootDir = str_replace('\\', '/', $rootDir);
        $baseUrl = rtrim($protocol . $host . ($rootDir === '/' ? '' : $rootDir), '/');

        $successUrl = $baseUrl . '/actions/paymongo_action.php?action=success&session_id={CHECKOUT_SESSION_ID}&ref_id=' . urlencode($refId);
        $cancelUrl  = $baseUrl . '/actions/paymongo_action.php?action=cancel&ref_id=' . urlencode($refId);

        $roomDisplayTitle = $isDorm ? "{$room['name']} ({$bedsCount} Bed" . ($bedsCount > 1 ? 's' : '') . " Dormitory)" : ($room['name'] ?? 'Room Stay');
        if ($isDeposit) {
            $roomDisplayTitle .= " (20% Security Deposit)";
        }

        $bookingPaymongoData = [
            'id' => $bookingId,
            'ref_id' => $refId,
            'room_id' => $roomId,
            'room_name' => $roomDisplayTitle,
            'nights' => $nights,
            'rooms_count' => $roomsCount,
            'beds_count' => $bedsCount,
            'total_amount' => $amountToCharge,
            'deposit_amount' => $isDeposit ? $depositAmount : $totalAmount,
            'balance_due' => $balanceDue,
            'is_deposit' => $isDeposit,
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'guest_phone' => $guestPhone,
            'check_in' => $checkIn,
            'user_id' => $userId
        ];

        $pmRes = PayMongoService::createCheckoutSession($bookingPaymongoData, $successUrl, $cancelUrl);
        if ($pmRes['success']) {
            $sessionId = $pmRes['session_id'];
            $checkoutUrl = $pmRes['checkout_url'];

            $updPm = $pdo->prepare("UPDATE bookings SET paymongo_session_id = ? WHERE id = ?");
            $updPm->execute([$sessionId, $bookingId]);
        }

        echo json_encode([
            'success' => true,
            'message' => $isDeposit 
                ? 'Reservation created! Please complete your 20% online security deposit to secure your room against no-show.' 
                : 'Reservation created successfully!',
            'booking_id' => $bookingId,
            'ref_id' => $refId,
            'booking_status' => $bookingStatus,
            'payment_method' => $paymentMethod,
            'guest_name' => $guestName,
            'guest_email' => $guestEmail,
            'guest_phone' => $guestPhone,
            'total_amount' => $totalAmount,
            'deposit_amount' => $isDeposit ? $depositAmount : $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'is_deposit' => $isDeposit,
            'expires_at' => $expiresAt,
            'receipt_url' => $receiptUrl,
            'checkout_url' => $checkoutUrl,
            'session_id' => $sessionId,
            'beds_count' => $bedsCount,
            'is_dorm' => $isDorm
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Handle Receipt Image Upload (Max 5MB, JPG/PNG/WebP)
 * @return string|null Relative web path to receipt
 */
function handleReceiptUpload() {
    if (!isset($_FILES['receipt_image']) || $_FILES['receipt_image']['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES['receipt_image'];

    // Max 5MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimes)) {
        return null;
    }

    $extension = 'jpg';
    if ($mimeType === 'image/png') $extension = 'png';
    if ($mimeType === 'image/webp') $extension = 'webp';

    $uploadDir = __DIR__ . '/../assets/image/uploads/receipts/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = 'receipt_' . uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'assets/image/uploads/receipts/' . $fileName;
    }

    return null;
}

/**
 * Admin Action: Approve Booking Receipt (pending_verification -> confirmed or deposit_paid)
 */
function approveBooking($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $bookingId = $data['id'] ?? null;
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit;
        }

        $paymentMethod = $booking['payment_method'];
        $newStatus = ($paymentMethod === 'deposit_gcash' || stripos($paymentMethod, 'deposit') !== false) 
            ? 'deposit_paid' 
            : 'confirmed';

        // Clear hold expiration timer upon approval
        $updateStmt = $pdo->prepare("
            UPDATE bookings
            SET booking_status = ?,
                status = 'Confirmed',
                expires_at = NULL,
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$newStatus, $bookingId]);

        echo json_encode([
            'success' => true,
            'message' => "Booking approved successfully! Status: " . ($newStatus === 'deposit_paid' ? 'Deposit Paid' : 'Confirmed'),
            'booking_status' => $newStatus
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

/**
 * Admin Action: Reject Booking / Invalid Receipt (pending_verification -> cancelled_expired)
 */
function rejectBooking($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $bookingId = $data['id'] ?? null;
    $reason = trim($data['reason'] ?? 'Invalid proof of payment');

    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE bookings
            SET booking_status = 'cancelled_expired',
                status = 'Cancelled',
                expires_at = NULL,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$bookingId]);

        echo json_encode([
            'success' => true,
            'message' => "Booking rejected. Room lock released.",
            'booking_status' => 'cancelled_expired'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

/**
 * Admin Action: Mark Checked-In & Collect Balance (pending_arrival / deposit_paid -> completed)
 */
function checkInAndCollectBalance($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $bookingId = $data['id'] ?? null;
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit;
        }

        $total = (float)($booking['total_amount'] > 0 ? $booking['total_amount'] : $booking['total_price']);

        $updateStmt = $pdo->prepare("
            UPDATE bookings
            SET booking_status = 'completed',
                status = 'Confirmed',
                paid_amount = ?,
                balance_due = 0.00,
                expires_at = NULL,
                updated_at = NOW()
            WHERE id = ?
        ");
        $updateStmt->execute([$total, $bookingId]);

        echo json_encode([
            'success' => true,
            'message' => 'Guest successfully checked in. Balance collected in full (₱' . number_format($total, 2) . ').',
            'booking_status' => 'completed',
            'paid_amount' => $total,
            'balance_due' => 0.00
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

/**
 * Cancel booking
 */
function cancelBooking($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    $bookingId = $data['id'] ?? null;
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET booking_status = 'cancelled_expired', 
                status = 'Cancelled',
                expires_at = NULL,
                updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$bookingId]);
        echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

/**
 * Updates booking status (Legacy and direct status updates)
 */
function updateBookingStatus($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB connection failed']);
        exit;
    }

    $bookingId = $data['id'] ?? null;
    $status = $data['status'] ?? $data['booking_status'] ?? null;

    if (!$bookingId || !$status) {
        echo json_encode(['success' => false, 'message' => 'ID and status required']);
        exit;
    }

    // Map status to both fields
    $legacyStatus = 'Pending';
    $bookingStatus = 'pending_verification';

    switch (strtolower($status)) {
        case 'confirmed':
            $legacyStatus = 'Confirmed';
            $bookingStatus = 'confirmed';
            break;
        case 'deposit_paid':
            $legacyStatus = 'Confirmed';
            $bookingStatus = 'deposit_paid';
            break;
        case 'pending_arrival':
            $legacyStatus = 'Pending';
            $bookingStatus = 'pending_arrival';
            break;
        case 'completed':
            $legacyStatus = 'Confirmed';
            $bookingStatus = 'completed';
            break;
        case 'cancelled':
        case 'cancelled_expired':
            $legacyStatus = 'Cancelled';
            $bookingStatus = 'cancelled_expired';
            break;
        case 'no_show':
            $legacyStatus = 'Cancelled';
            $bookingStatus = 'no_show';
            break;
        default:
            $legacyStatus = 'Pending';
            $bookingStatus = 'pending_verification';
            break;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = ?, booking_status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$legacyStatus, $bookingStatus, $bookingId]);
        echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

/**
 * Archives a booking (soft delete)
 */
function archiveBooking($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $bookingId = (int)($data['id'] ?? $data['booking_id'] ?? 0);
    if ($bookingId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid Booking ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, ref_id, is_archived, booking_status FROM bookings WHERE id = ? LIMIT 1");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit;
        }

        if ((int)$booking['is_archived'] === 1) {
            echo json_encode(['success' => false, 'message' => 'This booking is already archived.']);
            exit;
        }

        // Archive and release hold timer
        $update = $pdo->prepare("
            UPDATE bookings 
            SET is_archived = 1, 
                archived_at = NOW(), 
                expires_at = NULL, 
                updated_at = NOW() 
            WHERE id = ?
        ");
        $update->execute([$bookingId]);

        $counts = getArchiveBookingCounts($pdo);
        echo json_encode([
            'success' => true,
            'message' => 'Booking ' . ($booking['ref_id'] ?? '#' . $bookingId) . ' archived successfully. You can review or restore it from the Archive tab.',
            'booking_id' => $bookingId,
            'counts' => $counts
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Restores an archived booking back to the active list
 */
function restoreBooking($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $bookingId = (int)($data['id'] ?? $data['booking_id'] ?? 0);
    if ($bookingId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Valid Booking ID is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, ref_id, is_archived FROM bookings WHERE id = ? LIMIT 1");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit;
        }

        if ((int)$booking['is_archived'] === 0) {
            echo json_encode(['success' => false, 'message' => 'This booking is not archived.']);
            exit;
        }

        $update = $pdo->prepare("
            UPDATE bookings 
            SET is_archived = 0, 
                archived_at = NULL, 
                updated_at = NOW() 
            WHERE id = ?
        ");
        $update->execute([$bookingId]);

        $counts = getArchiveBookingCounts($pdo);
        echo json_encode([
            'success' => true,
            'message' => 'Booking ' . ($booking['ref_id'] ?? '#' . $bookingId) . ' restored to active list successfully.',
            'booking_id' => $bookingId,
            'counts' => $counts
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Batch archive multiple bookings or all cancelled/expired bookings
 */
function batchArchiveBookings($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $type = $data['type'] ?? null;
    $ids = $data['ids'] ?? [];

    try {
        if ($type === 'cancelled' || $type === 'expired') {
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET is_archived = 1, 
                    archived_at = NOW(), 
                    expires_at = NULL, 
                    updated_at = NOW() 
                WHERE booking_status IN ('cancelled_expired', 'no_show') 
                  AND COALESCE(is_archived, 0) = 0
            ");
            $stmt->execute();
            $affected = $stmt->rowCount();

            $counts = getArchiveBookingCounts($pdo);
            echo json_encode([
                'success' => true,
                'message' => "Archived {$affected} cancelled/expired booking(s).",
                'affected' => $affected,
                'counts' => $counts
            ]);
            return;
        }

        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'No booking IDs provided for batch archiving.']);
            exit;
        }

        $cleanIds = array_map('intval', array_filter($ids, 'is_numeric'));
        if (empty($cleanIds)) {
            echo json_encode(['success' => false, 'message' => 'No valid booking IDs provided.']);
            exit;
        }

        $inClause = implode(',', array_fill(0, count($cleanIds), '?'));
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET is_archived = 1, 
                archived_at = NOW(), 
                expires_at = NULL, 
                updated_at = NOW() 
            WHERE id IN ($inClause) AND COALESCE(is_archived, 0) = 0
        ");
        $stmt->execute($cleanIds);
        $affected = $stmt->rowCount();

        $counts = getArchiveBookingCounts($pdo);
        echo json_encode([
            'success' => true,
            'message' => "Archived {$affected} booking(s) successfully.",
            'affected' => $affected,
            'counts' => $counts
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Batch restore multiple bookings from archive
 */
function batchRestoreBookings($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $ids = $data['ids'] ?? [];
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'message' => 'No booking IDs provided for batch restoration.']);
        exit;
    }

    $cleanIds = array_map('intval', array_filter($ids, 'is_numeric'));
    if (empty($cleanIds)) {
        echo json_encode(['success' => false, 'message' => 'No valid booking IDs provided.']);
        exit;
    }

    try {
        $inClause = implode(',', array_fill(0, count($cleanIds), '?'));
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET is_archived = 0, 
                archived_at = NULL, 
                updated_at = NOW() 
            WHERE id IN ($inClause) AND is_archived = 1
        ");
        $stmt->execute($cleanIds);
        $affected = $stmt->rowCount();

        $counts = getArchiveBookingCounts($pdo);
        echo json_encode([
            'success' => true,
            'message' => "Restored {$affected} booking(s) to active list.",
            'affected' => $affected,
            'counts' => $counts
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Deletes specific booking permanently (only allowed for archived bookings)
 */
function deleteBooking($data = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    $bookingId = $data['id'] ?? $data['booking_id'] ?? $_GET['id'] ?? $_GET['booking_id'] ?? null;
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, ref_id, receipt_url, is_archived FROM bookings WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit;
        }

        // Safety policy: only archived bookings can be permanently deleted
        if ((int)$booking['is_archived'] !== 1) {
            echo json_encode([
                'success' => false, 
                'message' => 'Only archived bookings can be permanently deleted. Please archive this booking first.'
            ]);
            exit;
        }

        // Clean up receipt file if present
        if (!empty($booking['receipt_url']) && is_string($booking['receipt_url'])) {
            $receiptRel = str_replace('\\', '/', $booking['receipt_url']);
            $allowedBase = realpath(__DIR__ . '/../assets/image/uploads/receipts');
            $fullPath = realpath(__DIR__ . '/../' . ltrim($receiptRel, '/'));
            if ($allowedBase && $fullPath && strpos($fullPath, $allowedBase) === 0 && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        $delStmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $delStmt->execute([(int)$bookingId]);

        $counts = getArchiveBookingCounts($pdo);
        echo json_encode([
            'success' => true, 
            'message' => 'Booking ' . ($booking['ref_id'] ?? '#' . $bookingId) . ' permanently deleted.',
            'booking_id' => (int)$bookingId,
            'counts' => $counts
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

/**
 * Counts bookings by status for dashboard badges
 */
function getBookingCounts() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT booking_status, status, COUNT(*) as count 
            FROM bookings 
            WHERE COALESCE(is_archived, 0) = 0
            GROUP BY booking_status, status
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [
            'total' => 0,
            'pending' => 0,
            'confirmed' => 0,
            'cancelled' => 0,
            'pending_verification' => 0,
            'deposit_paid' => 0,
            'pending_arrival' => 0,
            'completed' => 0,
            'cancelled_expired' => 0,
            'no_show' => 0
        ];

        foreach ($rows as $r) {
            $bStatus = $r['booking_status'] ?? 'pending_verification';
            $legStatus = strtolower($r['status'] ?? 'pending');
            $c = (int)$r['count'];

            $counts['total'] += $c;
            if (isset($counts[$bStatus])) {
                $counts[$bStatus] += $c;
            }
            if (isset($counts[$legStatus])) {
                $counts[$legStatus] += $c;
            }
        }

        $archiveCounts = getArchiveBookingCounts($pdo);
        $counts['active'] = $archiveCounts['active'];
        $counts['archived'] = $archiveCounts['archived'];

        echo json_encode(['success' => true, 'counts' => $counts]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
}

// ----------------------------------------------------
// MAIN REQUEST DISPATCHER (Invoked on direct API call)
// ----------------------------------------------------
if (isset($_SERVER['REQUEST_METHOD'])) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }

    $method = $_SERVER['REQUEST_METHOD'];

    // Automatically execute expiry checks to ensure real-time inventory precision
    runExpiryEngine();

    if ($method === 'GET') {
        if (isset($_GET['action']) && $_GET['action'] === 'count') {
            getBookingCounts();
        } elseif (isset($_GET['action']) && ($_GET['action'] === 'get_one' || $_GET['action'] === 'get_single')) {
            $idOrRef = $_GET['id'] ?? $_GET['ref_id'] ?? null;
            getSingleBooking($idOrRef);
        } else {
            getBookings();
        }
    } elseif ($method === 'POST') {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
        } else {
            $input = $_POST;
        }

        $action = $input['action'] ?? 'create';

        switch ($action) {
            case 'create':
                createBooking($input);
                break;
            case 'archive':
                archiveBooking($input);
                break;
            case 'restore':
                restoreBooking($input);
                break;
            case 'batch_archive':
                batchArchiveBookings($input);
                break;
            case 'batch_restore':
                batchRestoreBookings($input);
                break;
            case 'delete':
                deleteBooking($input);
                break;
            case 'approve':
            case 'approve_receipt':
                approveBooking($input);
                break;
            case 'reject':
            case 'reject_receipt':
                rejectBooking($input);
                break;
            case 'check_in':
            case 'check_in_collect_balance':
                checkInAndCollectBalance($input);
                break;
            case 'cancel':
                cancelBooking($input);
                break;
            case 'update_status':
            default:
                updateBookingStatus($input);
                break;
        }
    } elseif ($method === 'DELETE') {
        deleteBooking();
    }
}
