<?php
require_once '../config/db.php';
header('Content-Type: application/json');

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
    // Add admin_response column if it doesn't exist yet
    $stmt = $pdo->query("SHOW COLUMNS FROM reports LIKE 'admin_response'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE reports ADD COLUMN admin_response TEXT DEFAULT NULL");
    }

    // Save the reply and mark as Resolved
    $stmt = $pdo->prepare("UPDATE reports SET admin_response = ?, status = 'Resolved' WHERE id = ?");
    $stmt->execute([$replyMessage, $reportId]);

    echo json_encode(['success' => true, 'message' => 'Reply saved successfully']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>