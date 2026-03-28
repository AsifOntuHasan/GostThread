<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$config_path = __DIR__ . '/../../../user/config/db.php';
require_once $config_path;

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'add':
        $word = trim(strtolower($input['word'] ?? ''));
        $category = $input['category'] ?? '';
        
        if (!$word || !$category) {
            echo json_encode(['success' => false, 'message' => 'Missing parameters']);
            exit;
        }
        
        $valid_categories = ['terrorist', 'sexual', 'cyberbullying'];
        if (!in_array($category, $valid_categories)) {
            echo json_encode(['success' => false, 'message' => 'Invalid category']);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO moderation_words (word, category) VALUES (?, ?)");
        $stmt->bind_param("ss", $word, $category);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id, 'message' => 'Word added']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add word']);
        }
        break;
        
    case 'delete':
        $id = (int)($input['id'] ?? 0);
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM moderation_words WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Word deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
