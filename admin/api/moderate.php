<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$config_path = __DIR__ . '/../../user/config/db.php';
require_once $config_path;

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$type = $input['type'] ?? '';
$action = $input['action'] ?? '';
$admin_id = $_SESSION['admin_id'];

if (!$id || !$type || !$action) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$table = $type === 'post' ? 'posts' : 'comments';

switch ($action) {
    case 'approve':
        $stmt = $conn->prepare("UPDATE $table SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $log = $conn->prepare("INSERT INTO moderation_logs (admin_id, action_type, target_type, target_id) VALUES (?, 'approve', ?, ?)");
        $log->bind_param("isi", $admin_id, $type, $id);
        $log->execute();
        
        echo json_encode(['success' => true, 'message' => ucfirst($type) . ' approved successfully']);
        break;
        
    case 'reject':
        $stmt = $conn->prepare("UPDATE $table SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $log = $conn->prepare("INSERT INTO moderation_logs (admin_id, action_type, target_type, target_id) VALUES (?, 'reject', ?, ?)");
        $log->bind_param("isi", $admin_id, $type, $id);
        $log->execute();
        
        echo json_encode(['success' => true, 'message' => ucfirst($type) . ' rejected']);
        break;
        
    case 'delete':
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $log = $conn->prepare("INSERT INTO moderation_logs (admin_id, action_type, target_type, target_id) VALUES (?, 'delete', ?, ?)");
        $log->bind_param("isi", $admin_id, $type, $id);
        $log->execute();
        
        echo json_encode(['success' => true, 'message' => ucfirst($type) . ' deleted permanently']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
