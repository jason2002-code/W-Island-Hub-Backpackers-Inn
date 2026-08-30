<?php
/**
 * BACKGROUND EXPIRY ENGINE
 * Handles automatic cancellation and release of room locks for:
 * 1. Unverified GCash Payments exceeding the 2-hour hold timer (booking_status = 'pending_verification')
 * 2. Unclaimed Pay at Hotel bookings exceeding expected arrival time + 1 hour (booking_status = 'pending_arrival')
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Execute Expiry Routine
 * @param PDO|null $pdo
 * @return array
 */
function runExpiryEngine($pdo = null) {
    if (!$pdo) {
        $pdo = getDB();
    }
    if (!$pdo) {
        return [
            'success' => false,
            'message' => 'Database connection failed'
        ];
    }

    $results = [
        'success' => true,
        'timestamp' => date('Y-m-d H:i:s'),
        'unverified_gcash_cancelled' => 0,
        'unclaimed_hotel_cancelled' => 0,
        'released_bookings' => []
    ];

    try {
        // --- JOB 1: Cancel unverified GCash payments (> 2 hours / expires_at passed) ---
        $stmt1 = $pdo->prepare("
            SELECT id, ref_id, room_id, guest_name, expires_at, created_at
            FROM bookings
            WHERE booking_status = 'pending_verification'
              AND (
                  (expires_at IS NOT NULL AND expires_at < NOW())
                  OR (expires_at IS NULL AND created_at < (NOW() - INTERVAL 2 HOUR))
              )
        ");
        $stmt1->execute();
        $expiredGcash = $stmt1->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($expiredGcash)) {
            $ids1 = array_column($expiredGcash, 'id');
            $inClause1 = implode(',', array_fill(0, count($ids1), '?'));

            $updateStmt1 = $pdo->prepare("
                UPDATE bookings
                SET booking_status = 'cancelled_expired',
                    status = 'Cancelled',
                    updated_at = NOW()
                WHERE id IN ($inClause1)
            ");
            $updateStmt1->execute($ids1);

            $results['unverified_gcash_cancelled'] = count($expiredGcash);
            foreach ($expiredGcash as $b) {
                $results['released_bookings'][] = [
                    'id' => $b['id'],
                    'ref_id' => $b['ref_id'],
                    'reason' => 'Unverified GCash 2-hour Hold Expired',
                    'room_id' => $b['room_id'],
                    'guest_name' => $b['guest_name']
                ];
            }
        }

        // --- JOB 2: Cancel unclaimed Pay at Hotel bookings (Arrival time + 1 hour passed) ---
        $stmt2 = $pdo->prepare("
            SELECT id, ref_id, room_id, guest_name, check_in_date, check_in, expected_arrival_time, expires_at
            FROM bookings
            WHERE booking_status = 'pending_arrival'
              AND (
                  (expires_at IS NOT NULL AND expires_at < NOW())
                  OR (
                      expected_arrival_time IS NOT NULL 
                      AND TIMESTAMP(COALESCE(check_in_date, check_in), expected_arrival_time) + INTERVAL 1 HOUR < NOW()
                  )
                  OR (
                      expected_arrival_time IS NULL 
                      AND TIMESTAMP(COALESCE(check_in_date, check_in), '16:00:00') < NOW()
                  )
              )
        ");
        $stmt2->execute();
        $expiredHotel = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($expiredHotel)) {
            $ids2 = array_column($expiredHotel, 'id');
            $inClause2 = implode(',', array_fill(0, count($ids2), '?'));

            $updateStmt2 = $pdo->prepare("
                UPDATE bookings
                SET booking_status = 'no_show',
                    status = 'Cancelled',
                    updated_at = NOW()
                WHERE id IN ($inClause2)
            ");
            $updateStmt2->execute($ids2);

            $results['unclaimed_hotel_cancelled'] = count($expiredHotel);
            foreach ($expiredHotel as $b) {
                $results['released_bookings'][] = [
                    'id' => $b['id'],
                    'ref_id' => $b['ref_id'],
                    'reason' => 'Pay at Hotel Arrival Window Expired (No-Show)',
                    'room_id' => $b['room_id'],
                    'guest_name' => $b['guest_name']
                ];
            }
        }

        $results['total_released'] = $results['unverified_gcash_cancelled'] + $results['unclaimed_hotel_cancelled'];
        $results['message'] = "Expiry engine executed successfully. Released {$results['total_released']} room hold(s).";

        return $results;
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Expiry Engine Error: ' . $e->getMessage()
        ];
    }
}

// Direct Web API Request Handler
if (basename($_SERVER['PHP_SELF']) === 'cron_expiry_action.php') {
    header('Content-Type: application/json');
    $result = runExpiryEngine();
    echo json_encode($result);
    exit;
}

