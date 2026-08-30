<?php
/**
 * Standalone Cron Entrypoint
 */
require_once __DIR__ . '/../actions/cron_expire_bookings.php';

if (php_sapi_name() === 'cli') {
    echo "Running Island Hub Booking Expiry Cron...\n";
    $result = expirePendingBookings();
    echo $result['message'] . "\n";
} else {
    $result = expirePendingBookings();
    header('Content-Type: application/json');
    echo json_encode($result);
}

