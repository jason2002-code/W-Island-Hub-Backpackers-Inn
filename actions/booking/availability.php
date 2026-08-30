<?php
/**
 * Room Availability & Overlap Checker Module
 * Evaluates room calendar collisions against confirmed, deposit paid, and active held reservations.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../cron_expire_bookings.php';

if (!function_exists('checkRoomAvailability')) {
function checkRoomAvailability($pdo, $roomId, $checkIn, $checkOut, $excludeBookingId = null) {
    if (!$pdo) {
        $pdo = getDB();
    }

    // Opportunistically run expiry cleaner on active holds
    expirePendingBookings($pdo);

    try {
        $sql = "
            SELECT id, ref_id, guest_name, check_in_date, check_out_date, booking_status, expires_at 
            FROM bookings 
            WHERE room_id = :room_id 
              AND (
                  (check_in_date < :check_out AND check_out_date > :check_in)
                  OR (check_in < :check_out AND check_out > :check_in)
              )
              AND (
                  booking_status IN ('confirmed', 'deposit_paid', 'completed')
                  OR (
                      booking_status IN ('pending_verification', 'pending_arrival') 
                      AND (expires_at IS NULL OR expires_at > NOW())
                  )
                  OR (
                      status = 'Confirmed' 
                      AND (booking_status IS NULL OR booking_status NOT IN ('cancelled_expired', 'no_show'))
                  )
              )
        ";

        $params = [
            ':room_id' => $roomId,
            ':check_in' => $checkIn,
            ':check_out' => $checkOut
        ];

        if ($excludeBookingId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeBookingId;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($conflicts)) {
            $activeHold = false;
            foreach ($conflicts as $c) {
                if (in_array($c['booking_status'], ['pending_verification', 'pending_arrival'])) {
                    $activeHold = true;
                    break;
                }
            }

            return [
                'is_available' => false,
                'has_active_hold' => $activeHold,
                'conflicts_count' => count($conflicts),
                'message' => $activeHold 
                    ? 'Room is temporarily held by another guest awaiting verification or arrival.'
                    : 'Room is already booked and confirmed for the selected dates.'
            ];
        }

        return [
            'is_available' => true,
            'message' => 'Room is available for the selected dates.'
        ];

    } catch (PDOException $e) {
        return [
            'is_available' => false,
            'message' => 'Availability check error: ' . $e->getMessage()
        ];
    }
}
}

