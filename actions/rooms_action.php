<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Handle method spoofing for form-data PUT requests
if ($method === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
    $method = 'PUT';
}

if ($method === 'GET') {
    getRooms();
} elseif ($method === 'POST') {
    addRoom();
} elseif ($method === 'PUT') {
    updateRoom();
} elseif ($method === 'DELETE') {
    deleteRoom();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

// Retrieves all rooms from rooms table, ordered by created_at DESC
function getRooms() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM rooms ORDER BY created_at DESC");
        $stmt->execute();
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'rooms' => $rooms]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Query failed']);
    }
}

// Adds new room to database with validation and dynamic status column check
function addRoom() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = $data['name'] ?? null;
    $type = $data['type'] ?? null;
    $price = $data['price'] ?? null;
    $description = $data['description'] ?? null;
    $image = $data['image'] ?? 'assets/image/rooms/default.jpg';
    $capacity = $data['capacity'] ?? 2;
    $amenities = $data['amenities'] ?? null;
    
    // Handle file upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_base = __DIR__ . '/../assets/image/';
        if (!is_dir($upload_base)) mkdir($upload_base, 0755, true);
        $fileExt = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $newName = uniqid() . '.' . $fileExt;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_base . $newName)) {
            $image = 'assets/image/' . $newName;
        }
    }
    
    if (!$name || !$type || !$price) {
        echo json_encode(['success' => false, 'message' => 'Name, type, price required']);
        exit;
    }
    try {
        $stmt = $pdo->query("DESCRIBE rooms");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $hasStatus = in_array('status', $columns);
        if ($hasStatus) {
            $sql = "INSERT INTO rooms (name, type, price, description, image, capacity, amenities, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Available')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $type, $price, $description, $image, $capacity, $amenities]);
        } else {
            $sql = "INSERT INTO rooms (name, type, price, description, image, capacity, amenities) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $type, $price, $description, $image, $capacity, $amenities]);
        }
        echo json_encode(['success' => true, 'message' => 'Room added', 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error']);
    }
}

// Updates room fields dynamically based on POST data
function updateRoom() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    
    // Handle file upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_base = __DIR__ . '/../assets/image/';
        if (!is_dir($upload_base)) mkdir($upload_base, 0755, true);
        $fileExt = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $newName = uniqid() . '.' . $fileExt;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_base . $newName)) {
            $data['image'] = 'assets/image/' . $newName;
        }
    }
    
    $roomId = $data['id'] ?? null;
    if (!$roomId) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    try {
        $updates = [];
        $params = [];
        if (isset($data['name'])) { $updates[] = 'name = ?'; $params[] = $data['name']; }
        if (isset($data['type'])) { $updates[] = 'type = ?'; $params[] = $data['type']; }
        if (isset($data['price'])) { $updates[] = 'price = ?'; $params[] = $data['price']; }
        if (isset($data['description'])) { $updates[] = 'description = ?'; $params[] = $data['description']; }
        if (isset($data['image'])) { $updates[] = 'image = ?'; $params[] = $data['image']; }
        if (isset($data['capacity'])) { $updates[] = 'capacity = ?'; $params[] = $data['capacity']; }
        if (isset($data['amenities'])) { $updates[] = 'amenities = ?'; $params[] = $data['amenities']; }
        if (isset($data['status'])) { $updates[] = 'status = ?'; $params[] = $data['status']; }
        if (empty($updates)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            exit;
        }
        $params[] = $roomId;
        $sql = "UPDATE rooms SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'Updated']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error']);
    }
}

// Deletes room if no active bookings linked
function deleteRoom() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'DB failed']);
        exit;
    }
    $roomId = $_GET['id'] ?? null;
    if (!$roomId) {
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status != 'Cancelled'");
        $stmt->execute([$roomId]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete with active bookings']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->execute([$roomId]);
        echo json_encode(['success' => true, 'message' => 'Deleted']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error']);
    }
}
?>
