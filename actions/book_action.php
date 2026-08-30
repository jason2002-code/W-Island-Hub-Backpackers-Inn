<?php
/**
 * BOOK ACTION CONTROLLER
 * Central Router & Dispatcher for all multi-tier booking and payment actions.
 */

if (session_status() == PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/booking/quote_calculator.php';
require_once __DIR__ . '/booking/availability.php';
require_once __DIR__ . '/booking/create_booking.php';
require_once __DIR__ . '/booking/booking_manager.php';
require_once __DIR__ . '/cron_expire_bookings.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'calculate_quote' || $action === 'quote') {
        calculateQuote($_GET);
    } elseif ($action === 'check_availability') {
        $roomId = intval($_GET['room_id'] ?? 0);
        $checkIn = trim($_GET['check_in'] ?? '');
        $checkOut = trim($_GET['check_out'] ?? '');
        $result = checkRoomAvailability(getDB(), $roomId, $checkIn, $checkOut);
        echo json_encode($result);
    } elseif ($action === 'get_user_bookings') {
        getUserBookings($_GET);
    } elseif ($action === 'get_all' || $action === 'admin_bookings') {
        getAllBookingsData();
    } else {
        getAllBookingsData();
    }
} elseif ($method === 'POST') {
    if ($action === 'create' || $action === 'book_room' || empty($action)) {
        processCreateBooking(getDB(), $_POST ?: $input, $_FILES);
    } elseif ($action === 'calculate_quote' || $action === 'quote') {
        calculateQuote($_POST ?: $input);
    } elseif ($action === 'approve' || $action === 'approve_booking') {
        approveBooking($_POST ?: $input);
    } elseif ($action === 'reject' || $action === 'reject_booking') {
        rejectBooking($_POST ?: $input);
    } elseif ($action === 'check_in' || $action === 'check_in_collect_balance') {
        checkInCollectBalance($_POST ?: $input);
    } elseif ($action === 'no_show' || $action === 'mark_no_show') {
        markNoShow($_POST ?: $input);
    } elseif ($action === 'run_expiry_worker') {
        $result = expirePendingBookings();
        echo json_encode($result);
    } else {
        processCreateBooking(getDB(), $_POST ?: $input, $_FILES);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
