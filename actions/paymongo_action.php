<?php
/**
 * PAYMONGO ACTION CONTROLLER
 * Handles PayMongo checkout session creation, payment verification,
 * and user return redirection for W Island Hub Backpackers Inn.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/paymongo.php';

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $jsonInput = json_decode($rawInput, true);
        if (isset($jsonInput['action'])) {
            $action = $jsonInput['action'];
            $_POST = array_merge($_POST, $jsonInput);
        }
    }
}

// Route actions
switch ($action) {
    case 'create_checkout':
        handleCreateCheckout();
        break;

    case 'verify_session':
        handleVerifySession();
        break;

    case 'success':
        handleSuccessReturn();
        break;

    case 'cancel':
        handleCancelReturn();
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid or missing action.']);
        exit;
}

/**
 * Determine dynamic base URL of the site
 */
function getBaseUrl(): string {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']); // e.g. /W Island Hub Backpackers Inn/actions
    $rootDir = dirname($scriptDir); // e.g. /W Island Hub Backpackers Inn
    $rootDir = str_replace('\\', '/', $rootDir);
    return rtrim($protocol . $host . ($rootDir === '/' ? '' : $rootDir), '/');
}

/**
 * Create Checkout Session for a booking
 */
function handleCreateCheckout() {
    header('Content-Type: application/json');
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    $bookingId = $_POST['booking_id'] ?? null;
    $refId = trim($_POST['ref_id'] ?? '');

    if (!$bookingId && empty($refId)) {
        echo json_encode(['success' => false, 'message' => 'Booking ID or Reference ID required.']);
        exit;
    }

    try {
        if ($bookingId) {
            $stmt = $pdo->prepare("
                SELECT b.*, r.name as room_name, r.type as room_type 
                FROM bookings b 
                LEFT JOIN rooms r ON b.room_id = r.id 
                WHERE b.id = ?
            ");
            $stmt->execute([$bookingId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT b.*, r.name as room_name, r.type as room_type 
                FROM bookings b 
                LEFT JOIN rooms r ON b.room_id = r.id 
                WHERE b.ref_id = ?
            ");
            $stmt->execute([$refId]);
        }

        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found.']);
            exit;
        }

        // Determine amount to charge (full balance due or total amount)
        $amountToCharge = (float)($booking['balance_due'] > 0 ? $booking['balance_due'] : $booking['total_amount']);
        if ($amountToCharge <= 0) {
            $amountToCharge = (float)($booking['total_price'] ?? 0);
        }
        if ($amountToCharge <= 0) {
            echo json_encode(['success' => false, 'message' => 'This reservation has no outstanding balance to pay.']);
            exit;
        }

        $booking['total_amount'] = $amountToCharge;

        $baseUrl = getBaseUrl();
        $successUrl = $baseUrl . '/actions/paymongo_action.php?action=success&session_id={CHECKOUT_SESSION_ID}&ref_id=' . urlencode($booking['ref_id']);
        $cancelUrl  = $baseUrl . '/actions/paymongo_action.php?action=cancel&ref_id=' . urlencode($booking['ref_id']);

        $res = PayMongoService::createCheckoutSession($booking, $successUrl, $cancelUrl);

        if (!$res['success']) {
            echo json_encode(['success' => false, 'message' => 'PayMongo Error: ' . ($res['error'] ?? 'Failed to create checkout session')]);
            exit;
        }

        $sessionId = $res['session_id'];
        $checkoutUrl = $res['checkout_url'];

        // Save session_id to database
        $updateStmt = $pdo->prepare("UPDATE bookings SET paymongo_session_id = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([$sessionId, $booking['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'PayMongo checkout session initialized',
            'session_id' => $sessionId,
            'checkout_url' => $checkoutUrl,
            'amount' => $amountToCharge,
            'ref_id' => $booking['ref_id']
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
}

/**
 * Verify PayMongo Checkout Session status
 */
function handleVerifySession() {
    header('Content-Type: application/json');
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    $sessionId = trim($_GET['session_id'] ?? $_POST['session_id'] ?? '');
    $refId = trim($_GET['ref_id'] ?? $_POST['ref_id'] ?? '');
    $bookingId = $_GET['booking_id'] ?? $_POST['booking_id'] ?? null;

    if (empty($sessionId) && (empty($refId) && empty($bookingId))) {
        echo json_encode(['success' => false, 'message' => 'Session ID or Booking reference required.']);
        exit;
    }

    try {
        if (empty($sessionId)) {
            if ($bookingId) {
                $s = $pdo->prepare("SELECT paymongo_session_id FROM bookings WHERE id = ?");
                $s->execute([$bookingId]);
            } else {
                $s = $pdo->prepare("SELECT paymongo_session_id FROM bookings WHERE ref_id = ?");
                $s->execute([$refId]);
            }
            $sessionId = $s->fetchColumn();
        }

        if (empty($sessionId)) {
            echo json_encode(['success' => false, 'message' => 'No PayMongo session found for this booking.']);
            exit;
        }

        $res = PayMongoService::getCheckoutSession($sessionId);
        if (!$res['success']) {
            echo json_encode(['success' => false, 'message' => $res['error']]);
            exit;
        }

        $attributes = $res['data']['attributes'] ?? [];
        $status = $attributes['status'] ?? 'unknown';
        $payments = $attributes['payments'] ?? [];
        $isPaid = ($status === 'paid' || !empty($payments));
        $paymentId = !empty($payments) ? ($payments[0]['id'] ?? null) : null;
        $paymentMethodType = !empty($payments) ? ($payments[0]['attributes']['source']['type'] ?? 'paymongo') : 'paymongo';

        if ($isPaid) {
            // Update booking as Confirmed & fully paid
            $upd = $pdo->prepare("
                UPDATE bookings 
                SET status = 'Confirmed',
                    booking_status = 'confirmed',
                    paid_amount = CASE WHEN total_amount > 0 THEN total_amount ELSE total_price END,
                    balance_due = 0.00,
                    payment_method = ?,
                    paymongo_payment_id = COALESCE(?, paymongo_payment_id),
                    updated_at = NOW()
                WHERE paymongo_session_id = ? 
                   OR ref_id = ?
            ");
            $methodLabel = 'PayMongo (' . ucfirst($paymentMethodType) . ')';
            $upd->execute([$methodLabel, $paymentId, $sessionId, $refId]);
        }

        echo json_encode([
            'success' => true,
            'session_status' => $status,
            'is_paid' => $isPaid,
            'payment_id' => $paymentId,
            'payment_method' => $paymentMethodType
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Verification error: ' . $e->getMessage()]);
    }
}

/**
 * Handle Browser Redirect from PayMongo Checkout on Payment Success
 */
function handleSuccessReturn() {
    $pdo = getDB();
    $sessionId = trim($_GET['session_id'] ?? '');
    $refId = trim($_GET['ref_id'] ?? '');
    $baseUrl = getBaseUrl();

    $verified = false;
    $booking = null;

    // Defensive fallback: If session_id was unexpanded macro or missing, retrieve from DB via ref_id
    if ((empty($sessionId) || $sessionId === '{CHECKOUT_SESSION_ID}') && !empty($refId) && $pdo) {
        $sStmt = $pdo->prepare("SELECT paymongo_session_id FROM bookings WHERE ref_id = ?");
        $sStmt->execute([$refId]);
        $sessionId = $sStmt->fetchColumn() ?: '';
    }

    // Defensive fallback: If ref_id is missing, retrieve from DB via session_id
    if (empty($refId) && !empty($sessionId) && $pdo) {
        $rStmt = $pdo->prepare("SELECT ref_id FROM bookings WHERE paymongo_session_id = ?");
        $rStmt->execute([$sessionId]);
        $refId = $rStmt->fetchColumn() ?: '';
    }

    if (!empty($sessionId)) {
        $res = PayMongoService::getCheckoutSession($sessionId);
        if ($res['success']) {
            $attrs = $res['data']['attributes'] ?? [];
            $payments = $attrs['payments'] ?? [];
            $isPaid = (($attrs['status'] ?? '') === 'paid' || !empty($payments));
            $paymentId = !empty($payments) ? ($payments[0]['id'] ?? null) : null;
            $sourceType = !empty($payments) ? ($payments[0]['attributes']['source']['type'] ?? 'online') : 'online';

            if ($isPaid && $pdo) {
                $methodLabel = 'PayMongo (' . ucfirst($sourceType) . ')';
                $upd = $pdo->prepare("
                    UPDATE bookings 
                    SET status = 'Confirmed',
                        booking_status = 'confirmed',
                        paid_amount = CASE WHEN total_amount > 0 THEN total_amount ELSE total_price END,
                        balance_due = 0.00,
                        payment_method = ?,
                        paymongo_payment_id = COALESCE(?, paymongo_payment_id),
                        updated_at = NOW()
                    WHERE paymongo_session_id = ? OR ref_id = ?
                ");
                $upd->execute([$methodLabel, $paymentId, $sessionId, $refId]);
                $verified = true;
            }
        }
    }

    if ($pdo && !empty($refId)) {
        $bStmt = $pdo->prepare("
            SELECT b.*, r.name as room_name, r.type as room_type 
            FROM bookings b 
            LEFT JOIN rooms r ON b.room_id = r.id 
            WHERE b.ref_id = ?
        ");
        $bStmt->execute([$refId]);
        $booking = $bStmt->fetch(PDO::FETCH_ASSOC);
    }

    $finalRedirectUrl = $baseUrl . '/index.php?paymongo_success=1&ref_id=' . urlencode($refId);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Successful - W Island Hub Backpackers Inn</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: { blue: '#0ea5e9', sand: '#fef3c7', dark: '#0f172a', green: '#22c55e' }
                        },
                        fontFamily: { sans: ['Poppins', 'sans-serif'], serif: ['Playfair Display', 'serif'] }
                    }
                }
            }
        </script>
        <meta http-equiv="refresh" content="4;url=<?php echo htmlspecialchars($finalRedirectUrl); ?>">
    </head>
    <body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
        <div class="max-w-md w-full bg-white rounded-3xl p-8 text-center shadow-2xl border border-gray-100 relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-100 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-5 text-3xl shadow-inner animate-bounce">
                <i class="fas fa-check"></i>
            </div>

            <span class="text-[11px] uppercase tracking-widest font-bold text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full inline-block mb-2">
                PayMongo Verified
            </span>

            <h2 class="text-2xl font-serif font-bold text-brand-dark mb-2">Payment Completed!</h2>
            <p class="text-xs text-gray-500 mb-6">Your transaction has been securely processed. Your accommodation reservation is officially confirmed.</p>

            <div class="bg-slate-50 p-4 rounded-2xl border border-gray-100 text-left text-xs space-y-2 mb-6">
                <div class="flex justify-between">
                    <span class="text-gray-400">Reference:</span>
                    <strong class="font-mono text-gray-800 font-bold"><?php echo htmlspecialchars($refId); ?></strong>
                </div>
                <?php if ($booking): ?>
                <div class="flex justify-between">
                    <span class="text-gray-400">Room:</span>
                    <strong class="text-gray-800"><?php echo htmlspecialchars($booking['room_name'] ?? 'Room'); ?></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Amount Paid:</span>
                    <strong class="text-emerald-600 font-bold">₱<?php echo number_format((float)($booking['total_amount'] > 0 ? $booking['total_amount'] : $booking['total_price']), 2); ?></strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Status:</span>
                    <span class="text-emerald-700 font-bold flex items-center gap-1">
                        <i class="fas fa-check-circle text-xs"></i> Confirmed
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <div class="text-xs text-gray-400 mb-4 flex items-center justify-center gap-2">
                <i class="fas fa-spinner fa-spin text-brand-blue"></i> Redirecting you back to your booking voucher...
            </div>

            <a href="<?php echo htmlspecialchars($finalRedirectUrl); ?>" class="w-full inline-block bg-brand-blue hover:bg-sky-600 text-white font-bold py-3 px-6 rounded-xl transition shadow text-xs">
                View Reservation Now &rarr;
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/**
 * Handle Browser Cancel Redirect
 */
function handleCancelReturn() {
    $refId = trim($_GET['ref_id'] ?? '');
    $baseUrl = getBaseUrl();
    header("Location: " . $baseUrl . "/index.php?paymongo_cancelled=1&ref_id=" . urlencode($refId) . "#rooms");
    exit;
}

