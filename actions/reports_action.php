<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    getReports();
} elseif ($method === 'POST') {
    addReport();
} elseif ($method === 'DELETE') {
    deleteReport();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

function getReports() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT r.*, b.room_id, rm.name as room_name, u.name as user_name 
            FROM reports r 
            LEFT JOIN bookings b ON r.booking_id = b.id 
            LEFT JOIN rooms rm ON b.room_id = rm.id 
            LEFT JOIN users u ON r.user_id = u.id 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        
        $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'reports' => $reports
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function addReport() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $userId = $data['user_id'] ?? null;
    $bookingId = $data['booking_id'] ?? null;
    $subject = $data['subject'] ?? null;
    $message = $data['message'] ?? null;
    $type = $data['type'] ?? 'feedback'; // feedback, complaint, suggestion
    
    if (!$userId || !$message) {
        echo json_encode(['success' => false, 'message' => 'User ID and message are required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO reports (user_id, booking_id, subject, message, type, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$userId, $bookingId, $subject, $message, $type]);
        
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully', 'id' => $pdo->lastInsertId()]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteReport() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $reportId = $_GET['id'] ?? null;
    
    if (!$reportId) {
        echo json_encode(['success' => false, 'message' => 'Report ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
        $stmt->execute([$reportId]);
        
        echo json_encode(['success' => true, 'message' => 'Report deleted successfully']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get dashboard statistics
function getDashboardStats() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    try {
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $totalUsers = $stmt->fetchColumn();
        
        // Total bookings
        $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
        $totalBookings = $stmt->fetchColumn();
        
        // Total rooms
        $stmt = $pdo->query("SELECT COUNT(*) FROM rooms");
        $totalRooms = $stmt->fetchColumn();
        
        // Total revenue (confirmed bookings)
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM bookings WHERE status = 'Confirmed'");
        $totalRevenue = $stmt->fetchColumn();
        
        // Pending bookings
        $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'");
        $pendingBookings = $stmt->fetchColumn();
        
        // Confirmed bookings
        $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Confirmed'");
        $confirmedBookings = $stmt->fetchColumn();
        
        // Cancelled bookings
        $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Cancelled'");
        $cancelledBookings = $stmt->fetchColumn();
        
        // Recent bookings
        $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC LIMIT 5");
        $recentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalBookings' => $totalBookings,
                'totalRooms' => $totalRooms,
                'totalRevenue' => $totalRevenue,
                'pendingBookings' => $pendingBookings,
                'confirmedBookings' => $confirmedBookings,
                'cancelledBookings' => $cancelledBookings,
                'recentBookings' => $recentBookings
            ]
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
