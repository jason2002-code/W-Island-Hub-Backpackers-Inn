<?php
/**
 * SLIDERS API ACTION
 * Handles CRUD operations for Hero Carousel and Marquee Gallery images.
 * Supports image uploads and status toggling.
 */
header('Content-Type: application/json');
error_log('sliders_action.php accessed: ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']);

session_start();
require_once '../config/db.php';

// Skip admin check for GET requests (public read), require for POST (write)
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_REQUEST['action'] ?? '';  // FIXED: Use $_REQUEST for POST FormData compatibility
error_log("Slider action: '$action'");

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
        error_log("Invalid slider action: '$action'");
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Fetch all sliders (for admin management)
function getAllSliders() {
    $pdo = getDB();
    if (!$pdo) {
        error_log('getAllSliders DB connection failed');
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM carousel_sliders ORDER BY type, sort_order ASC");
        $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'sliders' => $sliders]);
    } catch (Exception $e) {
        error_log('getAllSliders query error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Failed to load sliders']);
    }
}

// Fetch only active Hero sliders (for frontend)
function getHeroSliders() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        $stmt = $pdo->query("SELECT image_url FROM carousel_sliders WHERE type = 'hero' AND is_active = 1 ORDER BY sort_order");
        $sliders_raw = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $sliders = array_map(fn($url) => ['image_url' => $url], $sliders_raw);
        echo json_encode(['success' => true, 'sliders' => $sliders]);
    } catch (Exception $e) {
        error_log('getHeroSliders error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Query failed']);
    }
}

// Fetch only active Marquee sliders (for frontend)
function getMarqueeSliders() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        $stmt = $pdo->query("SELECT image_url FROM carousel_sliders WHERE type = 'marquee' AND is_active = 1 ORDER BY sort_order");
        $sliders_raw = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $sliders = array_map(fn($url) => ['image_url' => $url], $sliders_raw);
        echo json_encode(['success' => true, 'sliders' => $sliders]);
    } catch (Exception $e) {
        error_log('getMarqueeSliders error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Query failed']);
    }
}

// Create a new slider entry with image upload handling
function createSlider() {
    $pdo = getDB();
    $upload_base = __DIR__ . '/../assets/image/';
    if (!$pdo) {
        error_log('createSlider DB failed');
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    $title = trim($_POST['title'] ?? '');
    $type = in_array($_POST['type'] ?? 'hero', ['hero', 'marquee']) ? $_POST['type'] : 'hero';
    $sort_order = max(0, intval($_POST['sort_order'] ?? 0));
    $image_url = '';

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Title required']);
        return;
    }

    // Handle file upload (Priority: File Input > Manual URL)
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        if (!is_dir($upload_base)) mkdir($upload_base, 0755, true);
        $fileTmp = $_FILES['image_file']['tmp_name'];
        $fileSize = $_FILES['image_file']['size'];
        $fileExt = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg','jpeg','png','webp','gif'];
        
        if (in_array($fileExt, $allowedExt) && $fileSize < 5000000) {
            $newName = uniqid() . '.' . $fileExt;
            $image_url = 'assets/image/' . $newName;
            if (move_uploaded_file($fileTmp, $upload_base . $newName)) {
                error_log("Created slider uploaded: $image_url");
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed']);
                return;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid image (JPG/PNG/WEBP/GIF <5MB)']);
            return;
        }
    } elseif (!empty(trim($_POST['image_url'] ?? ''))) {
        $image_url = trim($_POST['image_url']);
        if (strpos($image_url, 'assets/image/') !== 0) {
            echo json_encode(['success' => false, 'message' => 'Image must start with assets/image/']);
            return;
        }
        error_log("Created slider manual URL: $image_url");
    } else {
        echo json_encode(['success' => false, 'message' => 'Image required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO carousel_sliders (title, image_url, type, sort_order, is_active) VALUES (?, ?, ?, ?, 1)");
        $success = $stmt->execute([$title, $image_url, $type, $sort_order]);
        if ($success && $stmt->rowCount() > 0) {
            $new_id = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Slider created successfully', 'id' => $new_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Create failed (check DB table)']);
        }
    } catch (PDOException $e) {
        error_log("createSlider PDO error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'DB error: Table carousel_sliders?']);
    }
}

// Update existing slider details or replace image
function updateSlider() {
    $pdo = getDB();
    $upload_base = __DIR__ . '/../assets/image/';
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }
    
    $title = trim($_POST['title'] ?? '');
    $type = in_array($_POST['type'] ?? 'hero', ['hero', 'marquee']) ? $_POST['type'] : 'hero';
    $sort_order = max(0, intval($_POST['sort_order'] ?? 0));

    // Get existing
    $stmt_old = $pdo->prepare("SELECT image_url FROM carousel_sliders WHERE id = ?");
    $stmt_old->execute([$id]);
    $old = $stmt_old->fetch(PDO::FETCH_ASSOC);
    if (!$old) {
        echo json_encode(['success' => false, 'message' => 'Slider not found']);
        return;
    }
    $old_image_url = $old['image_url'];
    $image_url = $old_image_url;

    // Update image if new file or URL
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image_file']['tmp_name'];
        $fileSize = $_FILES['image_file']['size'];
        $fileExt = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg','jpeg','png','webp','gif'];
        
        if (in_array($fileExt, $allowedExt) && $fileSize < 5000000) {
            $newName = uniqid() . '.' . $fileExt;
            $image_url = 'assets/image/' . $newName;
            if (move_uploaded_file($fileTmp, $upload_base . $newName)) {
                if (file_exists($upload_base . basename($old_image_url))) {
                    unlink($upload_base . basename($old_image_url));
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed']);
                return;
            }
        }
    } elseif (!empty(trim($_POST['image_url'] ?? ''))) {
        $image_url = trim($_POST['image_url']);
        if (strpos($image_url, 'assets/image/') !== 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid image path']);
            return;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE carousel_sliders SET title = ?, image_url = ?, type = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([$title, $image_url, $type, $sort_order, $id]);
        echo json_encode(['success' => true, 'message' => 'Updated successfully']);
    } catch (PDOException $e) {
        error_log('updateSlider error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}

// Delete a slider from DB and unlink the image file
function deleteSlider() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    } 

    $upload_base = __DIR__ . '/../assets/image/';
    $stmt_get = $pdo->prepare("SELECT image_url FROM carousel_sliders WHERE id = ?");
    $stmt_get->execute([$id]);
    $slider = $stmt_get->fetch(PDO::FETCH_ASSOC);
    if ($slider && strpos($slider['image_url'], 'assets/image/') === 0 && file_exists($upload_base . basename($slider['image_url']))) {
        unlink($upload_base . basename($slider['image_url']));
    }

    $stmt = $pdo->prepare("DELETE FROM carousel_sliders WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Deleted']);
}

// Toggle visibility status (Active/Disabled)
function toggleSlider() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }
    
    $stmt = $pdo->prepare("UPDATE carousel_sliders SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'message' => 'Toggled']);
}
?>
