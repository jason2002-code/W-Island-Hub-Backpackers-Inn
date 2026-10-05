<?php
/**
 * VERIFY EMAIL ACTION
 * Handles:
 * - send_code: Generates 6-digit OTP code & verification link and emails the guest/user
 * - verify_code: Verifies the 6-digit code or link token
 * - check_status: Checks if an email is already verified
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mail_helper.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Handle verification via email link click: ?action=verify_link&token=...&email=...
    $action = $_GET['action'] ?? '';
    if ($action === 'verify_link') {
        handleLinkVerification();
        exit;
    }
    if ($action === 'check_status') {
        handleCheckStatus();
        exit;
    }
}

if ($method !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$action = $input['action'] ?? '';

switch ($action) {
    case 'send_code':
        handleSendCode($input);
        break;
    case 'verify_code':
        handleVerifyCode($input);
        break;
    case 'check_status':
        handleCheckStatus($input);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

/**
 * Generates and sends OTP code & verification link to the given email
 */
function handleSendCode($input) {
    $email = trim($input['email'] ?? '');
    $name = trim($input['name'] ?? 'Valued Guest');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
        return;
    }

    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        return;
    }

    // Check if user is already verified recently (within last 24 hours)
    $stmtCheck = $pdo->prepare("
        SELECT id FROM email_verifications 
        WHERE email = ? AND verified = 1 AND verified_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY id DESC LIMIT 1
    ");
    $stmtCheck->execute([$email]);
    if ($stmtCheck->fetch()) {
        echo json_encode([
            'success' => true,
            'already_verified' => true,
            'message' => 'Email is already verified!'
        ]);
        return;
    }

    // Generate secure 6-digit numeric code and 32-byte hex token
    $code = str_pad(strval(random_int(100000, 999999)), 6, '0', STR_PAD_LEFT);
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Invalidate prior unverified codes for this email
    $pdo->prepare("DELETE FROM email_verifications WHERE email = ? AND verified = 0")->execute([$email]);

    // Insert new verification record
    $insertStmt = $pdo->prepare("
        INSERT INTO email_verifications (email, code, token, verified, expires_at, created_at)
        VALUES (?, ?, ?, 0, ?, NOW())
    ");
    $insertStmt->execute([$email, $code, $token, $expiresAt]);

    // Construct full verification URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $rootDir = dirname($scriptDir);
    $rootDir = str_replace('\\', '/', $rootDir);
    $baseUrl = rtrim($protocol . $host . ($rootDir === '/' ? '' : $rootDir), '/');

    $verifyLink = $baseUrl . '/actions/verify_email_action.php?action=verify_link&token=' . urlencode($token) . '&email=' . urlencode($email);

    // Build Email Template
    $subject = "Your Verification Code: {$code} - W Island Hub";
    $htmlContent = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 24px; }
            .card { max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 20px; padding: 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
            .header { text-align: center; margin-bottom: 24px; }
            .brand { font-size: 24px; font-weight: bold; color: #0f172a; }
            .badge { display: inline-block; background: #fef3c7; color: #b45309; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 12px; }
            .code-box { background: #f0fdf4; border: 2px dashed #22c55e; border-radius: 16px; padding: 20px; text-align: center; margin: 24px 0; }
            .code-text { font-size: 36px; font-weight: 800; letter-spacing: 6px; color: #15803d; font-family: monospace; }
            .button { display: block; width: 100%; box-sizing: border-box; background: #0ea5e9; color: #ffffff !important; text-align: center; padding: 14px 24px; border-radius: 12px; font-weight: bold; text-decoration: none; font-size: 15px; margin-top: 16px; }
            .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 24px; }
        </style>
    </head>
    <body>
        <div class='card'>
            <div class='header'>
                <span class='badge'>Guest Verification</span>
                <div class='brand'>W Island Hub</div>
                <p style='color: #64748b; font-size: 14px; margin-top: 6px;'>Backpackers Inn &bull; Dauis, Bohol</p>
            </div>
            <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>To confirm your identity and verify your email address (<strong>" . htmlspecialchars($email) . "</strong>), please enter the 6-digit code below or click the direct verification button:</p>
            
            <div class='code-box'>
                <div style='font-size: 12px; text-transform: uppercase; color: #166534; font-weight: bold; margin-bottom: 6px;'>Verification Code</div>
                <div class='code-text'>" . htmlspecialchars($code) . "</div>
                <div style='font-size: 11px; color: #65a30d; margin-top: 6px;'>Expires in 15 minutes</div>
            </div>

            <a href='" . htmlspecialchars($verifyLink) . "' target='_blank' class='button'>
                Verify Email Instantly
            </a>

            <p style='font-size: 12px; color: #64748b; margin-top: 20px; text-align: center;'>
                Or copy this verification link into your browser:<br>
                <a href='" . htmlspecialchars($verifyLink) . "' style='color: #0ea5e9; word-break: break-all;'>" . htmlspecialchars($verifyLink) . "</a>
            </p>

            <div class='footer'>
                If you did not request this verification, you can safely ignore this email.<br>
                &copy; " . date('Y') . " W Island Hub Backpackers Inn. All rights reserved.
            </div>
        </div>
    </body>
    </html>
    ";

    $mailSent = MailHelper::send($email, $subject, $htmlContent);

    echo json_encode([
        'success' => true,
        'message' => 'Verification code sent to ' . $email . '. Please check your inbox or spam folder.',
        'expires_in_minutes' => 15,
        'mail_sent' => $mailSent,
        // In local XAMPP environment where SMTP might not be set up, expose code in dev response for seamless testing
        'debug_code' => $code,
        'verify_link' => $verifyLink
    ]);
}

/**
 * Verifies code submitted by the guest
 */
function handleVerifyCode($input) {
    $email = trim($input['email'] ?? '');
    $code = trim($input['code'] ?? '');

    if (empty($email) || empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Email and 6-digit verification code are required.']);
        return;
    }

    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT id, expires_at, verified 
        FROM email_verifications 
        WHERE email = ? AND code = ? 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$email, $code]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        echo json_encode(['success' => false, 'message' => 'Invalid verification code. Please check and try again.']);
        return;
    }

    if (strtotime($record['expires_at']) < time()) {
        echo json_encode(['success' => false, 'message' => 'Verification code has expired. Please request a new code.']);
        return;
    }

    // Mark as verified
    $pdo->prepare("
        UPDATE email_verifications 
        SET verified = 1, verified_at = NOW() 
        WHERE id = ?
    ")->execute([$record['id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Email successfully verified! You may now proceed.',
        'verified_email' => $email
    ]);
}

/**
 * Verifies email via link click (GET request with token)
 */
function handleLinkVerification() {
    $token = trim($_GET['token'] ?? '');
    $email = trim($_GET['email'] ?? '');

    $pdo = getDB();
    $verified = false;
    $errorMsg = '';

    if ($token && $email && $pdo) {
        $stmt = $pdo->prepare("
            SELECT id, expires_at 
            FROM email_verifications 
            WHERE email = ? AND token = ?
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$email, $token]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            if (strtotime($record['expires_at']) >= time()) {
                $pdo->prepare("UPDATE email_verifications SET verified = 1, verified_at = NOW() WHERE id = ?")->execute([$record['id']]);
                $verified = true;
            } else {
                $errorMsg = 'This verification link has expired. Please request a new one.';
            }
        } else {
            $errorMsg = 'Invalid verification link.';
        }
    } else {
        $errorMsg = 'Missing token or email parameter.';
    }

    // Display a friendly confirmation page that automatically informs the user
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Email Verification - W Island Hub</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-4">
        <div class="bg-white text-slate-800 max-w-md w-full p-8 rounded-3xl shadow-2xl text-center border border-slate-100">
            <?php if ($verified): ?>
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Email Verified!</h2>
                <p class="text-slate-600 text-sm mb-6">
                    Your email <strong><?= htmlspecialchars($email) ?></strong> has been successfully verified. You can now return to W Island Hub and complete your booking or sign-up.
                </p>
                <button onclick="window.close(); if(!window.closed) { window.location.href = '../index.php'; }" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-6 rounded-2xl transition shadow-lg">
                    Continue to W Island Hub
                </button>
            <?php else: ?>
                <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Verification Failed</h2>
                <p class="text-slate-600 text-sm mb-6">
                    <?= htmlspecialchars($errorMsg) ?>
                </p>
                <a href="../index.php" class="inline-block w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3.5 px-6 rounded-2xl transition shadow">
                    Back to Homepage
                </a>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Checks if an email has already been verified
 */
function handleCheckStatus($input = null) {
    $email = trim($input['email'] ?? $_GET['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['success' => false, 'verified' => false, 'message' => 'Email required.']);
        return;
    }

    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'verified' => false]);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT id, verified_at 
        FROM email_verifications 
        WHERE email = ? AND verified = 1 AND verified_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$email]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'verified' => !empty($res),
        'email' => $email
    ]);
}
