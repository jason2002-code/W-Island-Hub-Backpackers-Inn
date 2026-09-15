<?php
/**
 * AMENITIES API ACTION
 * Handles CRUD operations for Inn Amenities.
 * Supports icon selection, category tagging, image uploads, and status toggling.
 */
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = '';

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'get_active';
} elseif ($method === 'POST') {
    $action = $_POST['action'] ?? '';
}

// Security: Require admin role for write operations
if ($method === 'POST' && (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'admin')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized admin access required']);
    exit;
}

switch ($action) {
    case 'get_all':
        getAllAmenities();
        break;
    case 'get_active':
        getActiveAmenities();
        break;
    case 'get_single':
        getSingleAmenity();
        break;
    case 'create':
        createAmenity();
        break;
    case 'update':
        updateAmenity();
        break;
    case 'delete':
        deleteAmenity();
        break;
    case 'toggle':
        toggleAmenity();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid amenities action']);
        break;
}

/**
 * Fetch all amenities for admin management
 */
function getAllAmenities() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    try {
        $stmt = $pdo->query("SELECT * FROM `amenities` ORDER BY `sort_order` ASC, `id` ASC");
        $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'amenities' => $amenities]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to load amenities: ' . $e->getMessage()]);
    }
}

/**
 * Fetch only active amenities for public guest dashboard
 */
function getActiveAmenities() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    try {
        $stmt = $pdo->query("SELECT * FROM `amenities` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC");
        $amenities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'amenities' => $amenities]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
    }
}

/**
 * Fetch single amenity by ID
 */
function getSingleAmenity() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amenity ID']);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM `amenities` WHERE `id` = ?");
        $stmt->execute([$id]);
        $amenity = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($amenity) {
            echo json_encode(['success' => true, 'amenity' => $amenity]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Amenity not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $e->getMessage()]);
    }
}

/**
 * Create a new amenity
 */
function createAmenity() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fas fa-star');
    $category = trim($_POST['category'] ?? 'General');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? (intval($_POST['is_active']) ? 1 : 0) : 1;
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Amenity title is required']);
        return;
    }

    if (empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Amenity description is required']);
        return;
    }

    // Handle optional file upload
    $upload_base = __DIR__ . '/../assets/image/';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file format. Allowed: JPG, PNG, WEBP, GIF']);
            return;
        }

        if (!is_dir($upload_base)) {
            mkdir($upload_base, 0777, true);
        }

        $new_filename = uniqid('amenity_') . '.' . $ext;
        $dest_path = $upload_base . $new_filename;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest_path)) {
            $image_url = 'assets/image/' . $new_filename;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO `amenities` (`title`, `description`, `icon`, `category`, `image_url`, `sort_order`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon, $category, $image_url ?: null, $sort_order, $is_active]);
        $newId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'Amenity added successfully',
            'id' => $newId
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to create amenity: ' . $e->getMessage()]);
    }
}

/**
 * Update existing amenity
 */
function updateAmenity() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amenity ID']);
        return;
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fas fa-star');
    $category = trim($_POST['category'] ?? 'General');
    $sort_order = intval($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? (intval($_POST['is_active']) ? 1 : 0) : 1;
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Amenity title is required']);
        return;
    }

    if (empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Amenity description is required']);
        return;
    }

    // Handle optional file upload
    $upload_base = __DIR__ . '/../assets/image/';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file format. Allowed: JPG, PNG, WEBP, GIF']);
            return;
        }

        if (!is_dir($upload_base)) {
            mkdir($upload_base, 0777, true);
        }

        $new_filename = uniqid('amenity_') . '.' . $ext;
        $dest_path = $upload_base . $new_filename;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $dest_path)) {
            // Unlink previous uploaded image if it exists and was unique to this amenity
            $stmtOld = $pdo->prepare("SELECT image_url FROM `amenities` WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldImg = $stmtOld->fetchColumn();
            if ($oldImg && strpos($oldImg, 'assets/image/amenity_') === 0 && file_exists($upload_base . basename($oldImg))) {
                @unlink($upload_base . basename($oldImg));
            }
            $image_url = 'assets/image/' . $new_filename;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE `amenities` SET `title` = ?, `description` = ?, `icon` = ?, `category` = ?, `image_url` = ?, `sort_order` = ?, `is_active` = ? WHERE `id` = ?");
        $stmt->execute([$title, $description, $icon, $category, $image_url ?: null, $sort_order, $is_active, $id]);

        echo json_encode(['success' => true, 'message' => 'Amenity updated successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update amenity: ' . $e->getMessage()]);
    }
}

/**
 * Delete an amenity
 */
function deleteAmenity() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amenity ID']);
        return;
    }

    try {
        // Retrieve and delete image file if custom uploaded
        $upload_base = __DIR__ . '/../assets/image/';
        $stmtGet = $pdo->prepare("SELECT image_url FROM `amenities` WHERE id = ?");
        $stmtGet->execute([$id]);
        $img = $stmtGet->fetchColumn();
        if ($img && strpos($img, 'assets/image/amenity_') === 0 && file_exists($upload_base . basename($img))) {
            @unlink($upload_base . basename($img));
        }

        $stmt = $pdo->prepare("DELETE FROM `amenities` WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Amenity deleted successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete amenity: ' . $e->getMessage()]);
    }
}

/**
 * Toggle active/disabled status
 */
function toggleAmenity() {
    $pdo = getDB();
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }

    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amenity ID']);
        return;
    }

    try {
        $stmt = $pdo->prepare("UPDATE `amenities` SET `is_active` = NOT `is_active` WHERE id = ?");
        $stmt->execute([$id]);

        $stmtState = $pdo->prepare("SELECT is_active FROM `amenities` WHERE id = ?");
        $stmtState->execute([$id]);
        $newState = $stmtState->fetchColumn();

        echo json_encode([
            'success' => true,
            'message' => 'Status updated',
            'is_active' => intval($newState)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to toggle status: ' . $e->getMessage()]);
    }
}
