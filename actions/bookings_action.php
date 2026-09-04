<?php
/**
 * BOOKINGS API ACTION
 * Multi-Tier Reservation Payment Engine Controller.
 * Handles creation, validation, proof uploads, status transitions, and CRUD.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/cron_expiry_action.php';

/**
 * Gets all bookings with comprehensive multi-tier and user details
 */
function getBookings() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
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
            ORDER BY b.created_at DESC
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
            $paymentMethodRaw = $b['payment_method'] ?? 'full_gcash';
            $paymentMethodLabel = 'Full Payment (GCash)';
            if ($paymentMethodRaw === 'deposit_gcash' || stripos($paymentMethodRaw, 'deposit') !== false) {
                $paymentMethodLabel = 'Deposit (30% GCash)';
            } elseif ($paymentMethodRaw === 'pay_at_hotel' || stripos($paymentMethodRaw, 'hotel') !== false) {
                $paymentMethodLabel = 'Pay at Hotel';
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
                'receiptUrl' => $b['receipt_url'] ?? null,
                'bookingStatus' => $b['booking_status'] ?? 'pending_verification',
                'status' => $b['status'] ?? 'Pending',
                'expiresAt' => $b['expires_at'],
                'specialRequests' => $b['special_requests'] ?? '',
                'created_at' => $b['created_at'],
                'updated_at' => $b['updated_at'] ?? $b['created_at'],
                'room_price' => (float)($b['room_price'] ?? 0)
            ];
        }, $bookings);

        echo json_encode(['success' => true, 'bookings' => $formatted]);
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
        echo json_encode(['success' => false, 'message' => 'Booking ID required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, r.name as room_name, r.type as room_type, r.price as room_price, r.image as room_image
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            WHERE b.id = ?
        ");
        $stmt->execute([$bookingId]);
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

    $userId = $data['user_id'] ?? null;
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

    // Basic Field Validations
    if (!$userId || !$roomId || !$checkIn || !$checkOut || empty($guestName)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required guest and stay details.']);
        exit;
    }

    // Role Validation: Admins cannot book
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if ($stmt->fetchColumn() === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Admins cannot create guest bookings.']);
        exit;
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

    $nights = (int)$checkOutDate->diff($checkInDate)->days;
    if ($nights < 1) $nights = 1;

    $pricePerNight = (float)$room['price'];
    $totalAmount = ($pricePerNight * $nights) + 15.00; // room subtotal + standard taxes/fees

    // Check Room Overlap Availability (Active holds and confirmed stays)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM bookings
        WHERE room_id = ?
          AND booking_status NOT IN ('cancelled_expired', 'no_show', 'completed')
          AND check_in_date < ?
          AND check_out_date > ?
    ");
    $stmt->execute([$roomId, $checkOut, $checkIn]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Room is no longer available on the selected dates. Please choose another date or room.']);
        exit;
    }

    // Payment Pathway Handling
    $paidAmount = 0.00;
    $balanceDue = $totalAmount;
    $bookingStatus = 'pending_verification';
    $legacyStatus = 'Pending';
    $expiresAt = null;
    $receiptUrl = null;

    // Normalizing payment method identifier
    if ($paymentMethod === 'GCash' || $paymentMethod === 'Full Payment' || $paymentMethod === 'full_gcash') {
        $paymentMethod = 'full_gcash';
    } elseif ($paymentMethod === 'Pay at Hotel' || $paymentMethod === 'pay_at_hotel') {
        $paymentMethod = 'pay_at_hotel';
    } elseif ($paymentMethod === 'Deposit' || $paymentMethod === 'deposit_gcash') {
        echo json_encode(['success' => false, 'message' => 'Downpayment is no longer supported. Please select GCash Full Payment or Pay at Hotel.']);
        exit;
    }

    // 1. Option A: Full Payment (GCash)
    if ($paymentMethod === 'full_gcash') {
        if (empty($gcashRefNo)) {
            echo json_encode(['success' => false, 'message' => 'GCash Reference Number is required for full payment.']);
            exit;
        }

        $receiptUrl = handleReceiptUpload();
        if (!$receiptUrl) {
            echo json_encode(['success' => false, 'message' => 'Proof of payment receipt image is required (JPG, PNG, WebP up to 5MB).']);
            exit;
        }

        $paidAmount = $totalAmount;
        $balanceDue = 0.00;
        $bookingStatus = 'pending_verification';
        $legacyStatus = 'Pending';
        $expiresAt = (new DateTime())->modify('+2 hours')->format('Y-m-d H:i:s');
    }
    // 2. Option B: Strict "Pay at Hotel" (Conditional: check-in <= 24 hrs)
    elseif ($paymentMethod === 'pay_at_hotel') {
        // Enforce 24-hour rule: check-in must be within 24 hours from now
        $now = new DateTime();
        $checkInDateTime = new DateTime($checkIn . ' 14:00:00'); // Standard check-in at 2:00 PM
        $diffHours = ($checkInDateTime->getTimestamp() - $now->getTimestamp()) / 3600;

        // Allow same day or next day within 24-30 hour threshold
        if ($diffHours > 30) {
            echo json_encode([
                'success' => false, 
                'message' => 'Strict "Pay at Hotel" is available only for reservations within 24 hours of check-in. Please select GCash Full Payment.'
            ]);
            exit;
        }

        if (empty($expectedArrivalTime)) {
            echo json_encode(['success' => false, 'message' => 'Expected Arrival Time is required for Pay at Hotel reservations.']);
            exit;
        }

        $paidAmount = 0.00;
        $balanceDue = $totalAmount;
        $bookingStatus = 'pending_arrival';
        $legacyStatus = 'Pending';

        // Hold room strictly until expected arrival time + 1 hour
        $arrivalDateTime = new DateTime($checkIn . ' ' . $expectedArrivalTime);
        $expiresAt = $arrivalDateTime->modify('+1 hour')->format('Y-m-d H:i:s');
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid payment method selected.']);
        exit;
    }

    try {
        $refId = 'BK-' . strtoupper(substr(uniqid(), -6));

        $stmt = $pdo->prepare("
            INSERT INTO bookings (
                ref_id, user_id, room_id, guest_name, guest_email, guest_phone,
                check_in_date, check_out_date, check_in, check_out, expected_arrival_time,
                nights, total_amount, paid_amount, balance_due, total_price,
                payment_method, gcash_reference_no, receipt_url,
                status, booking_status, expires_at, special_requests, created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?, NOW(), NOW()
            )
        ");

        $stmt->execute([
            $refId, $userId, $roomId, $guestName, $guestEmail, $guestPhone,
            $checkIn, $checkOut, $checkIn, $checkOut, $expectedArrivalTime,
            $nights, $totalAmount, $paidAmount, $balanceDue, $totalAmount,
            $paymentMethod, $gcashRefNo, $receiptUrl,
            $legacyStatus, $bookingStatus, $expiresAt, $specialRequests
        ]);

        $bookingId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation created successfully!',
            'booking_id' => $bookingId,
            'ref_id' => $refId,
            'booking_status' => $bookingStatus,
            'payment_method' => $paymentMethod,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'expires_at' => $expiresAt,
            'receipt_url' => $receiptUrl
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
 * Deletes specific booking
 */
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
        echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
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
        } elseif (isset($_GET['action']) && $_GET['action'] === 'get_one' && isset($_GET['id'])) {
            getSingleBooking((int)$_GET['id']);
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
