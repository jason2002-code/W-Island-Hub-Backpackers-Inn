<?php
/**
 * Booking Management & Transitions Module
 * Handles admin approvals, receipt rejections, front desk check-in / balance collection, and marking no-shows.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../cron_expire_bookings.php';

if (!function_exists('approveBooking')) {
function approveBooking($data = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    if (!$data) {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    $bookingId = intval($data['id'] ?? $data['booking_id'] ?? 0);
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            return;
        }

        // Determine target status based on payment method
        $newStatus = 'confirmed';
        if ($booking['payment_method'] === 'deposit_gcash') {
            $newStatus = 'deposit_paid';
        }

        $updateSql = "
            UPDATE bookings 
            SET booking_status = :new_status, 
                status = 'Confirmed', 
                expires_at = NULL 
            WHERE id = :id
        ";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            ':new_status' => $newStatus,
            ':id' => $bookingId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Booking payment approved successfully!',
            'new_status' => $newStatus
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Approval error: ' . $e->getMessage()]);
    }
}
}

if (!function_exists('rejectBooking')) {
function rejectBooking($data = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    if (!$data) {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    $bookingId = intval($data['id'] ?? $data['booking_id'] ?? 0);
    $reason = trim($data['reason'] ?? 'Invalid receipt / unverified payment');

    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }

    try {
        $updateStmt = $pdo->prepare("
            UPDATE bookings 
            SET booking_status = 'cancelled_expired', 
                status = 'Cancelled', 
                expires_at = NULL 
            WHERE id = ?
        ");
        $updateStmt->execute([$bookingId]);

        echo json_encode([
            'success' => true,
            'message' => 'Booking rejected and room inventory released.'
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Reject error: ' . $e->getMessage()]);
    }
}
}

if (!function_exists('checkInCollectBalance')) {
function checkInCollectBalance($data = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    if (!$data) {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    $bookingId = intval($data['id'] ?? $data['booking_id'] ?? 0);
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            return;
        }

        $totalAmount = floatval($booking['total_amount'] ?: $booking['total_price']);

        $updateStmt = $pdo->prepare("
            UPDATE bookings 
            SET booking_status = 'completed', 
                status = 'Confirmed', 
                paid_amount = :total, 
                balance_due = 0.00, 
                expires_at = NULL 
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':total' => $totalAmount,
            ':id' => $bookingId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Guest successfully checked-in and balance collected in full!'
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Check-in error: ' . $e->getMessage()]);
    }
}
}

if (!function_exists('markNoShow')) {
function markNoShow($data = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    if (!$data) {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    }

    $bookingId = intval($data['id'] ?? $data['booking_id'] ?? 0);
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }

    try {
        $updateStmt = $pdo->prepare("
            UPDATE bookings 
            SET booking_status = 'no_show', 
                status = 'Cancelled', 
                expires_at = NULL 
            WHERE id = ?
        ");
        $updateStmt->execute([$bookingId]);

        echo json_encode([
            'success' => true,
            'message' => 'Reservation marked as No-Show and room released.'
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'No-show error: ' . $e->getMessage()]);
    }
}
}

if (!function_exists('getAllBookingsData')) {
function getAllBookingsData($pdo = null) {
    if (!$pdo) {
        $pdo = getDB();
    }
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    // Opportunistically run expiry worker
    expirePendingBookings($pdo);

    try {
        $sql = "
            SELECT 
                b.*,
                r.name AS room_name,
                r.type AS room_type,
                r.image AS room_image,
                r.price AS room_price,
                u.name AS user_name,
                u.email AS user_email
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN users u ON b.user_id = u.id
            ORDER BY b.created_at DESC
        ";
        $stmt = $pdo->query($sql);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [
            'all' => count($bookings),
            'pending_verification' => 0,
            'pending_arrival' => 0,
            'deposit_paid' => 0,
            'confirmed' => 0,
            'completed' => 0,
            'cancelled_expired' => 0,
            'no_show' => 0
        ];

        foreach ($bookings as $b) {
            $bs = $b['booking_status'] ?? 'pending_verification';
            if (isset($counts[$bs])) {
                $counts[$bs]++;
            }
        }

        echo json_encode([
            'success' => true,
            'counts' => $counts,
            'bookings' => $bookings
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Fetch bookings error: ' . $e->getMessage()]);
    }
}
}

if (!function_exists('getUserBookings')) {
function getUserBookings($data = null) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    $userId = $data['user_id'] ?? $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        return;
    }

    // Opportunistically clean expired holds
    expirePendingBookings($pdo);

    try {
        $sql = "
            SELECT 
                b.*,
                r.name AS room_name,
                r.type AS room_type,
                r.image AS room_image,
                r.price AS room_price
            FROM bookings b
            LEFT JOIN rooms r ON b.room_id = r.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'bookings' => $bookings
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Fetch user bookings error: ' . $e->getMessage()]);
    }
}
}

