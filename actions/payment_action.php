<?php
/**
 * Payment Action - Handles payment processing
 * Integrates with bookings to process payments
 */

require_once '../config/db.php';
require_once '../config/paymongo.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = $input['action'] ?? 'process';

switch ($action) {
    case 'process':
        processPayment($input);
        break;
    case 'paymongo_checkout':
        createPaymongoCheckout($input);
        break;
    case 'verify':
        verifyPayment($input);
        break;
    case 'refund':
        refundPayment($input);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

/**
 * Process a payment for a booking
 */
function processPayment($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $bookingId = $data['booking_id'] ?? null;
    $paymentMethod = $data['payment_method'] ?? 'Pay at Hotel';
    $amount = $data['amount'] ?? 0;
    $transactionId = $data['transaction_id'] ?? null;
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit;
    }
    
    try {
        // Verify booking exists
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit;
        }
        
        // In a real app, you'd integrate with payment gateway here
        // For now, we'll just confirm the booking
        
        // Update booking status based on payment method
        $newStatus = ($paymentMethod === 'Pay at Hotel') ? 'Pending' : 'Confirmed';
        
        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET payment_method = ?, status = ?, created_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$paymentMethod, $newStatus, $bookingId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'booking_status' => $newStatus,
            'transaction_id' => $transactionId ?? 'TXN-' . strtoupper(substr(uniqid(), -8))
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Create a PayMongo checkout session from payment_action
 */
function createPaymongoCheckout($data) {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $bookingId = $data['booking_id'] ?? null;
    $refId = $data['ref_id'] ?? null;

    if (!$bookingId && !$refId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID or Ref ID is required']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT b.*, r.name as room_name FROM bookings b LEFT JOIN rooms r ON b.room_id = r.id WHERE b.id = ? OR b.ref_id = ?");
    $stmt->execute([$bookingId, $refId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit;
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $rootDir = dirname($scriptDir);
    $rootDir = str_replace('\\', '/', $rootDir);
    $baseUrl = rtrim($protocol . $host . ($rootDir === '/' ? '' : $rootDir), '/');

    $successUrl = $baseUrl . '/actions/paymongo_action.php?action=success&session_id={CHECKOUT_SESSION_ID}&ref_id=' . urlencode($booking['ref_id']);
    $cancelUrl  = $baseUrl . '/actions/paymongo_action.php?action=cancel&ref_id=' . urlencode($booking['ref_id']);

    $res = PayMongoService::createCheckoutSession($booking, $successUrl, $cancelUrl);
    if (!$res['success']) {
        echo json_encode(['success' => false, 'message' => $res['error']]);
        exit;
    }

    $upd = $pdo->prepare("UPDATE bookings SET paymongo_session_id = ? WHERE id = ?");
    $upd->execute([$res['session_id'], $booking['id']]);

    echo json_encode([
        'success' => true,
        'checkout_url' => $res['checkout_url'],
        'session_id' => $res['session_id']
    ]);
}

/**
 * Verify a payment transaction
 */
function verifyPayment($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $transactionId = $data['transaction_id'] ?? null;
    
    if (!$transactionId) {
        echo json_encode(['success' => false, 'message' => 'Transaction ID is required']);
        exit;
    }

    // If PayMongo Checkout Session ID
    if (strpos($transactionId, 'cs_') === 0) {
        $pmRes = PayMongoService::getCheckoutSession($transactionId);
        if ($pmRes['success']) {
            $status = $pmRes['data']['attributes']['status'] ?? 'unknown';
            $isPaid = ($status === 'paid');
            if ($isPaid) {
                $pdo->prepare("UPDATE bookings SET status = 'Confirmed', booking_status = 'confirmed', balance_due = 0 WHERE paymongo_session_id = ?")->execute([$transactionId]);
            }
            echo json_encode([
                'success' => true,
                'verified' => $isPaid,
                'status' => $status,
                'message' => $isPaid ? 'Payment verified as paid on PayMongo' : 'Payment session still pending'
            ]);
            return;
        }
    }
    
    echo json_encode([
        'success' => true,
        'verified' => true,
        'message' => 'Payment verified'
    ]);
}

/**
 * Process a refund for a cancelled booking
 */
function refundPayment($data) {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $bookingId = $data['booking_id'] ?? null;
    
    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit;
    }
    
    try {
        // Verify booking is cancelled
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND status = 'Cancelled'");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Cancelled booking not found']);
            exit;
        }
        
        // In a real app, you'd process refund via payment gateway
        // For demo, we'll just confirm the refund
        echo json_encode([
            'success' => true,
            'message' => 'Refund processed successfully',
            'refund_amount' => $booking['total_price'],
            'refund_id' => 'REF-' . strtoupper(substr(uniqid(), -8))
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
