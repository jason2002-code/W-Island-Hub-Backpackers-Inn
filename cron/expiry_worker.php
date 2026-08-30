<?php
/**
 * CLI Background Expiry Worker
 * Can be scheduled via Windows Task Scheduler or Linux crontab:
 * Usage: php cron/expiry_worker.php
 */

require_once __DIR__ . '/../actions/cron_expiry_action.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting W Island Hub Expiry Worker...\n";

$result = runExpiryEngine();

if ($result['success']) {
    echo "[" . date('Y-m-d H:i:s') . "] SUCCESS: {$result['message']}\n";
    if (!empty($result['released_bookings'])) {
        foreach ($result['released_bookings'] as $item) {
            echo "  - Ref: {$item['ref_id']} | Guest: {$item['guest_name']} | Room ID: {$item['room_id']} | Reason: {$item['reason']}\n";
        }
    }
} else {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: {$result['message']}\n";
}

