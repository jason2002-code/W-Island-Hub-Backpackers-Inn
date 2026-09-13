<?php
/**
 * PAYMONGO WEBHOOK HANDLER
 * Receives real-time asynchronous event notifications from PayMongo
 * (e.g., checkout_session.payment.paid, payment.paid).
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/paymongo.php';

header('Content-Type: application/json');

$rawPayload = file_get_contents('php://input');
if (empty($rawPayload)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Empty webhook payload.']);
    exit;
}

$event = json_decode($rawPayload, true);
if (!$event || !isset($event['data']['attributes']['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid event structure.']);
    exit;
}

$eventType = $event['data']['attributes']['type'];
$eventData = $event['data']['attributes']['data'] ?? [];
$attributes = $eventData['attributes'] ?? [];

$pdo = getDB();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

try {
    if ($eventType === 'checkout_session.payment.paid') {
        $sessionId = $eventData['id'] ?? '';
        $payments = $attributes['payments'] ?? [];
        $paymentId = !empty($payments) ? ($payments[0]['id'] ?? null) : null;
        $sourceType = !empty($payments) ? ($payments[0]['attributes']['source']['type'] ?? 'paymongo') : 'paymongo';
        $metadata = $attributes['metadata'] ?? [];
        $refId = $metadata['ref_id'] ?? null;
        $bookingId = $metadata['booking_id'] ?? null;

        $methodLabel = 'PayMongo (' . ucfirst($sourceType) . ')';

        $stmt = $pdo->prepare("
            UPDATE bookings 
            SET status = 'Confirmed',
                booking_status = 'confirmed',
                paid_amount = CASE WHEN total_amount > 0 THEN total_amount ELSE total_price END,
                balance_due = 0.00,
                payment_method = ?,
                paymongo_payment_id = COALESCE(?, paymongo_payment_id),
                updated_at = NOW()
            WHERE paymongo_session_id = ? 
               OR (ref_id = ? AND ? IS NOT NULL)
               OR (id = ? AND ? IS NOT NULL)
        ");
        $stmt->execute([$methodLabel, $paymentId, $sessionId, $refId, $refId, $bookingId, $bookingId]);

        echo json_encode(['success' => true, 'message' => 'Booking marked confirmed via webhook.']);
        exit;
    }

    if ($eventType === 'payment.paid') {
        $paymentId = $eventData['id'] ?? null;
        $sourceType = $attributes['source']['type'] ?? 'card';
        $metadata = $attributes['metadata'] ?? [];
        $refId = $metadata['ref_id'] ?? null;

        if ($refId) {
            $methodLabel = 'PayMongo (' . ucfirst($sourceType) . ')';
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'Confirmed',
                    booking_status = 'confirmed',
                    paid_amount = CASE WHEN total_amount > 0 THEN total_amount ELSE total_price END,
                    balance_due = 0.00,
                    payment_method = ?,
                    paymongo_payment_id = COALESCE(?, paymongo_payment_id),
                    updated_at = NOW()
                WHERE ref_id = ?
            ");
            $stmt->execute([$methodLabel, $paymentId, $refId]);
        }

        echo json_encode(['success' => true, 'message' => 'Payment recorded.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Unhandled event type ignored: ' . $eventType]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

