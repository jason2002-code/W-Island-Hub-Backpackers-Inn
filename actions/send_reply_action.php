<?php
/**
 * SEND REPLY ACTION
 * Handles sending an email reply to a guest regarding their report/feedback.
 */
require_once '../config/db.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$reportId = $input['report_id'] ?? null;
$replyMessage = $input['message'] ?? '';

if (!$reportId || empty($replyMessage)) {
    echo json_encode(['success' => false, 'message' => 'Report ID and message are required']);
    exit;
}

$pdo = getDB();

try {
    // Get report and user details
    $stmt = $pdo->prepare("
        SELECT r.*, u.email, u.name 
        FROM reports r
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reportId]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report || empty($report['email'])) {
        echo json_encode(['success' => false, 'message' => 'Report not found or guest has no registered email.']);
        exit;
    }

    $to = $report['email'];
    $subject = "Re: Your feedback regarding: " . $report['subject'];
    $headers = "From: noreply@wislandhub.com\r\n";
    $headers .= "Reply-To: admin@wislandhub.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $body = "Dear " . $report['name'] . ",\n\n";
    $body .= "Regarding your recent " . $report['type'] . " submitted to W Island Hub:\n";
    $body .= "\"" . $report['subject'] . "\"\n\n";
    $body .= "Admin Reply:\n" . $replyMessage . "\n\n";
    $body .= "Best regards,\nW Island Hub Management";

    // Send Email (Requires SMTP server configured in php.ini)
    if (mail($to, $subject, $body, $headers)) {
        $updateStmt = $pdo->prepare("UPDATE reports SET status = 'Reviewed' WHERE id = ?");
        $updateStmt->execute([$reportId]);
        echo json_encode(['success' => true, 'message' => 'Reply sent successfully to ' . $to]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email. Ensure SMTP is configured on the server.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}