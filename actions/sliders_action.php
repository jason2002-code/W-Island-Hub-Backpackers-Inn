<?php
header('Content-Type: application/json');
session_start();

require_once '../config/db.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_all':
        getAllSliders();
        break;
    case 'get_hero':
        getHeroSliders();
        break;
    case 'get_marquee':
        getMarqueeSliders();
        break;
    case 'create':
        createSlider();
        break;
    case 'update':
        updateSlider();
        break;
    case 'delete':
        deleteSlider();
        break;
    case 'toggle':
        toggleSlider();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getAllSliders() {
    global $conn;
    $sql = "SELECT * FROM carousel_sliders ORDER BY type, sort_order";
    $result = $conn->query($sql);
    
    $sliders = [];
    while ($row = $result->fetch_assoc()) {
        $sliders[] = $row;
    }
    
    echo json_encode(['success' => true, 'sliders' => $sliders]);
}

function getHeroSliders() {
    global $conn;
    $sql = "SELECT * FROM carousel_sliders WHERE type = 'hero' AND is_active = 1 ORDER BY sort_order";
    $result = $conn->query($sql);
    
    $sliders = [];
    while ($row = $result->fetch_assoc()) {
        $sliders[] = $row['image_url'];
    }
    
    echo json_encode(['success' => true, 'sliders' => $sliders]);
}

function getMarqueeSliders() {
    global $conn;
    $sql = "SELECT * FROM carousel_sliders WHERE type = 'marquee' AND is_active = 1 ORDER BY sort_order";
    $result = $conn->query($sql);
    
    $sliders = [];
    while ($row = $result->fetch_assoc()) {
        $sliders[] = $row['image_url'];
    }
    
    echo json_encode(['success' => true, 'sliders' => $sliders]);
}

function createSlider() {
    global $conn;
    
    $title = $_POST['title'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $type = $_POST['type'] ?? 'hero';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($image_url)) {
        echo json_encode(['success' => false, 'message' => 'Image URL is required']);
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO carousel_sliders (title, image_url, type, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $title, $image_url, $type, $sort_order);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Slider created successfully', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create slider']);
    }
}

function updateSlider() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    $title = $_POST['title'] ?? '';
    $image_url = $_POST['image_url'] ?? '';
    $type = $_POST['type'] ?? 'hero';
    $sort_order = intval($_POST['sort_order'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid slider ID']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE carousel_sliders SET title = ?, image_url = ?, type = ?, sort_order = ? WHERE id = ?");
    $stmt->bind_param("sssii", $title, $image_url, $type, $sort_order, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Slider updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update slider']);
    }
}

function deleteSlider() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid slider ID']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM carousel_sliders WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Slider deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete slider']);
    }
}

function toggleSlider() {
    global $conn;
    
    $id = intval($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid slider ID']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE carousel_sliders SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Slider status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update slider status']);
    }
}
