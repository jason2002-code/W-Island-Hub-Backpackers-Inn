<?php
require_once __DIR__ . '/../config/db.php';

if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Handle both GET and DELETE requests
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch all users
    getUsers();
} elseif ($method === 'DELETE') {
    // Delete a user
    deleteUser();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

function getUsers() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    try {
        // Hardcode the 'Customer' role for the frontend JSON formatting since the column no longer exists
        $stmt = $pdo->prepare("SELECT id, name, email, 'Customer' as role, created_at FROM users ORDER BY created_at DESC");
        $stmt->execute();
        
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'users' => $users
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteUser() {
    $pdo = getDB();
    
    if (!$pdo) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    // Get user ID from query string
    $userId = $_GET['id'] ?? null;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        // Delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
