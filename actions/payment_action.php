<?php
/**
 * Payment Action - Handles payment processing
 * Integrates with bookings to process payments
 */

require_once '../config/db.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'process';

switch ($action) {
    case 'process':
        processPayment($input);
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
    
    // In a real app, you'd verify with payment gateway
    // For demo purposes, we'll just return success
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
