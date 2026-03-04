<?php
require_once '../config/db.php';

header('Content-Type: application/json');

// Handle requests
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    getRooms();
} elseif ($method === 'POST') {
    addRoom();
} elseif ($method === 'PUT') {
    updateRoom();
} elseif ($method === 'DELETE') {
    deleteRoom();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

function getRooms() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms ORDER BY created_at DESC");
        $stmt->execute();
        
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'rooms' => $rooms
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function addRoom() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Debug: Log the received data
    error_log("Received data: " . print_r($data, true));
    
    $name = $data['name'] ?? null;
    $type = $data['type'] ?? null;
    $price = $data['price'] ?? null;
    $description = $data['description'] ?? null;
    $image = $data['image'] ?? 'assets/image/rooms/default.jpg';
    $capacity = $data['capacity'] ?? 2;
    $amenities = $data['amenities'] ?? null;
    
    if (!$name || !$type || !$price) {
        echo json_encode(['success' => false, 'message' => 'Name, type and price are required']);
        exit;
    }
    
    try {
        // Check if status column exists
        $stmt = $pdo->query("DESCRIBE rooms");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $hasStatus = in_array('status', $columns);
        
        error_log("Room columns: " . print_r($columns, true));
        
        if ($hasStatus) {
            $sql = "INSERT INTO rooms (name, type, price, description, image, capacity, amenities, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Available')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $type, $price, $description, $image, $capacity, $amenities]);
        } else {
            $sql = "INSERT INTO rooms (name, type, price, description, image, capacity, amenities) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $type, $price, $description, $image, $capacity, $amenities]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Room added successfully', 'id' => $pdo->lastInsertId()]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateRoom() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    $roomId = $data['id'] ?? null;
    $name = $data['name'] ?? null;
    $type = $data['type'] ?? null;
    $price = $data['price'] ?? null;
    $description = $data['description'] ?? null;
    $image = $data['image'] ?? null;
    $capacity = $data['capacity'] ?? null;
    $amenities = $data['amenities'] ?? null;
    $status = $data['status'] ?? null;
    
    if (!$roomId) {
        echo json_encode(['success' => false, 'message' => 'Room ID is required']);
        exit;
    }
    
    try {
        // Build update query dynamically
        $updates = [];
        $params = [];
        
        if ($name) { $updates[] = 'name = ?'; $params[] = $name; }
        if ($type) { $updates[] = 'type = ?'; $params[] = $type; }
        if ($price) { $updates[] = 'price = ?'; $params[] = $price; }
        if ($description) { $updates[] = 'description = ?'; $params[] = $description; }
        if ($image) { $updates[] = 'image = ?'; $params[] = $image; }
        if ($capacity) { $updates[] = 'capacity = ?'; $params[] = $capacity; }
        if ($amenities) { $updates[] = 'amenities = ?'; $params[] = $amenities; }
        if ($status) { $updates[] = 'status = ?'; $params[] = $status; }
        
        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            exit;
        }
        
        $params[] = $roomId;
        $sql = "UPDATE rooms SET " . implode(', ', $updates) . " WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'Room updated successfully']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteRoom() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    $roomId = $_GET['id'] ?? null;
    
    if (!$roomId) {
        echo json_encode(['success' => false, 'message' => 'Room ID is required']);
        exit;
    }
    
    try {
        // Check if room has active bookings
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status != 'Cancelled'");
        $stmt->execute([$roomId]);
        $bookingCount = $stmt->fetchColumn();
        
        if ($bookingCount > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete room with active bookings']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        
        echo json_encode(['success' => true, 'message' => 'Room deleted successfully']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
