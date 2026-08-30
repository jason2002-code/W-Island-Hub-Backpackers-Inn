<?php
/**
 * Create Booking Controller Module
 * Handles multipart receipt upload, payment option validation, and hold deadline initialization.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/availability.php';

if (!function_exists('processCreateBooking')) {
function processCreateBooking($pdo = null, $data = null, $files = null) {
    if (!$pdo) {
        $pdo = getDB();
    }
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    if (!$data) {
        $data = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);
    }
    if (!$files) {
        $files = $_FILES;
    }

    $userId = !empty($data['user_id']) ? intval($data['user_id']) : ($_SESSION['user_id'] ?? null);
    $roomId = intval($data['room_id'] ?? 0);
    $guestName = trim($data['guest_name'] ?? $_SESSION['name'] ?? 'Guest');
    $guestEmail = trim($data['guest_email'] ?? $_SESSION['email'] ?? '');
    $guestPhone = trim($data['guest_phone'] ?? '');
    $checkIn = trim($data['check_in'] ?? $data['check_in_date'] ?? '');
    $checkOut = trim($data['check_out'] ?? $data['check_out_date'] ?? '');
    $paymentMethod = trim($data['payment_method'] ?? 'full_gcash');
    $gcashRef = trim($data['gcash_reference_no'] ?? '');
    $expectedArrival = trim($data['expected_arrival_time'] ?? '');

    // Validation
    if (!$roomId || empty($checkIn) || empty($checkOut)) {
        echo json_encode(['success' => false, 'message' => 'Room and dates are required']);
        return;
    }

    if (!$userId) {
        // Fallback: check if guest user exists or fetch ID 1
        $userCheck = $pdo->query("SELECT id FROM users LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $userId = $userCheck ? $userCheck['id'] : 1;
    }

    $checkInTs = strtotime($checkIn);
    $checkOutTs = strtotime($checkOut);
    if (!$checkInTs || !$checkOutTs || $checkOutTs <= $checkInTs) {
        echo json_encode(['success' => false, 'message' => 'Invalid check-in or check-out date']);
        return;
    }

    $diffDays = ($checkOutTs - $checkInTs) / 86400;
    $nights = max(1, (int)round($diffDays));

    // Room info
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Selected room does not exist']);
        return;
    }

    $pricePerNight = floatval($room['price']);
    $totalAmount = round($pricePerNight * $nights, 2);

    // Overlap availability check
    $availability = checkRoomAvailability($pdo, $roomId, $checkIn, $checkOut);
    if (!$availability['is_available']) {
        echo json_encode([
            'success' => false, 
            'message' => $availability['message'] ?? 'Room is not available for these dates.'
        ]);
        return;
    }

    // Receipt upload handler
    $receiptUrl = null;
    if (isset($files['receipt_image']) && $files['receipt_image']['error'] === UPLOAD_ERR_OK) {
        $fileSize = $files['receipt_image']['size'];
        if ($fileSize > 5 * 1024 * 1024) { // Max 5MB
            echo json_encode(['success' => false, 'message' => 'Receipt file size exceeds 5MB limit']);
            return;
        }

        $ext = strtolower(pathinfo($files['receipt_image']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowedExts)) {
            echo json_encode(['success' => false, 'message' => 'Invalid receipt image format. Allowed: JPG, PNG, WebP']);
            return;
        }

        $uploadDir = __DIR__ . '/../../assets/image/receipts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $receiptFileName = 'receipt_' . $userId . '_' . time() . '_' . substr(uniqid(), -4) . '.' . $ext;
        $targetPath = $uploadDir . $receiptFileName;

        if (move_uploaded_file($files['receipt_image']['tmp_name'], $targetPath)) {
            $receiptUrl = 'assets/image/receipts/' . $receiptFileName;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload receipt proof. Please try again.']);
            return;
        }
    }

    // Pathway logic & amounts
    $paidAmount = 0.00;
    $balanceDue = $totalAmount;
    $bookingStatus = 'pending_verification';
    $expiresAt = null;

    if ($paymentMethod === 'full_gcash') {
        // Option A: 100% upfront
        if (empty($gcashRef)) {
            echo json_encode(['success' => false, 'message' => 'GCash Reference Number is required for full payment']);
            return;
        }
        if (strlen($gcashRef) < 10) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid GCash Reference Number (min 10 characters)']);
            return;
        }
        if (!$receiptUrl) {
            echo json_encode(['success' => false, 'message' => 'Proof of GCash payment (receipt screenshot) is required']);
            return;
        }

        $paidAmount = $totalAmount;
        $balanceDue = 0.00;
        $bookingStatus = 'pending_verification';
        // 2-hour hold timer for verification
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours'));

    } elseif ($paymentMethod === 'deposit_gcash') {
        // Option B: 30% deposit
        if (empty($gcashRef)) {
            echo json_encode(['success' => false, 'message' => 'GCash Reference Number is required for downpayment']);
            return;
        }
        if (strlen($gcashRef) < 10) {
            echo json_encode(['success' => false, 'message' => 'Please enter a valid GCash Reference Number (min 10 characters)']);
            return;
        }
        if (!$receiptUrl) {
            echo json_encode(['success' => false, 'message' => 'Proof of GCash deposit payment (receipt screenshot) is required']);
            return;
        }

        $paidAmount = round($totalAmount * 0.30, 2);
        $balanceDue = round($totalAmount - $paidAmount, 2);
        $bookingStatus = 'pending_verification';
        // 2-hour hold timer for verification
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours'));

    } elseif ($paymentMethod === 'pay_at_hotel') {
        // Option C: Strict Pay at Hotel (Only if <= 24 hours)
        $checkInDateTime = strtotime($checkIn . ' 14:00:00');
        $timeUntilCheckIn = $checkInDateTime - time();

        if ($timeUntilCheckIn > 86400) {
            echo json_encode([
                'success' => false, 
                'message' => 'Pay at Hotel is only available for reservations checking in within 24 hours.'
            ]);
            return;
        }

        if (empty($expectedArrival)) {
            echo json_encode(['success' => false, 'message' => 'Expected arrival time is mandatory for Pay at Hotel reservations.']);
            return;
        }

        $paidAmount = 0.00;
        $balanceDue = $totalAmount;
        $bookingStatus = 'pending_arrival';

        // Room is held until Expected Arrival Time + 1 hour on the check-in date
        $arrivalTimestamp = strtotime($checkIn . ' ' . $expectedArrival);
        if (!$arrivalTimestamp) {
            $arrivalTimestamp = strtotime($checkIn . ' 14:00:00');
        }
        $expiresAt = date('Y-m-d H:i:s', $arrivalTimestamp + 3600); // +1 hour

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid payment pathway selected']);
        return;
    }

    $refId = 'WIH-' . strtoupper(substr(uniqid(), -6));

    try {
        $insertSql = "
            INSERT INTO bookings (
                ref_id, user_id, room_id, guest_name, guest_email, guest_phone,
                check_in, check_out, check_in_date, check_out_date, expected_arrival_time,
                nights, total_price, total_amount, paid_amount, balance_due,
                payment_method, gcash_reference_no, receipt_url,
                status, booking_status, expires_at, created_at
            ) VALUES (
                :ref_id, :user_id, :room_id, :guest_name, :guest_email, :guest_phone,
                :check_in, :check_out, :check_in_date, :check_out_date, :expected_arrival_time,
                :nights, :total_price, :total_amount, :paid_amount, :balance_due,
                :payment_method, :gcash_reference_no, :receipt_url,
                :status, :booking_status, :expires_at, NOW()
            )
        ";

        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([
            ':ref_id' => $refId,
            ':user_id' => $userId,
            ':room_id' => $roomId,
            ':guest_name' => $guestName,
            ':guest_email' => $guestEmail,
            ':guest_phone' => $guestPhone,
            ':check_in' => $checkIn,
            ':check_out' => $checkOut,
            ':check_in_date' => $checkIn,
            ':check_out_date' => $checkOut,
            ':expected_arrival_time' => !empty($expectedArrival) ? $expectedArrival : null,
            ':nights' => $nights,
            ':total_price' => $totalAmount,
            ':total_amount' => $totalAmount,
            ':paid_amount' => $paidAmount,
            ':balance_due' => $balanceDue,
            ':payment_method' => $paymentMethod,
            ':gcash_reference_no' => !empty($gcashRef) ? $gcashRef : null,
            ':receipt_url' => $receiptUrl,
            ':status' => 'Pending',
            ':booking_status' => $bookingStatus,
            ':expires_at' => $expiresAt
        ]);

        $bookingId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Reservation successfully placed!',
            'booking' => [
                'id' => $bookingId,
                'ref_id' => $refId,
                'room_name' => $room['name'],
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'nights' => $nights,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_due' => $balanceDue,
                'payment_method' => $paymentMethod,
                'booking_status' => $bookingStatus,
                'expires_at' => $expiresAt
            ]
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error placing reservation: ' . $e->getMessage()]);
    }
}
}

