<?php
/**
 * Background Expiry Engine (Cron / Scheduled Worker)
 * 
 * Job 1 (Unverified GCash Payments):
 * Auto-cancels bookings in 'pending_verification' where expires_at < NOW() (2-hour hold timer exceeded).
 * Transitions status to 'cancelled_expired' and unlocks room inventory.
 * 
 * Job 2 (Unclaimed "Pay at Hotel"):
 * Auto-cancels bookings in 'pending_arrival' where expires_at < NOW() (expected_arrival_time + 1 hour exceeded).
 * Transitions status to 'no_show' and releases room inventory.
 * 
 * Can be executed via:
 * 1. CLI: php actions/cron_expire_bookings.php
 * 2. Scheduled Web Request / HTTP Cron: GET/POST /actions/cron_expire_bookings.php
 * 3. Imported as a helper function: expirePendingBookings()
 */

require_once __DIR__ . '/../config/db.php';

if (!function_exists('expirePendingBookings')) {
function expirePendingBookings($pdo = null) {
    if (!$pdo) {
        $pdo = getDB();
    }
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }

    $now = date('Y-m-d H:i:s');
    $results = [
        'success' => true,
        'timestamp' => $now,
        'gcash_expired_count' => 0,
        'gcash_expired_ids' => [],
        'hotel_no_show_count' => 0,
        'hotel_no_show_ids' => []
    ];

    try {
        // JOB 1: Expire Unverified GCash Payments (held for 2 hours)
        $stmt = $pdo->prepare("
            SELECT id, ref_id, room_id, guest_name, created_at, expires_at 
            FROM bookings 
            WHERE booking_status = 'pending_verification' 
              AND (
                  (expires_at IS NOT NULL AND expires_at <= NOW())
                  OR (expires_at IS NULL AND created_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR))
              )
        ");
        $stmt->execute();
        $expiredGcash = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($expiredGcash)) {
            $ids = array_column($expiredGcash, 'id');
            $inClause = implode(',', array_fill(0, count($ids), '?'));
            
            $updateStmt = $pdo->prepare("
                UPDATE bookings 
                SET booking_status = 'cancelled_expired', 
                    status = 'Cancelled' 
                WHERE id IN ($inClause)
            ");
            $updateStmt->execute($ids);

            $results['gcash_expired_count'] = count($ids);
            $results['gcash_expired_ids'] = $ids;
            $results['gcash_expired_refs'] = array_column($expiredGcash, 'ref_id');
        }

        // JOB 2: Expire Unclaimed "Pay at Hotel" Reservations (past expected_arrival_time + 1 hour)
        $stmt = $pdo->prepare("
            SELECT id, ref_id, room_id, guest_name, check_in_date, expected_arrival_time, expires_at 
            FROM bookings 
            WHERE booking_status = 'pending_arrival' 
              AND (
                  (expires_at IS NOT NULL AND expires_at <= NOW())
                  OR (
                      expires_at IS NULL 
                      AND expected_arrival_time IS NOT NULL 
                      AND TIMESTAMP(check_in_date, expected_arrival_time) <= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                  )
                  OR (
                      expires_at IS NULL 
                      AND expected_arrival_time IS NULL 
                      AND TIMESTAMP(check_in_date, '16:00:00') <= NOW()
                  )
              )
        ");
        $stmt->execute();
        $noShowBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($noShowBookings)) {
            $ids = array_column($noShowBookings, 'id');
            $inClause = implode(',', array_fill(0, count($ids), '?'));
            
            $updateStmt = $pdo->prepare("
                UPDATE bookings 
                SET booking_status = 'no_show', 
                    status = 'Cancelled' 
                WHERE id IN ($inClause)
            ");
            $updateStmt->execute($ids);

            $results['hotel_no_show_count'] = count($ids);
            $results['hotel_no_show_ids'] = $ids;
            $results['hotel_no_show_refs'] = array_column($noShowBookings, 'ref_id');
        }

        $results['total_released'] = $results['gcash_expired_count'] + $results['hotel_no_show_count'];
        $results['message'] = "Expiry worker ran successfully. Total holds released: {$results['total_released']}";

        return $results;

    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Error running expiry engine: ' . $e->getMessage()
        ];
    }
}
}

// If directly invoked via CLI or HTTP request:
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $res = expirePendingBookings();
    
    if (php_sapi_name() === 'cli') {
        echo "[" . date('Y-m-d H:i:s') . "] {$res['message']}\n";
        if (!empty($res['gcash_expired_refs'])) {
            echo " - Cancelled expired GCash refs: " . implode(', ', $res['gcash_expired_refs']) . "\n";
        }
        if (!empty($res['hotel_no_show_refs'])) {
            echo " - Released hotel no-show refs: " . implode(', ', $res['hotel_no_show_refs']) . "\n";
        }
    } else {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode($res, JSON_PRETTY_PRINT);
    }
}

